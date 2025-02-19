<?php
/**
 * Esse é um arquivo de template
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package Intranet Astra Child Theme
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


/*
 * Importanto script JS customizado
 * wp_enqueue_script( 'intranet-fafar-salas-script', get_stylesheet_directory_uri() . '/assets/js/salas.js', array( 'jquery' ), false, false );
 */
wp_enqueue_script_module( 'intranet-fafar-importar-disciplinas-script', get_stylesheet_directory_uri() . '/assets/js/importar-disciplinas.js', array( 'jquery' ), false, true );

$csv_data        = null;
$header          = null;
$allow_to_import = false;

$error_count         = null;
$duplicates_count    = null;
$not_a_fafar_subject = null;
$success_count       = null;
$total_count         = null;

if ( $_SERVER['REQUEST_METHOD'] === 'POST' && ! empty( $_FILES['class_subjects_file'] ) && $_FILES['class_subjects_file']['tmp_name'] ) {
    $file = $_FILES['class_subjects_file']['tmp_name'];
    
    if (($handle = fopen($file, 'r')) !== false) {
        $raw_content = file_get_contents($file);
        
        if (substr($raw_content, 0, 3) === "\xEF\xBB\xBF") {
            $raw_content = substr($raw_content, 3);
            file_put_contents($file, $raw_content); // Save BOM-free content back to file
        }

        // Detect file encoding
        $encoding = mb_detect_encoding($raw_content, ['UTF-8', 'ISO-8859-1', 'Windows-1252', 'ASCII'], true) ?: 'ISO-8859-1';
        
        // Open file again for reading
        rewind($handle);
        
        // Read headers and convert to UTF-8
        $header = fgetcsv($handle, 500, ';', '"', '\\');
        $header = array_map(fn($col) => mb_convert_encoding($col, 'UTF-8', $encoding), $header);

        $data = [];

        while (($row = fgetcsv($handle, 500, ';', '"', '\\')) !== false) {
            $row = array_map(fn($col) => mb_convert_encoding($col, 'UTF-8', $encoding), $row);

            if( count( $header ) === count( $row ) )
                $data[] = array_combine($header, $row);
        }
        
        fclose($handle);
        

        $csv_data = $data;

        $allow_to_import = true;

        import_class_subjects( $csv_data );

    } else {
        $csv_data = 'ERRO: Falha ao abrir o arquivo CSV!';
    }
}

if ( $_SERVER['REQUEST_METHOD'] === 'POST' && isset( $_POST['class_subjects'] ) ) {

    echo '<script> alert("Recebido ' . count( $_POST['class_subjects'] ) . ' disciplinas!");</script>';

}

