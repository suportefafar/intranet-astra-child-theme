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

$csv_data = null;
$header = null;
$allow_to_import = false;

$error_count = null;
$duplicates_count = null;
$success_count = null;
$total_count = null;

if ( $_SERVER['REQUEST_METHOD'] === 'POST' && ! empty( $_FILES['class_subjects_file'] ) && $_FILES['class_subjects_file']['tmp_name'] ) {
    $file = $_FILES['class_subjects_file']['tmp_name'];
    
    if (($handle = fopen($file, 'r')) !== false) {
        $raw_content = file_get_contents($file);
        
        // Detect file encoding
        $encoding = mb_detect_encoding($raw_content, ['UTF-8', 'ISO-8859-1', 'Windows-1252'], true) ?: 'ISO-8859-1';
        
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
    global $error_count, $duplicates_count, $success_count, $total_count;

    $class_subjects = intranet_fafar_api_get_submissions_by_object_name( 'class_subject' );
    if( isset( $class_subjects['error_msg'] ) ) $class_subjects = array();

    $error_count = 0;
    $success_count = 0;
    $total_count = count( $new_class_subjects );

    foreach( $new_class_subjects as $item ) {

        $new_class_subject = array(
            'code' => $item['Atividade acadêmica - Código'],
            'type' => array( ( 
                $item['Atividade acadêmica - Código'] === 'Teó.' ? 'Teórica' : 'Prática'
            ) ),
            'group' => $item['Id.'],
            'level' => '',
            'course' => array( $item['Cursos demandantes'] ),
            'adjustment' => '' ,
            'professors' => $item['Professores (nome, nº de inscrição, encargo)'],
            'course_load' => $item['Atividade acadêmica - CH'] ,
            'departament' => array( $item['Oft.'] ),
            'name_of_subject' => $item['Atividade acadêmica - Nome'],
            'nature_of_subject' => array( 'Obrigatória' ),
            'credits_of_subject' => ( ( (float) $item['Atividade acadêmica - CH'] ) / 15 ),
            'number_vacancies_offered' => $item['Número de vagas - Disp.'],
            'version_of_curriculum_matrix' => '' 
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

    $success_count = $total_count - $error_count;

}

function is_the_same( $class_subject_a, $class_subject_b ){

    /*
     * Se já não existe por CÓDIGO, NOME e TURMA 
     * Obs.: No caso do NOME, aplicar low case, retirar acentos e espaços
     */

    // Códigos
    $code_a = intranet_fafar_utils_remove_accents( $class_subject_a['code'] );
    $code_a = strtolower( $code_a );
    $code_a = str_replace( ' ', '', $code_a );

    $code_b = intranet_fafar_utils_remove_accents( $class_subject_b['code'] );
    $code_b = strtolower( $code_b );
    $code_b = str_replace( ' ', '', $code_b );

    // Nomes
    $name_a = intranet_fafar_utils_remove_accents( $class_subject_a['name_of_subject'] );
    $name_a = strtolower( $name_a );
    $name_a = str_replace( ' ', '', $name_a );

    $name_b = intranet_fafar_utils_remove_accents( $class_subject_b['name_of_subject'] );
    $name_b = strtolower( $name_b );
    $name_b = str_replace( ' ', '', $name_b );

    // Turmas
    $group_a = intranet_fafar_utils_remove_accents( $class_subject_a['group'] );
    $group_a = strtolower( $group_a );
    $group_a = str_replace( ' ', '', $group_a );

    $group_b = intranet_fafar_utils_remove_accents( $class_subject_b['group'] );
    $group_b = strtolower( $group_b );
    $group_b = str_replace( ' ', '', $group_b );

    if( 
        $code_a === $code_b && 
        $name_a === $name_b && 
        $group_a === $group_b  
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

        <h5><?= ( $success_count ?? '0' ) . '/' . ( $total_count ?? '0' ) ?> itens importados.  Erros: <?= ( $error_count ?? '0' ) ?> itens</h5>
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
