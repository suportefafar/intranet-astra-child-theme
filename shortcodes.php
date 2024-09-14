<?php

add_shortcode( 'intranet_fafar_sidebar_profile', 'intranet_fafar_sidebar_profile' );

add_shortcode( 'intranet_fafar_sidebar_menu', 'intranet_fafar_sidebar_menu' );

add_shortcode( 'intranet_fafar_logs', 'intranet_fafar_logs' );

add_shortcode( 'intranet_fafar_disciplinas', 'intranet_fafar_disciplinas' );

add_shortcode( 'intranet_fafar_salas', 'intranet_fafar_salas' );

add_shortcode( 'intranet_fafar_importar_json', 'intranet_fafar_importar_json' );

add_shortcode( 'intranet_fafar_importar_reservas', 'intranet_fafar_importar_reservas' );

add_shortcode( 'intranet_fafar_vizualizar_objeto', 'intranet_fafar_vizualizar_objeto' );

add_shortcode( 'intranet_fafar_reservas_por_sala', 'intranet_fafar_reservas_por_sala' );

add_shortcode( 'intranet_fafar_reservas_por_disciplina', 'intranet_fafar_reservas_por_disciplina' );

add_shortcode( 'intranet_fafar_assistente_de_reservas', 'intranet_fafar_assistente_de_reservas' );


function intranet_fafar_sidebar_profile() {

    $user       = wp_get_current_user();
    $avatar_url = get_avatar_url( $user->get( 'ID' ) );
    $user_meta = get_userdata( $user->get( 'ID' ) );
    $main_role = $user_meta->roles[0];

    echo '
        <div class="d-flex gap-4 mb-5">
            <a href="https://intranet.farmacia.ufmg.br/membros/' . $user->get( 'user_login' ) . '/profile/change-avatar/">
                <img src="' . $avatar_url . '" width="64" alt="User profile avatar" />
            </a>

            <div class="d-flex flex-column justify-content-center gap-1">
                <h5 class="p-0 m-0">
                    <a href="https://intranet.farmacia.ufmg.br/membros/' . $user->get( 'user_login' ) . '/">' . 
                    $user->get( 'display_name' ) . 
                    '</a>
                    </h5>
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

    $user = wp_get_current_user();
    print_r( $user );
    

    echo '
    
        <!-- HEADER BUTTONS 

        <div class="d-flex justify-content-end gap-2 mb-4">
            <a href="/adicionar-disciplina" class="btn btn-outline-primary text-decoration-none w-button">
                <i class="bi bi-plus-lg"></i>
                Disciplina
            </a>
        </div>
        
        -->

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
        echo '<h5>Nenhum ID informado</h5>';
        return;
    }


    global $wpdb;

    $table_name = $wpdb->prefix . 'fafar_cf7crud_submissions';
    
    $query = "SELECT * FROM `" . $table_name . "` WHERE id='" . $_GET["id"] . "'";

    $submissions = $wpdb->get_results( $query );

    $submission = $submissions[0];

    $submission_decoded = (array) json_decode( $submission->data );
    
    $submission_decoded["id"]           = $submission->id;
    $submission_decoded["form_id"]      = $submission->form_id;
    $submission_decoded["object_name"]  = $submission->object_name;
    $submission_decoded["is_active"]    = $submission->is_active;
    $submission_decoded["updated_at"]   = $submission->updated_at;
    $submission_decoded["created_at"]   = $submission->created_at;

    echo "<pre>";
    print_r($submission_decoded);
    echo "</pre>";

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
    
    <form class="my-3" action="/importar-json" method="POST">

        <div class="form-floating mb-3">
            <input type="text" class="form-control" id="floatingInput" name="object_name"/>
            <label for="floatingInput">Nome do Objeto</label>
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

