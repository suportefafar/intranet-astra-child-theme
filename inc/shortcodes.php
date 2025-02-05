<?php
// Prevent direct access to the file.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_shortcode( 'intranet_fafar_logs', 'intranet_fafar_logs' );

add_shortcode( 'intranet_fafar_importar_json', 'intranet_fafar_importar_json' );

add_shortcode( 'intranet_fafar_importar_reservas', 'intranet_fafar_importar_reservas' );

add_shortcode( 'intranet_fafar_reservas_por_disciplina', 'intranet_fafar_reservas_por_disciplina' );

add_shortcode( 'intranet_fafar_importar_disciplinas', 'intranet_fafar_importar_disciplinas' );

/* 
 * 
 * Shortcodes that returns html for forms, used on 
 * Contact Form 7 - Dynamic Text Extension plugin
 */
add_shortcode( 'intranet_fafar_get_users_as_select_options', 'intranet_fafar_get_users_as_select_options' );

add_shortcode( 'intranet_fafar_get_ips_as_select_options', 'intranet_fafar_get_ips_as_select_options' );

add_shortcode( 'intranet_fafar_get_user_slug_role', 'intranet_fafar_get_user_slug_role' );

add_shortcode( 'intranet_fafar_get_classrooms_as_select_options', 'intranet_fafar_get_classrooms_as_select_options' );

add_shortcode( 'intranet_fafar_generate_service_ticket_code', 'intranet_fafar_generate_service_ticket_code' );

add_shortcode( 'intranet_fafar_get_not_classrooms_as_select_options', 'intranet_fafar_get_not_classrooms_as_select_options' );


function intranet_fafar_logs() {

    echo '

        <!-- CHARTS -->

        <!--<div class="d-flex justify-content-around mb-4">
            <div class="card" style="width: 18rem;">intranet_fafar_importar_json
                <canvas id="myChart1"></canvas>
                <div class="card-body">
                    <h5 class="card-title">Chart vs Mês</h5>
                </div>
            </div>

            <div class="card" style="width: 18rem;">
                <canvas id="myChart2"></canvas>
                <div class="card-body">
                    <h5 class="card-title">Chart vs Ano</h5>
                </div>
            </div>

            <div class="card" style="width: 18rem;">
                <canvas id="myChart3"></canvas>
                <div class="card-body">
                    <h5 class="card-title">Chart vs Setor</h5>
                </div>
            </div>
        </div>-->

        <!-- TABS

        <ul class="nav nav-tabs">
            <li class="nav-item">
                <a class="nav-link active" aria-current="page" href="#">Todos</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#">Adicionado</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#">Editado</a>
            </li>
            <li class="nav-item">
                <a class="nav-link">Erro</a>
            </li>
        </ul>

        -->

        <!-- TABLES -->

        <div id="table-wrapper"></div>

    ';

}

