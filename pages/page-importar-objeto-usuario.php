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
$above_counter       = null;
$combinations        = array();

$new_users           = array(); 

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' && 
    isset( $_POST['old_users'] ) && 
    isset( $_POST['old_addresses'] )
) {

    $old_users_json = $_POST["old_users"];
    $old_addresses_json   = $_POST["old_addresses"];

    //$json_d = json_decode( preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $json_string), true );
    $old_users = json_decode(stripslashes($old_users_json), true);
    $old_addresses   = json_decode(stripslashes($old_addresses_json), true);

    print_r("<br/>");
    print_r("JSON LAST ERROR: ");

    switch ( json_last_error() ) {
        case JSON_ERROR_NONE:
            echo ' - No errors';
        break;
        case JSON_ERROR_DEPTH:
            echo ' - Maximum stack depth exceeded';
        break;
        case JSON_ERROR_STATE_MISMATCH:
            echo ' - Underflow or the modes mismatch';
        break;
        case JSON_ERROR_CTRL_CHAR:
            echo ' - Unexpected control character found';
        break;
        case JSON_ERROR_SYNTAX:
            echo ' - Syntax error, malformed JSON';
        break;
        case JSON_ERROR_UTF8:
            echo ' - Malformed UTF-8 characters, possibly incorrectly encoded';
        break;
        default:
            echo ' - Unknown error';
        break;
    }

    print_r("<br/>");

    foreach ( $old_users as $old_user )
    {
        if(
            $old_user['nivel'] !== '1' && 
            $old_user['nivel'] !== '2'
        )   continue;

        // Primeiro Nome

        $first_name = explode( ' ', $old_user['nome'] );
        array_pop( $first_name );
        $first_name = implode( ' ', $first_name );

        // Último nome

        $last_name = explode( ' ', $old_user['nome'] );
        $last_name = end( $last_name );

        // Role

        $role = get_fafar_user_role( $old_user['setor'] );

        if( $role === 'aposentado' ) {
            $role = '';
        }

        // Address
        $address_cep_code     = '';
        $address_uf           = '';
        $address_city         = '';
        $address_neighborhood = '';
        $address_public_place = '';
        $address_number       = '';
        $address_complement   = '';

        $address = get_fafar_address_obj( $old_user['id'], $old_addresses );

        if( $address ) {
            $address_cep_code     = $address['cep'];
            $address_uf           = $address['uf'];
            $address_city         = $address['municipio'];
            $address_neighborhood = $address['bairro'];
            $address_public_place = $address['logr'];
            $address_number       = $address['num'];
            $address_complement   = $address['comp'];
        }

        $new_user = array(
            // 'ID'              => 0, // (int) User ID (for updating existing users)
            // 'user_pass'       => 'StrongPassword123',// (string) Password (leave empty to generate automatically)
            'user_login'      => intranet_fafar_utils_escape_and_clean( $old_user['usuario'], 'lower' ), // (string) Required: Username (login name)
            // 'user_nicename'   => 'new-user', // (string) URL-friendly name (auto-generated if omitted)
            'user_email'      => intranet_fafar_utils_escape_and_clean( $old_user['email'], 'lower' ), // (string) User email (must be unique)
            // 'user_url'        => 'https://example.com', // (string) User website URL
            'user_registered' => $old_user['cadastro'], // (string) Registration date (YYYY-MM-DD HH:MM:SS)
            // 'user_status'     => 0, // (int) User status (default: 0)
            'display_name'    => intranet_fafar_utils_escape_and_clean( $old_user['nome'] ), // (string) Name displayed publicly
            'nickname'        => intranet_fafar_utils_escape_and_clean( explode( ' ', $old_user['nome'] )[0] ), // (string) User nickname
            'first_name'      => intranet_fafar_utils_escape_and_clean( $first_name ), // (string) First name
            'last_name'       => intranet_fafar_utils_escape_and_clean( $last_name ), // (string) Last name
            // 'description'     => 'Web Developer', // (string) Bio/Description
            'role'            => $role, // (string) Role (subscriber, contributor, author, editor, administrator)

            // Custom fields:

            'personal_phone'               => get_fafar_user_phone_clean(get_fafar_user_phone($old_user)),
            'personal_birthday'            => '',
            'personal_cpf'                 => '',
            'personal_ufmg_registration'   => '',
            'personal_siape'               => '',
            'address_cep_code'             => intranet_fafar_utils_escape_and_clean($address_cep_code),
            'address_uf'                   => intranet_fafar_utils_escape_and_clean($address_uf),
            'address_city'                 => intranet_fafar_utils_escape_and_clean($address_city),
            'address_neighborhood'         => intranet_fafar_utils_escape_and_clean($address_neighborhood),
            'address_public_place'         => intranet_fafar_utils_escape_and_clean($address_public_place),
            'address_number'               => intranet_fafar_utils_escape_and_clean($address_number),
            'address_complement'           => intranet_fafar_utils_escape_and_clean($address_complement),
            'public_servant_bond_type'     => get_fafar_user_bond_type( $old_user['cargo'] ), // EFETIVO, SUBSTITUTO, VALUNTÁRIO
            'public_servant_bond_category' => $old_user['nivel'] === '1' ? 'TAE' : 'DOCENTE',
            'public_servant_bond_position' => get_fafar_user_position( $old_user['cargo'], $old_user['nivel'] ),
            'public_servant_bond_class'    => '',
            'public_servant_bond_level'    => '',
            'bond_status'                  => get_fafar_user_status( $old_user['ativo'] ), // ATIVO, APOSENTADO, DESLIGADO E REMOVIDO
            'workplace_extension'          => $old_user['ramal'],
            'workplace_place'              => get_fafar_user_place( $old_user['sala'] ),
        );

        if (!username_exists($new_user['user_login']) && !email_exists($new_user['user_email'])) {
            $user_id = wp_insert_user($new_user);
            
            if (!is_wp_error($user_id)) {
                echo "User created with ID: " . $user_id;
                
                // Save custom fields in wp_usermeta
                $custom_fields = [
                    'personal_phone'               => get_fafar_user_phone_clean(get_fafar_user_phone($old_user)),
                    'personal_birthday'            => '',
                    'personal_cpf'                 => '',
                    'personal_ufmg_registration'   => '',
                    'personal_siape'               => '',
                    'address_cep_code'             => intranet_fafar_utils_escape_and_clean($address_cep_code),
                    'address_uf'                   => intranet_fafar_utils_escape_and_clean($address_uf),
                    'address_city'                 => intranet_fafar_utils_escape_and_clean($address_city),
                    'address_neighborhood'         => intranet_fafar_utils_escape_and_clean($address_neighborhood),
                    'address_public_place'         => intranet_fafar_utils_escape_and_clean($address_public_place),
                    'address_number'               => intranet_fafar_utils_escape_and_clean($address_number),
                    'address_complement'           => intranet_fafar_utils_escape_and_clean($address_complement),
                    'public_servant_bond_type'     => get_fafar_user_bond_type( $old_user['cargo'] ), // EFETIVO, SUBSTITUTO, VALUNTÁRIO
                    'public_servant_bond_category' => $old_user['nivel'] === '1' ? 'TAE' : 'DOCENTE',
                    'public_servant_bond_position' => get_fafar_user_position( $old_user['cargo'], $old_user['nivel'] ),
                    'public_servant_bond_class'    => '',
                    'public_servant_bond_level'    => '',
                    'bond_status'                  => get_fafar_user_status( $old_user['ativo'] ), // ATIVO, APOSENTADO, DESLIGADO E REMOVIDO
                    'workplace_extension'          => $old_user['ramal'],
                    'workplace_place'              => get_fafar_user_place( $old_user['sala'] ),
                ];
        
                foreach ($custom_fields as $key => $value) {
                    update_user_meta($user_id, $key, $value);
                }
        
            } else {
                echo "Error: " . $user_id->get_error_message();
            }
        }
        

        $new_users[] = $new_user;
    }

}

