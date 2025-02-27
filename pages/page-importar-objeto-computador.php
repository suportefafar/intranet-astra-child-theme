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

$new_submissions     = array(); 

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' && 
    isset( $_POST['old_users'] ) && 
    isset( $_POST['old_computers'] )
) {

    $old_users_json = $_POST["old_users"];
    $old_computers_json   = $_POST["old_computers"];

    //$json_d = json_decode( preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $json_string), true );
    $old_users = json_decode(stripslashes($old_users_json), true);
    $old_computers   = json_decode(stripslashes($old_computers_json), true);

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

    foreach ( $old_computers as $old_computer )
    {

        $new_computer = array(
            'object_name' => 'equipament',
            'group_owner' => 'tecnologia_da_informacao_e_suporte',
            'permissions' => '774',
            'data' => array(
                "ip" => [ get_fafar_ip_by_old_id( $old_computer['Ip_id'] ) ],
                "desc" => '[old] ' . $old_computer['Descricao'] . ' | ' . $old_computer['Sistema'],
                "asset" => $old_computer['Patrimonio'],
                "brand" => "",
                "model" => "",
                "place" => [
                    get_fafar_user_place( $old_computer['Sala'] )
                ],
                "status" => [
                    ( $old_computer['ativo'] == 1 ? "Ativado" : "Desativado" )
                ],
                "gpu_ram" => "",
                "on_loan" => "",
                "os_arch" => [],
                "os_type" => [],
                "ram_type" => [],
                "applicant" => [
                    get_fafar_new_user_id_by_old_id( $old_computer['usuario_id'], $old_users )
                ],
                "cpu_brand" => [],
                "cpu_model" => "",
                "gpu_brand" => [],
                "gpu_model" => "",
                "os_version" => "",
                "disk_type_1" => [
                    "HD"
                ],
                "disk_type_2" => [
                    "HD"
                ],
                "disk_type_3" => [
                    "HD"
                ],
                "disk_type_4" => [
                    "HD"
                ],
                "mac_address" => "",
                "ram_capacity" => "",
                "cpu_frequency" => "",
                "gpu_frequency" => "",
                "internal_asset" => "",
                "disk_capacity_1" => "",
                "disk_capacity_2" => "",
                "disk_capacity_3" => "",
                "disk_capacity_4" => "",
                "object_sub_type" => [
                    "Computador"
                ],
                "is_connected_to_router" => [ ( $old_computer['Ip_id'] == 0 ? 'Sim' : 'Não' ) ]
            ),
        );



        intranet_fafar_api_create( $new_computer );



        // $new_submissions[] = $new_computer;

        if( $old_computer['Monitor'] ) {

            $new_monitor = array(
                'object_name' => 'equipament',
                'group_owner' => 'tecnologia_da_informacao_e_suporte',
                'permissions' => '774',
                'data' => array(
                    "ip" => [],
                    "desc" => '[old] ' . $old_computer['Monitor'],
                    "asset" => $old_computer['Patrimonio_Monitor'],
                    "brand" => "",
                    "model" => "",
                    "place" => [
                        get_fafar_user_place( $old_computer['Sala'] )
                    ],
                    "status" => [
                        ( $old_computer['ativo'] == 1 ? "Ativado" : "Desativado" )
                    ],
                    "gpu_ram" => "",
                    "on_loan" => "",
                    "os_arch" => [],
                    "os_type" => [],
                    "ram_type" => [],
                    "applicant" => [
                        get_fafar_new_user_id_by_old_id( $old_computer['usuario_id'], $old_users )
                    ],
                    "cpu_brand" => [],
                    "cpu_model" => "",
                    "gpu_brand" => [],
                    "gpu_model" => "",
                    "os_version" => "",
                    "disk_type_1" => [
                        "HD"
                    ],
                    "disk_type_2" => [
                        "HD"
                    ],
                    "disk_type_3" => [
                        "HD"
                    ],
                    "disk_type_4" => [
                        "HD"
                    ],
                    "mac_address" => "",
                    "ram_capacity" => "",
                    "cpu_frequency" => "",
                    "gpu_frequency" => "",
                    "internal_asset" => "",
                    "disk_capacity_1" => "",
                    "disk_capacity_2" => "",
                    "disk_capacity_3" => "",
                    "disk_capacity_4" => "",
                    "object_sub_type" => [
                        "Monitor"
                    ],
                    "is_connected_to_router" => [ 'Não' ]
                ),
            );

            //$new_submissions[] = $new_monitor;



            intranet_fafar_api_create( $new_monitor );


            
        }

    }

}

function get_fafar_ip_by_old_id( $old_id ) {

    if( $old_id == 0 ) return '';

    $ips = intranet_fafar_api_get_submissions_by_object_name( 'ip' );

    foreach( $ips as $ip ) {

        if( $ip['data']['old_id'] === $old_id ) {
            return $ip['id'];
        }

    } 

    return '';

}

