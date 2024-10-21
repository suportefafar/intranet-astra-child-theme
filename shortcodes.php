<?php

add_shortcode( 'intranet_fafar_sidebar_profile', 'intranet_fafar_sidebar_profile' );

add_shortcode( 'intranet_fafar_sidebar_menu', 'intranet_fafar_sidebar_menu' );

add_shortcode( 'intranet_fafar_logs', 'intranet_fafar_logs' );

add_shortcode( 'intranet_fafar_disciplinas', 'intranet_fafar_disciplinas' );

add_shortcode( 'intranet_fafar_salas', 'intranet_fafar_salas' );

add_shortcode( 'intranet_fafar_importar_json', 'intranet_fafar_importar_json' );

add_shortcode( 'intranet_fafar_importar_reservas', 'intranet_fafar_importar_reservas' );

add_shortcode( 'intranet_fafar_vizualizar_objeto', 'intranet_fafar_vizualizar_objeto' );

add_shortcode( 'intranet_fafar_reservas', 'intranet_fafar_reservas' );

add_shortcode( 'intranet_fafar_reservas_por_disciplina', 'intranet_fafar_reservas_por_disciplina' );

add_shortcode( 'intranet_fafar_assistente_de_reservas', 'intranet_fafar_assistente_de_reservas' );

add_shortcode( 'intranet_fafar_importar_disciplinas', 'intranet_fafar_importar_disciplinas' );


function intranet_fafar_sidebar_profile() {

    $user       = wp_get_current_user();
    $avatar_url = get_avatar_url( $user->get( 'ID' ) );
    $user_meta  = get_userdata( $user->get( 'ID' ) );
    $main_role  = $user_meta->roles[0];

    echo '
        <div class="d-flex gap-4 mb-5">
            <a href="https://intranet.farmacia.ufmg.br/membros/' . $user->get( 'user_login' ) . '/profile/change-avatar/">
                <img src="' . $avatar_url . '" width="64" alt="User profile avatar" />
            </a>

            <div class="d-flex flex-column justify-content-center gap-1">
                <h6 class="p-0 m-0">
                    <a href="https://intranet.farmacia.ufmg.br/membros/' . $user->get( 'user_login' ) . '/" class="text-decoration-none">' . 
                    $user->get( 'display_name' ) . 
                    '</a>
                </h6>
                <small class="p-0 m-0 text-muted">' . ucfirst( $main_role ) . '</small>
            </div>
        </div>
        ';
}

function intranet_fafar_sidebar_menu() {

    // add_filter( 'nav_menu_css_class', function ( $classes ) {

    //     array_push( $classes, 'border-bottom' );
    //     return $classes;

    // });

    echo '<div style="min-height:16em">';
        echo wp_nav_menu(array(
            'menu' => 'Lateral',
            'container' => false,
            'menu_class' => '',
            'fallback_cb' => '__return_false',
            'items_wrap' => '<ul id="%1$s" class="navbar-nav me-auto mb-2 mb-md-0 %2$s">%3$s</ul>',
            'depth' => 2,
            'walker' => new bootstrap_5_wp_nav_menu_walker()
        ));
    echo '</div>';
}

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

function intranet_fafar_vizualizar_objeto() {

    if( ! isset( $_GET["id"] ) ) {
        echo '<pre> Nenhum ID informado. </pre>';
        return;
    }

    $id = sanitize_text_field( wp_unslash( $_GET["id"] ) );

    $submission = intranet_fafar_api_get_submission_by_id( $id );

    echo "<pre>";
    print_r($submission);
    echo "</pre>";

}

function intranet_fafar_disciplinas() {

    echo '
    
        <!-- HEADER BUTTONS -->

        <div class="d-flex justify-content-start gap-2 mb-4">
            <a href="/adicionar-disciplina" class="btn btn-outline-success text-decoration-none w-button">
                <i class="bi bi-plus-lg"></i>
                Adicionar
            </a>
        </div>

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

        <!-- TABS -->

        <!--<ul class="nav nav-tabs">
            <li class="nav-item">
                <a class="nav-link active" aria-current="page" href="#">Active</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#">Link</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#">Link</a>
            </li>
            <li class="nav-item">
                <a class="nav-link disabled" aria-disabled="true">Disabled</a>
            </li>
        </ul>-->

        <!-- TABLES -->

        <div id="table-wrapper"></div>

    ';

}

function intranet_fafar_salas() {


    echo '
    
        <!-- HEADER BUTTONS -->

        <div class="d-flex justify-content-start gap-2 mb-4">
            <a href="/adicionar-sala" class="btn btn-outline-success text-decoration-none">
                <i class="bi bi-plus-lg"></i>
                Adicionar
            </a>
        </div>

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

        <!-- TABS -->

        <!--<ul class="nav nav-tabs">
            <li class="nav-item">
                <a class="nav-link active" aria-current="page" href="#">Active</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#">Link</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#">Link</a>
            </li>
            <li class="nav-item">
                <a class="nav-link disabled" aria-disabled="true">Disabled</a>
            </li>
        </ul>-->

        <!-- TABLES -->

        <div id="table-wrapper"></div>

    ';

}