function get_fafar_user_role( $number ) {

    if(
        ! isset( $number ) ||
        $number === null || 
        is_array( $number )
    ) return '';

    $roles = [
        '',
        'ACT',
        'ALM',
        'FAS',
        'PFA',
        'PPGCA',
        'PPGCF',
        'PPGACT',
        'PPGMAF',
        'Colegiado de Graduação Farmácia',
        'Colegiado de Graduação Biomedicina',
        'Secretaria Geral',
        'Secretaria Executiva',
        'Diretoria',
        'Superintendência Administrativa',
        'Almoxarifado',
        'Biblioteca',
        'Contabilidade',
        'Compras',
        'Tecnologia da Informação e Suporte',
        'Centro de Memória',
        'Apoio Logístico e Operacional',
        'Biotério',
        'Gerenciamento Ambiental e Biossegurança',
        'Arquivo',
        'NAPq/CENEX',
        'Pessoal',
        'Patrimônio',
        'Assessoria de Assuntos Educacionais',
        'Aposentado',
    ];

    if( ! is_numeric($number) ) {
        return '';
    }

    if( ( (int) $number ) > count( $roles ) ) {
        return '';
    }

    return intranet_fafar_utils_escape_and_clean_to_compare( str_replace( ' ', '_', $roles[$number] ) );

}