function get_fafar_service_type( $setor, $servico ) {


    if(
        ! isset( $setor ) ||
        $setor === null || 
        is_array( $setor )
    ) return '';

    $ft = array(
        '1' => [
            '0' => 'Eletrônica',
            '1' => 'Instalação de ...',
            '2' => 'Manutenção de ...',
            '3' => 'Remoção de ...',
            '4' => 'Outros',
        ],
        '2' => [
            '0' => 'Outros',
            '1' => 'Instalação de ...',
            '2' => 'Manutenção de ...',
            '3' => 'Remoção de ...',
            '4' => 'Outros',
        ],
        '3' => [
            '0' => 'Instalações elétricas',
            '1' => 'Instalações elétricas',
            '2' => 'Instalações elétricas',
            '3' => 'Instalações elétricas',
            '4' => 'Instalações elétricas',
        ],
        '4' => [
            '0' => 'Instalações hidráulicas',
            '1' => 'Instalações hidráulicas',
            '2' => 'Instalações hidráulicas',
            '3' => 'Instalações hidráulicas',
            '4' => 'Instalações hidráulicas',
        ],
        '5' => [
            '0' => 'Outros',
            '1' => 'Instalação de ...',
            '2' => 'Manutenção de ...',
            '3' => 'Remoção de ...',
            '4' => 'Outros',
        ],
        '6' => [ '0' => 'Transferência de bens internos', '5' => 'Movimentação de ...', ],
        '7' => [
            '0' => 'Transferência de bens externos',
            '6' => 'Empréstimo',
            '7' => 'Transferência',
            '8' => 'Manutenção',
        ],
        '8' => [ '0' => 'Registro de tombamento', '5' => 'Movimentação de ...', ],
        '9' => [ '0' => 'Outros', '5' => 'Movimentação de ...', ],
        '10' => [
            '0' => 'Outros',
            '6' => 'Informação',
            '7' => 'Abono de ponto',
            '8' => 'Progressão',
        ],
    );

    if( isset( $ft[(int) $setor][(int) $servico] ) ) return $ft[(int) $setor][(int) $servico];

    $st = array(
        '0' => 'Outros',
        '1' => 'Eletrônica',
        '2' => 'Outros',
        '3' => 'Instalações elétricas',
        '4' => 'Instalações hidráulicas',
        '5' => 'Outros',
        '6' => 'Transferência de bens internos',
        '7' => 'Transferência de bens externos',
        '8' => 'Registro de tombamento',
        '9' => 'Outros',
        '10' => 'Outros',
    )[(int) $setor];

    if( $st ) return $st;

    return 'Outros';

}

function get_fafar_departament_assigned_to( $index ) {

    if(
        ! isset( $index ) ||
        $index === null || 
        is_array( $index )
    ) return '';

    return array(
        '1' => 'apoio_logistico_e_operacional',
        '2' => 'tecnologia_da_informacao_e_suporte',
        '3' => 'apoio_logistico_e_operacional',
        '4' => 'apoio_logistico_e_operacional',
        '5' => 'apoio_logistico_e_operacional',
        '6' => 'patrimonio',
        '7' => 'patrimonio',
        '8' => 'patrimonio',
        '9' => 'patrimonio',
        '10' => 'pessoal',
    )[(int) $index] ?? '';

}

function get_fafar_old_status( $index ) {

    if(
        ! isset( $index ) ||
        $index === null || 
        is_array( $index )
    ) return '';

    return array(
        '1' => 'Aguardando',
        '2' => 'Em andamento',
        '3' => 'Finalizada',
        '4' => 'Cancelada',
    )[(int) $index] ?? 'Nova';

}

function get_fafar_new_user_id_by_old_id( $old_id, $old_users ) {
    if(
        ! isset( $old_id ) ||
        $old_id === null || 
        is_array( $old_id )
    ) return '';

    foreach ( $old_users as $old_user ) {
        
        if(
            $old_user['id'] === $old_id
        ){

            $user = get_user_by( 'login', intranet_fafar_utils_escape_and_clean_to_compare( $old_user['usuario'] ) );
            
            if ( $user ) {

                return $user->ID;

            } else {

                return 0;

            }
            
        } 

    }

    return 0;
}

function get_fafar_sector_by_old_id( $old_id ) {

    if(
        ! isset( $old_id ) ||
        $old_id === null || 
        is_array( $old_id )
    ) return '';

    return array(
        '1' => 'tecnologia_da_informacao_e_suporte',
        '2' => 'tecnologia_da_informacao_e_suporte',
        '3' => 'apoio_logistico_e_operacional',
        '4' => 'apoio_logistico_e_operacional',
    )[(int) $old_id] ?? '';

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

    return '';
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
                <textarea class="form-control" placeholder="Insira o texto JSON aqui" id="floatingTextarea" name="old_computers" rows="15" required></textarea>
                <label for="floatingTextarea">Computadores</label>
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
                            if( isset( $new_submissions ) && is_array( $new_submissions ) && count( $new_submissions ) > 0 ) {
                                foreach ( array_keys( $new_submissions[0] ) as $col ) {

                                    echo '<th scope="col">' . $col . '</th>';

                                }
                            }    
                        ?>

                    </tr>
                </thead>
                <tbody>
                        <?php
                            if( isset( $new_submissions ) && is_array( $new_submissions ) && count( $new_submissions ) > 0 ) {

                                foreach ( $new_submissions as $index => $row ) {
                                    echo '<tr>';

                                    foreach ( $row as $col ) {
                                        echo '<td>';
                                        print_r($col);
                                        echo '</td>';
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