function import_class_subjects( $new_class_subjects ) {
    global $error_count, $duplicates_count, $not_a_fafar_subject, $success_count, $total_count;

    $class_subjects = intranet_fafar_api_get_submissions_by_object_name( 'class_subject' );
    if( isset( $class_subjects['error_msg'] ) ) $class_subjects = array();

    $error_count         = 0;
    $not_a_fafar_subject = 0;
    $success_count       = 0;
    $total_count         = count( $new_class_subjects );

    foreach( $new_class_subjects as $item ) {

        if(
            ! isset( $item['Código'] ) || 
            ! isset( $item['Curso'] ) || 
            ! isset( $item['Turma'] ) ||
            ! isset( $item['Horário'] ) || 
            ! isset( $item['Vagas'] )
        ){
            echo '<br />Faltando uma das colunas obrigatórias!<br />';
            $error_count++;
            return;
        }

        /* 
         * Aplica o filtro de código de disciplina se o 
         * curso informado não for da Pós.
         * Filtro: 
         * Verifica se tem código e se ele tem 
         * 'ACT', 'ALM', 'FAF', 'FAS', 'PFA' ou 'NUT' 
         */
        if( 
            ! str_contains( $item['Curso'], 'PPG' ) && 
            preg_match( '/ACT|ALM|FAF|FAS|PFA|NUT/', $item['Código'] ) !== 1
          ) {
            $not_a_fafar_subject++;
            continue;
        }

        // Decidimos que é melhor errar com uma optativa que com uma obrigatória
        $nature_of_subject = 'Obrigatória';
        if( 
            isset( $item['Natureza'] ) && 
            intranet_fafar_utils_escape_and_clean_to_compare( $item['Natureza'] ) === 'optativa'
          ) $nature_of_subject = 'Optativa';
        
        /*
         * Essa condição maluca se dá pelo fato do relatório 
         * do SIGA - conseguido pelo colegiado -, trazer essa informação
         * como 'Téo.' ou 'Prá.'. E para não forçar a todos que usem essa 
         * abreviação, então se faz necessário abracar todas as possibidades 
         */
        $type = ( isset( $item['Tipo'] ) ? $item['Tipo'] : '' );
        if( 
            str_contains( 
                $type, 
                intranet_fafar_utils_escape_and_clean_to_compare( 'teo' ) 
            ) 
        ) $type = 'Teórica';
        else if(
            str_contains( 
                $type, 
                intranet_fafar_utils_escape_and_clean_to_compare( 'pra' ) 
            )
        ) $type = 'Prática';
        else if(
            str_contains( 
                $type, 
                intranet_fafar_utils_escape_and_clean_to_compare( 'amb' ) 
            )
        ) $type = 'Ambas';

        // Verificar todos as colunas, exceto 'Código', 'Curso', 'Turma', 'Horário', 'Vagas'
        $inicio                = ( isset( $item['Início'] ) ? $item['Início'] : '' );
        $fim                   = ( isset( $item['Fim'] ) ? $item['Fim'] : '' );
        $carga_horaria         = ( isset( $item['Carga Horária'] ) ? $item['Carga Horária'] : 0 );
        $credits               = ( ( (float) $carga_horaria ) / 15 );
        $curso                 = ( isset( $item['Curso'] ) ? $item['Curso'] : '' );
        $nivel                 = ( isset( $item['Nível'] ) ? $item['Nível'] : '' );
        $departamento          = ( isset( $item['Departamento'] ) ? $item['Departamento'] : '' );
        $ajuste                = ( isset( $item['Ajuste'] ) ? $item['Ajuste'] : 0 );
        $professores           = ( isset( $item['Professores'] ) ? $item['Professores'] : '' );
        $matrizes_curriculares = ( isset( $item['Matrizes Currículares'] ) ? $item['Matrizes Currículares'] : '' );

        $new_class_subject = array(
            'code'                         => intranet_fafar_utils_escape_and_clean( $item['Código'] ),
            'name_of_subject'              => intranet_fafar_utils_escape_and_clean( $item['Nome'] ),
            'group'                        => intranet_fafar_utils_escape_and_clean( $item['Turma'] ),
            'nature_of_subject'            => array( $nature_of_subject ),
            'number_vacancies_offered'     => intranet_fafar_utils_escape_and_clean( $item['Vagas'] ),
            'desired_time'                 => intranet_fafar_utils_escape_and_clean( $item['Horário'] ),
            'desired_start_date'           => intranet_fafar_utils_escape_and_clean( $inicio ),
            'desired_end_date'             => intranet_fafar_utils_escape_and_clean( $fim ),
            'course_load'                  => intranet_fafar_utils_escape_and_clean( $carga_horaria ),
            'credits_of_subject'           => $credits,
            'course'                       => array( intranet_fafar_utils_escape_and_clean( $curso ) ),
            'level'                        => array( intranet_fafar_utils_escape_and_clean( $nivel ) ),
            'departament'                  => array( intranet_fafar_utils_escape_and_clean( $departamento ) ),
            'type'                         => array( intranet_fafar_utils_escape_and_clean( $type ) ),
            'adjustment'                   => intranet_fafar_utils_escape_and_clean( $ajuste ),
            'professors'                   => intranet_fafar_utils_escape_and_clean( $professores ),
            'version_of_curriculum_matrix' => intranet_fafar_utils_escape_and_clean( $matrizes_curriculares ),
        );

        $has_class_subject = false;

        foreach( $class_subjects as $class_subject ) {

            if( is_the_same( $class_subject['data'], $new_class_subject ) ) $has_class_subject = true;

        }

        if( $has_class_subject ) {
            $duplicates_count++;
            continue;
        }

        $result = intranet_fafar_api_create( array( 
            'object_name' => 'class_subject',
            'owner'       => '',
            'group_owner' => '',
            'permissions' => '770',
            'data'        => $new_class_subject, 
         ) );

         if( isset( $result['error_msg'] ) ) {
            print_r( $result['error_msg'] );
            $error_count++;
         }

    }

    $success_count = $total_count - $duplicates_count - $error_count - $not_a_fafar_subject;

}