function get_fafar_user_phone( $user ) {

    if(
        ! isset( $user ) ||
        $user === null
    ) return '';

    if(
        isset( $user['telcel'] ) && 
        $user['telcel']
    ) {
        return $user['telcel'];
    } else if(
        isset( $user['telfix'] ) && 
        $user['telfix']
    ) {
        return $user['telfix'];
    } else {
        return '';
    }
}

function get_fafar_user_phone_clean( $text ) {

    $text = str_replace( '(', '', $text );
    $text = str_replace( ')', '', $text );
    $text = str_replace( '.', '', $text );
    $text = str_replace( '_', '', $text );
    $text = str_replace( '-', '', $text );

    return intranet_fafar_utils_escape_and_clean_to_compare( $text );
}

function get_fafar_user_place( $number ) {

    if(
        ! isset( $number ) ||
        $number === null || 
        is_array( $number )
    ) return '';

    $places = intranet_fafar_api_get_submissions_by_object_name( 'place' );

    foreach ( $places as $place ) {
        
        if(
            $place['data']['number'] === $number
        ) return $place['id'];

    }

    return false;
}

function get_fafar_address_obj( $user_id, $addresses ) {

    if(
        ! isset( $user_id ) ||
        ! $user_id 
    ) return '';

    if(
        ! isset( $addresses ) ||
        ! $addresses
    ) return '';

    foreach( $addresses as $address ) {
        if( $address['idusuario'] === $user_id ) {
            return $address;
        }
    }

    return false;

}

function get_fafar_user_bond_type( $index ) {
    
    if(
        ! isset( $index ) ||
        $index === null || 
        is_array( $index )
    ) return '';

    return array(
        '23' => 'SUBSTITUTO',
        '36' => 'VOLUNTÁRIO',
    )[(int) $index] ?? 'EFETIVO';

}

function get_fafar_user_status( $index ) {
    
    if(
        ! isset( $index ) ||
        $index === null || 
        is_array( $index )
    ) return '';

    return array(
        '1' => 'ATIVO',
        '2' => 'APOSENTADO',
        '3' => 'REMOVIDO',
        '4' => 'DESLIGADO',
    )[(int) $index] ?? '';

}