function intranet_fafar_disciplinas() {

    echo '
    
        <!-- HEADER BUTTONS -->

        <div class="d-flex justify-content-end gap-2 mb-4">
            <a href="/adicionar-disciplina" class="btn btn-outline-primary text-decoration-none w-button">
                <i class="bi bi-plus-lg"></i>
                Disciplina
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

        <div class="d-flex justify-content-end gap-2 mb-4">
            <a href="/assistente-de-reservas-de-salas" class="btn btn-outline-warning text-decoration-none">
                <i class="bi bi-magic"></i>
                Assistente
            </a>
            <a href="/adicionar-sala" class="btn btn-outline-primary text-decoration-none">
                <i class="bi bi-plus-lg"></i>
                Sala
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

            if( ! str_starts_with( $json_item->codigo, "ACT" ) &&
                ! str_starts_with( $json_item->codigo, "ALM" ) &&
                ! str_starts_with( $json_item->codigo, "FAF" ) &&
                ! str_starts_with( $json_item->codigo, "FAS" ) &&
                ! str_starts_with( $json_item->codigo, "PFA" ) ) continue;

            
            $json_item->descricao = $json_item->codigo . " " . $json_item->turma . " " . $json_item->nome;

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
    
    <form class="my-3" action="/importar-json" method="POST">

        <div class="form-floating mb-3">
            <input type="text" class="form-control" id="floatingInput" name="object_name"/>
            <label for="floatingInput">Nome do Objeto</label>
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

function intranet_fafar_reservas_por_disciplina() {

    if( ! isset( $_GET["id"] ) ) {
        echo '<h5>Nenhum ID informado</h5>';
        return;
    }


    global $wpdb;

    $table_name = $wpdb->prefix . 'fafar_cf7crud_submissions';
    
    $query = "SELECT * FROM `" . $table_name . "` WHERE id='" . $_GET["id"] . "'";
    
    $submissions = $wpdb->get_results( $query );

    $disciplina_data = (array) json_decode( $submissions[0]->data );

    echo '

        <!-- HEADER BUTTONS -->

        <div class="d-flex flex-column gap-2 mb-2">
            <h4>
            ' . $disciplina_data["codigo"] . '
            </h4>
            <h5 class="text-secondary">
            ' . ($disciplina_data["nome"] ?? '') . '
            </h5>   
            <div class="d-flex gap-2">
                <h6 class="text-secondary">
                Turma: ' . $disciplina_data["turma"] . ' / 
                Departamento: ' . $disciplina_data["departamento"] . 'ª / 
                Carga Horária:  ' . $disciplina_data["cargaHoraria"] . ' 
                </h6>   
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mb-5">
            <a href="/adicionar-reserva-por-disciplina?disciplina=' . $_GET["id"] . '" class="btn btn-outline-primary text-decoration-none disabled w-button">
                <i class="bi bi-plus-lg"></i>
                Adicionar
            </a>
        </div>

        <!-- CALENDAR RENDER -->

        <div id="calendar" class="mb-4"></div>

        <!-- TABLES -->

        <div id="table-wrapper" class="mb-4"></div>
    ';
}

function intranet_fafar_reservas_por_sala() {

    if( ! isset( $_GET["id"] ) ) {
        echo '<h5>Nenhum ID informado</h5>';
        return;
    }


    global $wpdb;

    $table_name = $wpdb->prefix . 'fafar_cf7crud_submissions';
    
    $query = "SELECT * FROM `" . $table_name . "` WHERE id='" . $_GET["id"] . "'";
    
    $submissions = $wpdb->get_results( $query );

    $disciplina_data = (array) json_decode( $submissions[0]->data );

    echo '

        <!-- HEADER BUTTONS -->

        <div class="d-flex flex-column gap-2 mb-2">
            <div class="d-flex gap-2">
                <h4>
                ' . $disciplina_data["numero"] . '
                </h4>
                <h5 class="text-secondary">
                ' . ($disciplina_data["descricao"] ?? '') . '
                </h5>   
            </div>
            <div class="d-flex gap-2">
                <h6 class="text-secondary">
                Bloco: ' . $disciplina_data["bloco"] . ' / 
                Andar: ' . $disciplina_data["andar"] . 'ª / 
                Capacidade:  ' . $disciplina_data["capacidade"] . ' 
                </h6>   
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mb-5">
            <a href="/assistente-de-reservas-de-salas" class="btn btn-outline-secondary text-decoration-none w-button">
                <i class="bi bi-printer"></i>
                Imprimir
            </a>
            <a href="/adicionar-reserva-por-sala?sala=' . $_GET["id"] . '" class="btn btn-outline-primary text-decoration-none w-button">
                <i class="bi bi-plus-lg"></i>
                Reserva
            </a>
        </div>

        <!-- CALENDAR RENDER -->

        <div id="calendar" class="mb-4"></div>

        <!-- TABLES -->

        <div id="table-wrapper" class="mb-4"></div>
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