function intranet_fafar_importar_json() {

    global $wpdb;

    $table_name = $wpdb->prefix . 'fafar_cf7crud_submissions';


    if( isset( $_POST["json_string"] ) ) {

        $json_string = $_POST["json_string"];

        print_r("Processando.....");
        print_r("<br/>");
        //print_r($json_string);

        //$json_d = json_decode( preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $json_string), true );
        $json_d = json_decode(stripslashes($json_string));

        //print_r($json_d);

        print_r("<br/>");
        print_r("JSON LAST ERROR: ");
        switch (json_last_error()) {
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

        $total_rows = count( $json_d );
        $count = 0;
        foreach( $json_d as $json_item ) {

            print_r($json_item);
            print_r("<br/>");
            print_r( (($count++)/$total_rows*100) . "%" );
            print_r("<br/>");

            $bytes       = random_bytes(5);
            $unique_hash = time().bin2hex($bytes); 
        
            $form_post_id      = -1;
            $form_data_as_json = json_encode( $json_item );
        
            $wpdb->insert( $table_name, array(
                'id'          => $unique_hash,
                'form_id'     => $form_post_id,
                'object_name' => $_POST['input_object_name'] ?? '',
                'data'        => $form_data_as_json,
                'permissions' => $_POST['input_permissions'] ?? '777',
                'owner'       => ($_POST['input_owner'] ?? ''),
                'group_owner' => ($_POST['input_group_owner'] ?? null)
            ) );


        }

    }


    echo '
    
    <form class="my-3" action="/importar-json" method="POST">

        <div class="form-floating mb-3">
            <input type="text" class="form-control" id="floatingInput" name="input_object_name"/>
            <label for="floatingInput">Nome do Objeto</label>
        </div>

        <div class="form-floating mb-3">
            <input type="text" class="form-control" id="fasd" name="input_owner"/>
            <label for="fasd">Dono</label>
        </div>

        <div class="form-floating mb-3">
            <input type="text" class="form-control" id="gsdge" name="input_group_owner"/>
            <label for="gsdge">Grupo Dono</label>
        </div>

        <div class="form-floating mb-3">
            <input type="text" class="form-control" id="asdasd" name="input_permissions"/>
            <label for="asdasd">Permissões</label>
        </div>

        <div class="form-floating mb-3">
            <textarea class="form-control" placeholder="Insira o texto JSON aqui" id="floatingTextarea" name="json_string" rows="15" required></textarea>
            <label for="floatingTextarea">Texto JSON</label>
        </div>

        <button type="submit">
            <i class="bi bi-file-earmark-arrow-up"></i>
            Importar
        </button>
    </form>

    ';
}

function intranet_fafar_importar_reservas() {

    global $wpdb;

    $table_name = $wpdb->prefix . 'fafar_cf7crud_submissions';


    if( isset( $_POST["json_string"] ) ) {

        $json_string = $_POST["json_string"];

        print_r("Processando.....");
        print_r("<br/>");
        //print_r($json_string);

        //$json_d = json_decode( preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $json_string), true );
        $json_d = json_decode(stripslashes($json_string));

        //print_r($json_d);

        print_r("<br/>");
        print_r("JSON LAST ERROR: ");
        switch (json_last_error()) {
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

        $total_rows = count( $json_d );
        $count = 0;
        foreach( $json_d as $json_item ) {

            print_r($json_item);
            print_r("<br/>");
            print_r( (($count++)/$total_rows*100) . "%" );
            print_r("<br/>");

            $bytes       = random_bytes(5);
            $unique_hash = time().bin2hex($bytes); 
        
            $form_post_id      = -1;
            $form_data_as_json = json_encode( $json_item );
        
            $object_name = $_POST['object_name'] ?? '';
        
            $wpdb->insert( $table_name, array(
                'id'          => $unique_hash,
                'form_id'     => $form_post_id,
                'object_name' => $object_name,
                'data'        => $form_data_as_json,
            ) );


        }

    }


    echo '
    
    <form class="my-3" action="/importar-reservas" method="POST">

        <div class="form-floating mb-3">
            <input type="text" class="form-control" id="floatingInput" name="input_object_name"/>
            <label for="floatingInput">Nome do Objeto</label>
        </div>

        <div class="form-floating mb-3">
            <input type="text" class="form-control" id="fasd" name="input_owner"/>
            <label for="fasd">Dono</label>
        </div>

        <div class="form-floating mb-3">
            <input type="text" class="form-control" id="gsdge" name="input_group_owner"/>
            <label for="gsdge">Grupo Dono</label>
        </div>

        <div class="form-floating mb-3">
            <input type="text" class="form-control" id="asdasd" name="input_permissions"/>
            <label for="asdasd">Permissões</label>
        </div>

        <div class="form-floating mb-3">
            <textarea class="form-control" placeholder="Insira o texto JSON aqui" id="floatingTextarea" name="json_string" rows="15" required></textarea>
            <label for="floatingTextarea">Texto JSON</label>
        </div>

        <button type="submit">
            <i class="bi bi-file-earmark-arrow-up"></i>
            Importar
        </button>
    </form>

    ';

}

function intranet_fafar_importar_disciplinas() {

    global $wpdb;

    $table_name = $wpdb->prefix . 'fafar_cf7crud_submissions';


    if( isset( $_POST["json_string"] ) ) {

        $json_string = $_POST["json_string"];

        print_r("Processando.....");
        print_r("<br/>");
        //print_r($json_string);

        //$json_d = json_decode( preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $json_string), true );
        $json_d = json_decode(stripslashes($json_string), true);

        //print_r($json_d);

        print_r("<br/>");
        print_r("JSON LAST ERROR: ");
        switch (json_last_error()) {
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

        $total_rows = count( $json_d );
        $count = 0;
        foreach( $json_d as $json_item ) {

            print_r($json_item);
            print_r("<br/>");
            print_r( (($count++)/$total_rows*100) . "%" );
            print_r("<br/>");

            
            $json_item['desc'] = $json_item['code'] . " - " . $json_item['name_of_subject'];

            $bytes       = random_bytes(5);
            $unique_hash = time().bin2hex($bytes); 
        
            $form_post_id      = -1;
            $form_data_as_json = json_encode( $json_item );
        
            $object_name = $_POST['object_name'] ?? '';
        
            $wpdb->insert( $table_name, array(
                'id'          => $unique_hash,
                'form_id'     => $form_post_id,
                'object_name' => $_POST['input_object_name'] ?? '',
                'data'        => $form_data_as_json,
                'permissions' => $_POST['input_permissions'] ?? '777',
                'owner'       => ($_POST['input_owner'] ?? ''),
                'group_owner' => ($_POST['input_group_owner'] ?? null)
            ) );


        }

    }


    echo '
    disciplina
    
    <form class="my-3" action="/importar-disciplinas" method="POST">

        <div class="form-floating mb-3">
            <input type="text" class="form-control" id="floatingInput" name="input_object_name" value="class_subject"/>
            <label for="floatingInput">Nome do Objeto</label>
        </div>

        <div class="form-floating mb-3">
            <input type="text" class="form-control" id="fasd" name="input_owner"/>
            <label for="fasd">Dono</label>
        </div>

        <div class="form-floating mb-3">
            <input type="text" class="form-control" id="gsdge" name="input_group_owner"/>
            <label for="gsdge">Grupo Dono</label>
        </div>

        <div class="form-floating mb-3">
            <input type="text" class="form-control" id="asdasd" name="input_permissions"/>
            <label for="asdasd">Permissões</label>
        </div>

        <div class="form-floating mb-3">
            <textarea class="form-control" placeholder="Insira o texto JSON aqui" id="floatingTextarea" name="json_string" rows="15" required></textarea>
            <label for="floatingTextarea">Texto JSON</label>
        </div>

        <button type="submit">
            <i class="bi bi-file-earmark-arrow-up"></i>
            Importar
        </button>
    </form>

    ';
}

function intranet_fafar_get_users_as_select_options_old() {

    $users = get_users( 
            array ( 
                    'role__not_in' => 'Administrator', 
                    'orderby' => 'display_name', 
                    'order' => 'ASC' 
                ) 
            );

    $options = '<option value=""></option>';
    foreach ( $users as $user ) {

        $options .= '<option value="' . $user->data->ID . '">';
        $options .=     $user->data->display_name;
        $options .= '</option>';

    }

    return $options;

}

function intranet_fafar_get_users_as_select_options() {

    $users = get_users( 
            array ( 
                    'role__not_in' => 'Administrator', 
                    'orderby' => 'display_name', 
                    'order' => 'ASC' 
                ) 
            );

    $options = array();

    foreach ( $users as $user ) {
        $options[esc_attr( $user->ID )] = esc_html( $user->display_name );
    }
    
    return json_encode( $options );

}

function intranet_fafar_get_ips_as_select_options() {

    $ips = intranet_fafar_api_get_submissions_by_object_name( 'ip', array(
        'orderby_json' => 'address',
        'inet_aton' => '1',
    ) ); 

    if ( isset( $ips['error_msg'] ) )
        $ips = array();

    $equipaments = intranet_fafar_api_get_submissions_by_object_name( 'equipament' );

    if ( isset( $equipaments['error_msg'] ) )
        $equipaments = array();

    $current_equipament = null;
    if ( isset( $_GET['id'] ) ) {

        $id = intranet_fafar_api_san( $_GET['id'] );
        $current_equipament = intranet_fafar_api_get_submission_by_id( $id );

    }

    $options = array();

    foreach ( $ips as $ip ) {

        $is_available = true;
        foreach ( $equipaments as $equipament ) {

            /* 
             * Isso garante que o IP que está sendo usado pelo 
             * equipamento sendo editado, esteja na lista de opções
             */
            if( $current_equipament &&
                isset( $current_equipament['data']['ip'][0] ) &&
                $current_equipament['data']['ip'][0] === $ip['id'] ) {
        
                $is_available = true;
                continue;
            
            }

            if ( isset( $equipament['data']['ip'][0] ) &&
                 $equipament['data']['ip'][0] === $ip['id'] ) {
            
                $is_available = false;
                break;

            }

        }

        if ( $is_available )
            $options[esc_attr( $ip['id'] )] = esc_html( $ip['data']['address'] );

    }

    return json_encode( $options ); 

}

function intranet_fafar_get_classrooms_as_select_options() {

    $places = intranet_fafar_api_get_submissions_by_object_name( 'place', array(
        'orderby_json' => 'number',
    ) );

    $options = array();

    foreach ( $places as $place ) {

        if ( $place['data']['object_sub_type'] === 'classroom' )
            $options[esc_attr( $place['id'] )] = esc_html( $place['data']['number'] );

    }

    return json_encode( $options ); 

}

function intranet_fafar_get_not_classrooms_as_select_options() {

    $places = intranet_fafar_api_get_submissions_by_object_name( 'place', array(
        'orderby_json' => 'number',
    ) );

    $options = array();

    foreach ( $places as $place ) {

        if ( $place['data']['object_sub_type'] !== 'classroom' )
            $options[esc_attr( $place['id'] )] = esc_html( $place['data']['number'] );

    }

    return json_encode( $options ); 

}

function intranet_fafar_generate_service_ticket_code() {
    
    $number_of_letters = 3;
    $number_of_digits = 3;

    $code_used = true;
    $new_code = '------';
    do{

        $new_code = intranet_fafar_generate_code( $number_of_letters, $number_of_digits );

        $query = "SELECT * FROM `SET_TABLE_NAME` WHERE `object_name` = 'service_ticket' AND JSON_CONTAINS(data, '\"" . $new_code . "\"', '$.code')";

        // Obtém todas as ordens de serviços, mesmo que ativas
        $submissions = intranet_fafar_api_read( $query, false, false );

        if ( empty( $submissions ) || isset( $submissions['error_msg'] ) ) $code_used = false;

    } while( $code_used );
    
    // Concatenate letters and numbers
    return $new_code;

}

function intranet_fafar_generate_code( $n_letters, $n_digits ) {

    // Generate three random uppercase letters
    $letters = '';
    for ($i = 0; $i < $n_letters; $i++) {
        $letters .= chr(rand(65, 90)); // ASCII values for A-Z are 65-90
    }
            
    // Generate three random digits
    $numbers = '';
    for ($i = 0; $i < $n_digits; $i++) {
        $numbers .= rand(0, 9);
    }

    // Concatenate letters and numbers
    return $letters . $numbers;

}

function intranet_fafar_get_user_slug_role() {

    $user       = wp_get_current_user();
    $role_slug  = $user->roles[0];
    $role_slug  = '';

    if ( $user->roles[0] ) {

        $role_slug = $user->roles[0];

    }

    return $role_slug;

}