function is_the_same( $class_subject_a, $class_subject_b ) {

    /*
     * Se já não existe por CÓDIGO, NOME e TURMA 
     * Obs.: No caso do NOME, aplicar low case, retirar acentos e espaços
     */
    if( 
        ! isset( $class_subject_a['code'] ) || 
        ! isset( $class_subject_a['name_of_subject'] ) || 
        ! isset( $class_subject_a['group'] )
      ) return false;

    if( 
        ! isset( $class_subject_a['code'] ) || 
        ! isset( $class_subject_a['name_of_subject'] ) || 
        ! isset( $class_subject_a['group'] )
      ) return false;

    // Códigos
    $code_a = intranet_fafar_utils_escape_and_clean_to_compare( $class_subject_a['code'] );
    $code_b = intranet_fafar_utils_escape_and_clean_to_compare( $class_subject_b['code'] );

    // Nomes
    $name_a = intranet_fafar_utils_escape_and_clean_to_compare( $class_subject_a['name_of_subject'] );
    $name_b = intranet_fafar_utils_escape_and_clean_to_compare( $class_subject_b['name_of_subject'] );

    // Turmas
    $group_a = intranet_fafar_utils_escape_and_clean_to_compare( $class_subject_a['group'] );
    $group_b = intranet_fafar_utils_escape_and_clean_to_compare( $class_subject_b['group'] );

    if( 
        intranet_fafar_utils_escape_and_clean_to_compare( $code_a ) === intranet_fafar_utils_escape_and_clean_to_compare( $code_b ) && 
        intranet_fafar_utils_escape_and_clean_to_compare( $name_a ) === intranet_fafar_utils_escape_and_clean_to_compare( $name_b ) && 
        intranet_fafar_utils_escape_and_clean_to_compare( $group_a ) === intranet_fafar_utils_escape_and_clean_to_compare( $group_b )  
      ) return true;

    return false;

}

get_header(); ?>

<?php if ( astra_page_layout() == 'left-sidebar' ) : ?>

	<?php get_sidebar(); ?>

<?php endif ?>

	<div id="primary" <?php astra_primary_class(); ?>>

		<?php astra_primary_content_top(); ?>

        <?php astra_content_page_loop(); ?>

        <script>
            const csvData = <?php echo wp_json_encode( $csv_data, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT ); ?>;
        </script>

<!--
    *
    *
    *
    * Conteúdo customizado da página
    * Início
--> 

        <form action="/importar-disciplinas" method="POST" enctype="multipart/form-data">

            <div class="mb-3">
                <label for="class_subjects_file" class="form-label">Oferta de Mapa de Sala (.csv)</label>
                <input class="form-control" type="file" id="class_subjects_file" name="class_subjects_file" />
            </div>

            <button type="submit">
                <i class="bi bi-file-earmark-arrow-up"></i>
                Importar
            </button>

        </form>

        <hr class="mx-1" />
        
        <button class="mb-3 btn btn-success" id="btn_import" <?= $allow_to_import ? '' : 'disabled' ?>>
            <i class="bi bi-file-earmark-arrow-up"></i>
            Importar
        </button>

        <h5><?= ( $success_count ?? '0' ) . '/' . ( $total_count ?? '0' ) ?> itens importados. | Duplicados: <?= ( $duplicates_count ?? '0' ) ?> itens. | Disciplinas Não-FAFAR: <?= ( $not_a_fafar_subject ?? '0' ) ?> | Erros: <?= ( $error_count ?? '0' ) ?> itens</h5>
        <table class="table">
            <thead>
                <tr>
                
                    <?php
                        if( isset( $csv_data ) && is_array( $csv_data ) && count( $csv_data ) > 0 ){
                            foreach ( array_keys( $csv_data[0] ) as $col ) {

                                echo '<th scope="col">' . $col . '</th>';

                            }
                        }    
                    ?>

                </tr>
            </thead>
            <tbody>
                    <?php
                        if( isset( $csv_data ) && is_array( $csv_data ) && count( $csv_data ) > 0 ){

                            foreach ( $csv_data as $index => $row ) {
                                echo '<tr>';

                                foreach ( $row as $col ) {
                                    echo '<td>' . $col . '</td>';
                                }

                                echo '</tr>'; 
                            }
                        }    
                    ?>
            </tbody>
        </table>

        <pre>
            <?php

                print_r( isset( $csv_data ) ? $csv_data : '' );

            ?>
        </pre>
        
<!--
    * Conteúdo customizado da página
    * Fim
    *
    *
    *
-->    

		<?php astra_primary_content_bottom(); ?>

	</div><!-- #primary -->

<?php if ( astra_page_layout() == 'right-sidebar' ) : ?>

	<?php get_sidebar(); ?>

<?php endif ?>

<?php get_footer(); ?>