function get_fafar_user_position( $position, $type ) {

    if(
        ! isset( $position ) ||
        $position === null || 
        is_array( $position )
    ) return '';

    if(
        ! isset( $type ) ||
        $type === null || 
        is_array( $type )
    ) return '';

	$positions = [
        '2' => 'ADMINISTRADOR',
        '3' => 'ALMOXARIFE',
        '4' => 'ANALISTA DE SISTEMA',
        '5' => 'ASSISTENTE ADMINISTRATIVO',
        '6' => 'ASSISTENTE DE LABORATÓRIO ',
        '8' => 'AUXILAR DE LABORATÓRIO',
        '9' => 'AUXILIAR DE ADMINISTRAÇÃO',
        '10' => 'AUXILIAR DE LABORATÓRIO',
        '11' => 'BIBLIOTECÁRIO',
        '12' => 'BIÓLOGO',
        '13' => 'BOMBEIRO HIDRÁULICO',
        '14' => 'CONTADOR',
        '15' => 'FARMACÊUTICO',
        '16' => 'GERENTE DE QUALIDADE',
        '64' => 'INDETERMINADO',
        '17' => 'MÉDICO VETERINÁRIO',
        '18' => 'MOTORISTA',
        '19' => 'REPOGRAFIA',
        '20' => 'SECRETÁRIO EXECUTIVO',
        '21' => 'SERVENTE DE OBRAS',
        '22' => 'SETOR',
        '24' => 'TÉCNICA ASSUNTOS EDUCACIONAIS',
        '26' => 'TÉCNICO ADMINISTRATIVO',
        '25' => 'TÉCNICO DE ALIMENTOS E LATICÍNIOS',
        '65' => 'TÉCNICO DE FARMÁCIA',
        '28' => 'TÉCNICO DE LABORATÓRIO/BIOLOGIA',
        '30' => 'TÉCNICO DE LABORATÓRIO/FÍSICA',
        '31' => 'TÉCNICO DE LABORATÓRIO/INDUSTRIAL',
        '29' => 'TÉCNICO DE LABORATÓRIO/QUÍMICA',
        '27' => 'TÉCNICO DE LABORATÓTIO/ANÁLISES CLÍNICAS',
        '32' => 'TÉCNICO EM CONTABILIDADE',
        '33' => 'TÉCNICO EM TECNOLOGIA DA INFORMAÇÃO',
        '34' => 'TÉCNICO QUIMICO',
    ];

    // Docente    
    if( $type === '2' ) {
        $positions = [
            '1' => 'ADJUNTO',
            '7' => 'ASSOCIADO',
            '23' => 'SUBSTITUTO',
            '35' => 'TITULAR',
            '36' => 'VOLUNTÁRIO',
        ];

        return ( isset( $positions[$position] ) ? $positions[$position] : '' );
    }

    return ( isset( $positions[$position] ) ? $positions[$position] : '' );

}

get_header(); ?>

<?php if ( astra_page_layout() == 'left-sidebar' ) : ?>

	<?php get_sidebar(); ?>

<?php endif ?>

	<div id="primary" <?php astra_primary_class(); ?>>

		<?php astra_primary_content_top(); ?>

        <?php astra_content_page_loop(); ?>

<!--
    *
    *
    *
    * Conteúdo customizado da página
    * Início
--> 

    
        <form class="my-3" action="/importar-objeto" method="POST">

            <div class="form-floating mb-3">
                <textarea class="form-control" placeholder="Insira o texto JSON aqui" id="floatingTextarea" name="old_users" rows="15" required></textarea>
                <label for="floatingTextarea">Usuários</label>
            </div>

            <div class="form-floating mb-3">
                <textarea class="form-control" placeholder="Insira o texto JSON aqui" id="floatingTextarea" name="old_addresses" rows="15" required></textarea>
                <label for="floatingTextarea">Endereços</label>
            </div>

            <button type="submit">
                Processar
            </button>
        </form>

        <br />
        <br />

        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                    
                        <?php
                            if( isset( $new_users ) && is_array( $new_users ) && count( $new_users ) > 0 ){
                                foreach ( array_keys( $new_users[0] ) as $col ) {

                                    echo '<th scope="col">' . $col . '</th>';

                                }
                            }    
                        ?>

                    </tr>
                </thead>
                <tbody>
                        <?php
                            if( isset( $new_users ) && is_array( $new_users ) && count( $new_users ) > 0 ){

                                foreach ( $new_users as $index => $row ) {
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
        </div>
        
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