function intranet_fafar_assistente_de_reservas() {

    echo '
        <form id="form-buscar-salas" class="mb-5">
            <div class="form-group mb-3">
                <label for="dia-do-evento">* Dia do Evento </label>
                <input type="date" 
                    class="form-control" 
                    id="dia-do-evento" 
                    name="dia_evento" 
                    min="2024-09-10" 
                    aria-required="true" 
                    required />
            </div>
            <div class="form-group mb-3">
                <label for="inicio-evento">* Início </label>
                <input type="time" 
                    class="form-control" 
                    id="inicio-evento" 
                    name="inicio_evento" 
                    aria-required="true" 
                    required />
            </div>
            <div class="form-group mb-3">
                <label for="fim-evento">* Fim </label>
                <input type="time" 
                    class="form-control" 
                    id="fim-evento" 
                    name="fim_evento" 
                    aria-required="true" 
                    required />
            </div>
            <div class="form-group mb-3">
                <label for="capacidade">* Capacidade </label>
                <input type="number" 
                    class="form-control" 
                    id="capacidade" 
                    name="capacidade" 
                    min="1" 
                    max="200" 
                    placeholder="20" 
                    aria-required="true" 
                    required />
            </div>
            <button type="submit" class="btn btn-outline-secondary">Buscar Salas</button>
        </form>

        <!-- TABLES -->

        <div id="table-wrapper" class="my-5 d-none"></div>
    ';
}

function intranet_fafar_reservas() {

    $places = intranet_fafar_api_get_submissions_by_object_name( 'place' );

    if ( isset( $places['msg_error'] ) )
        $places = array();

    
    usort($places, function($a, $b) {
        return $a['number'] <=> $b['number']; // Ascending order
    });

    $tab_li_elements = '';
    $first = true;
    foreach ( $places as $place ) {

        if ( $place['object_sub_type'] != 'classroom' ) continue;

        $tab_li_elements .= '
                <li class="nav-item">
                    <a class="text-decoration-none nav-link ' . ($first ? 'active' : '') . '" ' . ($first ? 'aria-current="page"' : "") . ' href="#" data-place-id="' . $place['id'] . '">
                    ' . $place['number'] . '
                    </a>
                </li>
            ';

        $first = false;
    }

    echo '
    
        <!---->

        <div class="alert alert-warning d-none" role="alert" id="alert">Carregando....</div>

        <!-- HEADER BUTTONS -->

        <div class="d-flex justify-content-start gap-2 mb-4">
            <a href="/adicionar-reserva" class="btn btn-outline-success text-decoration-none w-button">
                <i class="bi bi-plus-lg"></i>
                Adicionar
            </a>
            <a href="/assistente-de-reservas-de-salas" class="btn btn-outline-warning text-decoration-none">
                <i class="bi bi-magic"></i>
                Assistente
            </a>
            <a href="#" class="btn btn-outline-secondary text-decoration-none">
                <i class="bi bi-printer"></i>
                Imprimir
            </a>
        </div>

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

        <!-- TABS -->

        <ul class="nav nav-tabs" id="ul_place_tabs">
            ' . $tab_li_elements . '
        </ul>
        
        <!-- CALENDER -->

         <div id="calendar"></div>

         <br />

        <!-- TABLES -->

        <h4>Lista</h4>

        <div id="table-wrapper"></div>

        <!-- MODALS -->

        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#exampleModal">
            Launch demo modal
        </button>

        <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title m-0">Detalhes</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <table class="table table-borderless border-0">
                            <tbody>
                                <tr>
                                    <td class="text-body">Título</td>
                                    <td id="modal_event_title" class="text-body-emphasis">--</td>
                                </tr>
                                <tr>
                                    <td class="text-body">Início</td>
                                    <td id="modal_event_start" class="text-body-emphasis">--</td>
                                </tr>
                                <tr>
                                    <td class="text-body">Fim</td>
                                    <td id="modal_event_end" class="text-body-emphasis">--</td>
                                </tr>
                                <tr>
                                    <td class="text-body">Dono</td>
                                    <td id="modal_event_owner" class="text-body-emphasis">--</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="modal-footer">
                        <a class="btn btn-light text-decoration-none" href="/vizualizar-objeto/" target="_blank">
                            <i class="bi bi-info-lg"></i>
                            Mais
                        </a>
                        <button type="button" class="btn btn-primary">
                            <i class="bi bi-pencil"></i>
                            Editar
                        </button>
                        <button type="button" class="btn btn-danger">
                            <i class="bi bi-trash"></i>
                            Excluir
                        </button>
                    </div>
                </div>
            </div>
        </div>

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

function intranet_fafar_excluir_objeto() {

    if ( is_admin() ) return;

    if ( ! isset( $_GET['id'] ) ) {

        echo '
            <script>
                alert("No ID passed!");
                window.history.back();
            </script>
        ';

        return;

    }

    if ( ! isset( $_GET['confirmed'] ) ) {

        echo '<script>
                const r = confirm("\nExcluir objeto?\n\nNão poderá ser desfeito!\n");

                if(r){
                    window.location.href = "./excluir-objeto/?id=' . $_GET['id'] . '&confirmed=1";
                } else {
                    window.history.back();
                }
            </script>';

    return;

    }

    global $wpdb;

    $table_name = $wpdb->prefix . 'fafar_cf7crud_submissions';

    $id = sanitize_text_field( $_GET['id'] );

    $res = $wpdb->delete(
        $table_name,
        array(
            'id' => $id,
        )
    );

    if ( ! $res ) {

        echo '
            <script>
                alert("No object found!");
                window.history.back();
            </script>
        ';

        return;

    }

    echo '
        <script> 
            window.history.back();
        </script>
    ';

}