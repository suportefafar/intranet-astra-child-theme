<?php

add_filter( 'rest_authentication_errors', '__return_true' );


add_action( 'rest_api_init', 'intranet_fafar_api_register_submission_routes' );

/**
 * This function is where we register our routes for our example endpoint.
 */
function intranet_fafar_api_register_submission_routes() {
    // Here we are registering our route for a collection of submissions and creation of submissions.
    register_rest_route( 'intranet/v1', '/submissions', array(
            // By using this constant we ensure that when the WP_REST_Server changes, our create endpoints will work as intended.
            'methods'  => WP_REST_Server::CREATABLE,
            // Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
            'callback' => 'intranet_fafar_api_create_submission',
            /* 
             * Define permissions for access to this end point
             * 'permission_callback' => '__return_true', // Allows all users to access for simplicity.
             * Ensure only logged-in users can access:
             */ 
            'permission_callback' => function() {
                return is_user_logged_in();
            }
    ) );


    // READABLE

    register_rest_route( 'intranet/v1', '/submissions/service_tickets/by_user', array(
        // By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
        'methods'  => WP_REST_Server::READABLE,
        // Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
        'callback' => 'intranet_fafar_api_get_service_tickets_by_user_handler',
    ) );

    register_rest_route( 'intranet/v1', '/submissions/service_tickets/by_departament', array(
        // By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
        'methods'  => WP_REST_Server::READABLE,
        // Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
        'callback' => 'intranet_fafar_api_get_service_tickets_by_departament_handler',
    ) );

    register_rest_route( 'intranet/v1', '/submissions/service_ticket_updates/by_service_ticket', array(
        // By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
        'methods'  => WP_REST_Server::READABLE,
        // Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
        'callback' => 'intranet_fafar_api_get_service_ticket_updates_by_service_ticket_handler',
    ) );

    register_rest_route( 'intranet/v1', '/submissions/equipaments', array(
        // By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
        'methods'  => WP_REST_Server::READABLE,
        // Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
        'callback' => 'intranet_fafar_api_get_equipaments_handler',
    ) );

    register_rest_route( 'intranet/v1', '/submissions/place/available', array(
        // By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
        'methods'  => WP_REST_Server::READABLE,
        // Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
        'callback' => 'intranet_fafar_api_get_places_available',
    ) );

    register_rest_route( 'intranet/v1', '/submissions/(?P<id>[\w]+)', array(
        // By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
        'methods'  => WP_REST_Server::READABLE,
        // Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
        'callback' => 'intranet_fafar_api_get_submission_by_id_handler',
    ) );

    register_rest_route( 'intranet/v1', '/users/(?P<id>[\w]+)', array(
        // By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
        'methods'  => WP_REST_Server::READABLE,
        // Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
        'callback' => 'intranet_fafar_api_get_user_by_id_handler',
    ) );

    register_rest_route( 'intranet/v1', '/submissions/object/(?P<object>[\w]+)', array(
        // By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
        'methods'  => WP_REST_Server::READABLE,
        // Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
        'callback' => 'intranet_fafar_api_get_submissions_by_object_name_handler',
    ) );

    register_rest_route( 'intranet/v1', '/submissions/(?P<place>[\w]+)/reservations', array(
        // By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
        'methods'  => WP_REST_Server::READABLE,
        // Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
        'callback' => 'intranet_fafar_api_get_place_reservations',
    ) );

    register_rest_route( 'intranet/v1', '/submissions/reservations/(?P<id>[\w]+)', array(
        // By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
        'methods'  => WP_REST_Server::READABLE,
        // Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
        'callback' => 'intranet_fafar_api_get_reservation_by_id_handler',
    ) );
    

    // EDITABLE

    register_rest_route( 'intranet/v1', '/submissions/(?P<id>[\w]+)', array(
        // By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
        'methods'  => WP_REST_Server::EDITABLE,
        // Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
        'callback' => 'intranet_fafar_api_update_submission_by_id_handler',
    ) );

    // DELETABLE

    register_rest_route( 'intranet/v1', '/submissions/(?P<id>[\w]+)', array(
        // By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
        'methods'  => WP_REST_Server::DELETABLE,
        // Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
        'callback' => 'intranet_fafar_api_delete_submission_by_id_handler',
    ) );

}

function intranet_fafar_api_update_submission_by_id_handler( $request ) {

    $id = (string) $request['id'];

    // Get data from the request
    $submission = $request->get_json_params();

    error_log( print_r( $submission, true ) );

    $submission = intranet_fafar_api_update( $id, $submission );

    if ( isset( $submission['error_msg'] ) ) {

        return new WP_Error( 'rest_api_sad', esc_html__( $submission['error_msg'], 'intranet-fafar-api' ), ( ( $submission['http_status'] ) ?? 400 ) );

    }

    return rest_ensure_response( json_encode( $submission ) );

}

function intranet_fafar_api_delete_submission_by_id_handler( $request ) {

    $id = (string) $request['id'];

    $submission = intranet_fafar_api_delete_submission_by_id( $id );

    if ( isset( $submission['error_msg'] ) ) {

        return new WP_Error( 'rest_api_sad', esc_html__( $submission['error_msg'], 'intranet-fafar-api' ), ( ( $submission['http_status'] ) ?? 400 ) );

    }

    return rest_ensure_response( json_encode( $submission ) );

}

function intranet_fafar_api_delete_submission_by_id( $id ) {

    if( ! $id ) 
        return array( 'error_msg' => '[0101]No id informed.', 'http_status' => 400 );

    $submission = intranet_fafar_api_get_submission_by_id( $id );

    if ( isset( $submission['error_msg'] ) )
        return $submission;

    $submission = intranet_fafar_api_delete( $submission, $delete_permanently = false, $check_permissions = true );

    return $submission;
}

function intranet_fafar_api_get_place_reservations( $request ) {

    $place_id = (string) $request['place'];

    $submissions = intranet_fafar_api_get_reservations_by_place( $place_id );

    if ( isset( $submissions['error_msg'] ) ) {

        return new WP_Error( 'rest_api_sad', esc_html__( $submissions['error_msg'], 'intranet-fafar-api' ), $submissions['http_status'] );

    }

    // return rest_ensure_response( 
    //         json_encode( 
    //             intranet_fafar_api_bff_reservations_prepare( 
    //                 $submissions,
    //                 array( 'id', 'rrule', 'duration', 'desc', 'discipline', 'owner', 'applicant', 'place' )
    //             ) 
    //         ) 
    //     );

    return rest_ensure_response( json_encode( $submissions ) );

}

function intranet_fafar_api_bff_reservations_prepare( $reservations, $attr_wl ) {


    $arr = array();
    foreach ( $reservations as $reservation ) {

        $item_arr = array();
        foreach ( $reservation as $key => $value ) {

            if ( ! in_array( $key, $attr_wl ) ) continue;
            
            $value = ( is_array( $value ) ? $value[0] : $value );
            if ( $key == 'discipline' ) {

                $discipline = (array) intranet_fafar_api_get_submission_by_id( $value );
                $item_arr['discipline'] = array( 'id' => $discipline['id'], 
                                                 'code' => $discipline['code'], 
                                                 'name_of_subject' => $discipline['name_of_subject'], 
                                                 'group' => $discipline['group'] );

                continue;

            }

            $item_arr[$key] = $value;

        }

        array_push( $arr, $item_arr );

    }

    return $arr;

}

function intranet_fafar_api_create_new_loan( $form_data, $contact_form ) {
    
    // Verificações iniciais
    if( ! isset( $form_data['object_name'] ) ) return $form_data;

    if ( $form_data['object_name'] !== 'equipament_loan' ) return $form_data;

    //error_log(print_r($form_data, true));

    $form_data['data'] = json_decode( $form_data['data'], true );

    if ( ! isset( $form_data['data']['loan_date'] ) )
        return array( 'error_msg' => '[001] Data de empréstimo não informada!' );

    $equipament = intranet_fafar_api_get_submission_by_id( $form_data['data']['equipament'] );

    if ( ! $equipament )
        return array( 'error_msg' => '[001] Equipamento não existe!' );

    if ( isset( $equipament['data']['on_loan'] ) && $equipament['data']['on_loan'] )
        return array( 'error_msg' => '[002] Equipamento está emprestado!' );

    $equipament['data']['on_loan'] = '1';

    $equipament = intranet_fafar_api_update( $equipament['id'], $equipament );

    if ( isset( $equipament['error_msg'] ) )
        return array( 'error_msg' => $equipament['error_msg'] );

    $form_data['data'] = json_encode( $form_data['data'] );

    return $form_data;
}

function intranet_fafar_api_register_loan_return( $form_data, $contact_form ) { 

    // Verificações iniciais
    if ( ! isset( $form_data['object_name'] ) ) return $form_data;

    if ( $form_data['object_name'] !== 'equipament_loan_return' ) return $form_data;

    //error_log(print_r($form_data, true));

    // Atualizando a propriedade 'on_loan' do equipamento
    $form_data['data'] = json_decode( $form_data['data'], true );

    if ( ! isset( $form_data['data']['return_date'] ) )
        return array( 'error_msg' => '[001] Data de retorno não informada!' );

    $equipament = intranet_fafar_api_get_submission_by_id( $form_data['data']['equipament'] );

    if ( ! $equipament )
        return array( 'error_msg' => '[001] Equipamento não existe!' );

    $equipament['data']['on_loan'] = 0;

    $equipament = intranet_fafar_api_update( $equipament['id'], $equipament );

    if ( isset( $res['error_msg'] ) )
        return array( 'error_msg' => $res['error_msg'] );

    // Atualizando o status do empréstimo do equipamento
    $loans = intranet_fafar_api_get_loans_by_equipament( $form_data['data']['equipament'] );

    $loan = $loans[0];

    if ( ! $loan )
        return array( 'error_msg' => '[001] Equipamento atualizado. Porém, ' . $loan['error_msg'] );

    $loan['data']['returned']    = '1';
    $loan['data']['return_date'] = $form_data['data']['return_date']; // Verificado no topo
    $loan['data']['return_desc'] = ( ( $form_data['data']['return_desc'] ) ?? '');

    $loan = intranet_fafar_api_update( $loan['id'], $loan );

    if ( isset( $loan['error_msg'] ) )
        return array( 'error_msg' => '[001] Equipamento atualizado. Porém, ' . $loan['error_msg'] );

    // Retorna uma obj genérico para concluir a submissão com sucesso
    return array( 'far_prevent_submit' => true );

}

function intranet_fafar_api_insert_update_on_service_ticket( $form_data, $contact_form ) {
   
       // Verificações iniciais
       if ( ! isset( $form_data['object_name'] ) ) return $form_data;

       if ( $form_data['object_name'] !== 'service_ticket_update' ) return $form_data;
   
       error_log(print_r($form_data, true));
   
       // Atualizando a propriedade 'status' da ordem de serviço
       $form_data['data'] = json_decode( $form_data['data'], true );
   
       if ( ! isset( $form_data['data']['status'][0] ) )
           return array( 'error_msg' => '[001] Status não informado!' );
   
       $service_ticket = intranet_fafar_api_get_submission_by_id( $form_data['data']['service_ticket'] );
   
       if ( ! $service_ticket )
           return array( 'error_msg' => '[002] Ordem de serviço não existe!' );
   
       $service_ticket['data']['status'] = $form_data['data']['status'][0];
   
       $service_ticket = intranet_fafar_api_update( $service_ticket['id'], $service_ticket );

       if ( isset( $service_ticket['error_msg'] ) )
           return array( 'error_msg' => $service_ticket['error_msg'] );

       // Se tudo deu certo, então apenas retorna o objeto para ser inserido
       $form_data['data'] = json_encode( $form_data['data'] );

       // Retorna uma obj genérico para concluir a submissão com sucesso
       return $form_data;
    
}

function intranet_fafar_api_get_loans_by_equipament( $id ) {

    global $wpdb;

    $table_name = $wpdb->prefix . 'fafar_cf7crud_submissions';

    if( ! $id ) {

        return array( 'error_msg' => '[0101] No "id" found.', 'http_status' => 400 );

    }

    /* 
     * Montando a query SQL.
     * Pesquisa por equipamento com o id informado e 
     * ordena do empréstimo mais recente ao mais antigo
     */
    $query = "SELECT * FROM `SET_TABLE_NAME` WHERE ";

    $query .= 'JSON_CONTAINS( data, \'' . json_encode( array( 'equipament' => $id ) ) . '\')';

    $query .= " ORDER BY created_at DESC";

    // Fluxo padrão de leitura
    $submissions = intranet_fafar_api_read( $query );

    if ( ! $submissions || count( $submissions ) == 0 ) {

        return array( 'error_msg' => '[0102] No submission found with id "' . ( $id ?? 'UNKNOW_ID') . '"', 'http_status' => 400 );

    }

    return $submissions;
}

function intranet_fafar_api_get_service_tickets_by_user_handler( $request ) {

    $submissions = intranet_fafar_api_get_service_tickets_by_user();

    if ( isset( $submissions['error_msg'] ) ) {

        return new WP_Error( 'rest_api_sad', esc_html__( $submissions['error_msg'], 'intranet-fafar-api' ), $submissions['http_status'] );

    }

    return rest_ensure_response( json_encode( $submissions ) );

}

function intranet_fafar_api_get_service_tickets_by_user() {

    $user    = wp_get_current_user();
    $user_id = $user->ID;

    //$query = "SELECT * FROM `SET_TABLE_NAME` WHERE `object_name` = 'service_ticket' AND JSON_CONTAINS(data, '\"" . $new_code . "\"', '$.code')";

    $query = "SELECT * FROM `SET_TABLE_NAME` WHERE `object_name` = 'service_ticket' AND `owner` = '" . $user_id . "' ORDER BY created_at DESC";

    // Se for Administrador, então acessa todas as ordens de serviço
    if ( $user_id === 1 )
        $query = "SELECT * FROM `SET_TABLE_NAME` WHERE `object_name` = 'service_ticket'";


    $service_tickets = intranet_fafar_api_read( $query );

    if ( isset( $service_tickets['error_msg'] ) )
        return array( 'error_msg' => $service_ticket['error_msg'] );

    if ( empty( $service_tickets ) )
        return array( 'error_msg' => '[323] Nenhuma ordem de serviço encontrada do usuário atual!' );
    
    for ( $i = 0; $i < sizeof( $service_tickets ); $i++ ) {

       /*
        * Substituir os campos que tem ID de outro objeto,
        * pelo objeto de mesmo ID
        */ 
        if ( isset( $service_tickets[$i]['owner'] ) && is_numeric( $service_tickets[$i]['owner'] ) )
            $service_tickets[$i]['owner'] = intranet_fafar_api_get_user_by_id( $service_tickets[$i]['owner'] );

        if ( isset( $service_tickets[$i]['data']['place'][0] ) )
            $service_tickets[$i]['data']['place'] = intranet_fafar_api_get_submission_by_id( $service_tickets[$i]['data']['place'][0] );

        if ( isset( $service_tickets[$i]['data']['departament_assigned_to'][0] ) ) {

            // Get the display name of the role
            $role_slug = $service_tickets[$i]['data']['departament_assigned_to'][0];
                
            $role_display_name = '--';
                
            if ( isset( wp_roles()->roles[ $role_slug ] ) )
                $role_display_name = wp_roles()->roles[ $role_slug ]['name'];

            $service_tickets[$i]['data']['departament_assigned_to'] = array( 'role_slug' => $role_slug, 'role_display_name' => $role_display_name );

        }

        
        if ( isset( $service_tickets[$i]['data']['assigned_to'] ) ) {

            $service_tickets[$i]['data']['assigned_to'] = intranet_fafar_api_get_user_by_id( $service_tickets[$i]['data']['assigned_to'] );

        }

    }

    return $service_tickets;    

}

function intranet_fafar_api_get_service_tickets_by_departament_handler( $request ) {

    // Get all query parameters
    $query_params = $request->get_query_params();

    if ( isset( $query_params['status'] ) ) {
    
        $status = $query_params['status'];

        $submissions  = intranet_fafar_api_get_service_tickets_by_departament( null, $status );

    } else {

        $submissions  = intranet_fafar_api_get_service_tickets_by_departament();

    }

    if ( isset( $submissions['error_msg'] ) ) {

        return new WP_Error( 'rest_api_sad', esc_html__( $submissions['error_msg'], 'intranet-fafar-api' ), $submissions['http_status'] );

    }

    return rest_ensure_response( json_encode( $submissions ) );

}

function intranet_fafar_api_get_service_tickets_by_departament( $departament = null, $status = null ) {

    if ( ! $departament ) {

        $user        = wp_get_current_user();

        $role_slug   = $user->roles[0];

        $departament = $role_slug;

    }

    $query = "SELECT * FROM `SET_TABLE_NAME` WHERE `object_name` = 'service_ticket' AND JSON_CONTAINS(data, '\"" . $departament . "\"', '$.departament_assigned_to') ORDER BY created_at DESC";

    $service_tickets = intranet_fafar_api_read( $query );

    if ( isset( $service_tickets['error_msg'] ) )
        return array( 'error_msg' => $service_ticket['error_msg'] );

    if ( empty( $service_tickets ) )
        return array( 'error_msg' => '[323] Nenhuma ordem de serviço encontrada do usuário atual!' );
    
    for ( $i = 0; $i < sizeof( $service_tickets ); $i++ ) {

        if ( 
            $status && 
            strtolower( $status ) !== strtolower( $service_tickets[$i]['data']['status'] ) 
           ) {

            continue;

        }

       /*
        * Substituir os campos que tem ID de outro objeto,
        * pelo objeto de mesmo ID
        */ 
        if ( isset( $service_tickets[$i]['owner'] ) && is_numeric( $service_tickets[$i]['owner'] ) )
            $service_tickets[$i]['owner'] = intranet_fafar_api_get_user_by_id( $service_tickets[$i]['owner'] );

        if ( isset( $service_tickets[$i]['data']['place'][0] ) )
            $service_tickets[$i]['data']['place'] = intranet_fafar_api_get_submission_by_id( $service_tickets[$i]['data']['place'][0] );

        if ( isset( $service_tickets[$i]['data']['departament_assigned_to'][0] ) ) {

            // Get the display name of the role
            $role_slug = $service_tickets[$i]['data']['departament_assigned_to'][0];
                
            $role_display_name = '--';
                
            if ( isset( wp_roles()->roles[ $role_slug ] ) )
                $role_display_name = wp_roles()->roles[ $role_slug ]['name'];

            $service_tickets[$i]['data']['departament_assigned_to'] = array( 'role_slug' => $role_slug, 'role_display_name' => $role_display_name );

        }

        if ( isset( $service_tickets[$i]['data']['assigned_to'] ) ) {

            $service_tickets[$i]['data']['assigned_to'] = intranet_fafar_api_get_user_by_id( $service_tickets[$i]['data']['assigned_to'] );

        }

    }

    return $service_tickets;      

}

function intranet_fafar_api_get_service_ticket_by_id( $id ) {

    $service_ticket = intranet_fafar_api_get_submission_by_id( $id );

    if ( isset( $service_ticket['error_msg'] ) )
        return array( 'error_msg' => $service_ticket['error_msg'] );

    if ( empty( $service_ticket ) )
        return array( 'error_msg' => '[323] Nenhuma ordem de serviço encontrada!' );
    
    /*
     * Substituir os campos que tem ID de outro objeto,
     * pelo objeto de mesmo ID
     */ 
    if ( isset( $service_ticket['owner'] ) && is_numeric( $service_ticket['owner'] ) )
        $service_ticket['owner'] = intranet_fafar_api_get_user_by_id( $service_ticket['owner'] );

    if ( isset( $service_ticket['data']['place'][0] ) )
        $service_ticket['data']['place'] = intranet_fafar_api_get_submission_by_id( $service_ticket['data']['place'][0] );

    if ( isset( $service_ticket['data']['departament_assigned_to'][0] ) ) {

        // Get the display name of the role
        $role_slug = $service_ticket['data']['departament_assigned_to'][0];
                
        $role_display_name = '--';
                
        if ( isset( wp_roles()->roles[ $role_slug ] ) )
            $role_display_name = wp_roles()->roles[ $role_slug ]['name'];

        $service_ticket['data']['departament_assigned_to'] = array( 'role_slug' => $role_slug, 'role_display_name' => $role_display_name );

    }

    if ( isset( $service_tickets['data']['assigned_to'] ) ) {

        $service_tickets['data']['assigned_to'] = intranet_fafar_api_get_user_by_id( $service_tickets['data']['assigned_to'] );

    }

    return $service_ticket;    

}

function intranet_fafar_api_get_service_ticket_updates_by_service_ticket_handler( $request ) {

    $service_ticket_id = (string) $request['id'];

    $submissions = intranet_fafar_api_get_service_ticket_updates_by_service_ticket( $service_ticket_id );

    if ( isset( $submissions['error_msg'] ) ) {

        return new WP_Error( 'rest_api_sad', esc_html__( $submissions['error_msg'], 'intranet-fafar-api' ), $submissions['http_status'] );

    }

    return rest_ensure_response( json_encode( $submissions ) );

}

function intranet_fafar_api_get_service_ticket_updates_by_service_ticket( $service_ticket_id ) {

    if ( ! $service_ticket_id ) 
        return array( 'error_msg' => '[645] ID não informado!' );

    $query = "SELECT * FROM `SET_TABLE_NAME` WHERE `object_name` = 'service_ticket_update' AND JSON_CONTAINS(data, '\"" . $service_ticket_id . "\"', '$.service_ticket') ORDER BY created_at DESC";

    $service_ticket_updates = intranet_fafar_api_read( $query );

    if ( isset( $service_ticket_updates['error_msg'] ) )
        return array( 'error_msg' => $service_ticket['error_msg'] );

    if ( empty( $service_ticket_updates ) )
        return array( 'error_msg' => '[323] Nenhuma atualização da ordem de serviço encontrada do usuário atual!' );
    
    for ( $i = 0; $i < sizeof( $service_ticket_updates ); $i++ ) {

       /*
        * Substituir os campos que tem ID de outro objeto,
        * pelo objeto de mesmo ID
        */ 
        if ( isset( $service_ticket_updates[$i]['owner'] ) && is_numeric( $service_ticket_updates[$i]['owner'] ) )
            $service_ticket_updates[$i]['owner'] = intranet_fafar_api_get_user_by_id( $service_ticket_updates[$i]['owner'] );


    }

    return $service_ticket_updates;      

}

/*
 * {
 *   title: "my recurring STRING event",
 *   rrule:
 *     "DTSTART:20240201T113000\nRRULE:FREQ=WEEKLY;INTERVAL=1;UNTIL=20241201;BYDAY=MO,FR",
 * },
 * 
 * Array\n(
 *     [id] => 17310244196c882324f4
 *     [data] => {
 *          "far_prevent_submit":"1",
 *          "desc":"DESCRICAO DO EVENTO SIM",
 *          "discipline":["1728413739e86f5dc2b8"],
 *          "date":"2024-11-07",
 *          "start_time":"08:00",
 *          "end_time":"09:00",
 *          "frequency":["weekly"],
 *          "weekdays":["1","3"],
 *          "end_date":"2024-11-07",
 *          "place":["172842803339bbfade73"],
 *          "applicant":["5"],
 *          "does_post_on_fafar_website":["Publicar no site da FAFAR"]
 *      }
 *     [form_id] => 446
 *     [object_name] => reservation
 *     [owner] => 5
 *     [group_owner] => ti
 *     [remote_ip] => 150.164.110.253
 *     [submission_url] => 
 * )
 * 
 *  {
       title: "my recurring STRING event",
       rrule:
         "DTSTART:20240201T113000\nRRULE:FREQ=WEEKLY;INTERVAL=1;UNTIL=20241201;BYDAY=MO,FR",
    },
 * 
 * @param $form_data
 * @return FromData | null
*/
function intranet_fafar_api_create_or_update_reservation( $form_data, $contact_form ) {

    error_log( print_r( $form_data, true ) );

    // Verificações iniciais
    if( ! isset( $form_data['object_name'] ) ) return $form_data;

    if ( $form_data['object_name'] !== 'reservation' ) return $form_data;
    
    $new_form_data = $form_data;
    $new_form_data['data'] = json_decode( $new_form_data['data'], true );

    // Verificar se dados necessários foram informados
    if ( ! isset( $new_form_data['data']['date'] )  ||
         ! isset( $new_form_data['data']['start_time'] ) || 
         ! isset( $new_form_data['data']['end_time'] ) || 
         ! isset( $new_form_data['data']['frequency'][0] ) || 
         ! isset( $new_form_data['data']['place'][0] ) 
       ) {
            
        return array( 'error_msg' => '[001] Data, tempo, frenquência ou lugar não informado!' );
        
    }

    // Verificar se *hora* de fim é posterior ao de início
    $s = new DateTime( $new_form_data['data']['start_time'] );
    $e = new DateTime( $new_form_data['data']['end_time'] );
    if ( $s >= $e ) {

        return array( 'error_msg' => '[001] Horário de início não pode ser depois de fim!' );

    }

    // Verircar se *data* de fim é posterior ao de início, se houver data de fim
    if ( $new_form_data['data']['frequency'][0] !== 'once' && isset( $new_form_data['data']['end_date'] ) ) {

        $s = new DateTime( $new_form_data['data']['date'] );
        $e = new DateTime( $new_form_data['data']['end_date'] );

        if ( $s >= $e ) {

            return array( 'error_msg' => '[001] Data de início não pode ser depois de fim!' );
        
        }

    }

    $title = 'Reserva ' . time();

    if( isset( $new_form_data['data']['desc'] ) && 
        $new_form_data['data']['desc'] !== '' 
      ) {

        $title = $new_form_data['data']['desc'];

    } else if ( isset( $new_form_data['data']['discipline'][0] ) ) {

        $discipline = intranet_fafar_api_get_submission_by_id( $new_form_data['data']['discipline'][0]  );

        if ( $discipline ) {

            $title = $discipline['data']['code'] . ' (' . $discipline['data']['group'] . ')';

        }

    }

    // Setando a prop 'title'
    $new_form_data['data']['title'] = $title;

    if ( $new_form_data['data']['frequency'][0] === 'weekly' ) {

        if ( ! isset( $new_form_data['data']['weekdays'] ) || 
             ! isset( $new_form_data['data']['end_date'] ) 
           ) {

            return array( 'error_msg' => '[001] Dia de semana e/ou data de término não informado(s)!' );

        }

        // Gerando a prop 'dt_dstart' (Data início + Hora início) só com números
        $date = new DateTime( $new_form_data['data']['date'] );

        $time = DateTime::createFromFormat( 'H:i', $new_form_data['data']['start_time'] );

        $dt_start = $date->format( 'Ymd' ) . 'T' . $time->format('His');

        // Gerando a prop 'byday' com o array retornado pelos checkboxes do CF7
        $byday = [];

        foreach ( $new_form_data['data']['weekdays'] as $day ) {

            $date = new DateTime();

            $date->setISODate( 2024, 1, $day ); // Using week 1 of 2024

            $byday[] = strtoupper( substr( $date->format('l'), 0, 2 ) ); // Get the first two letters and convert to uppercase
            
        }

        // Gerando a prop 'until' só com números
        $date = new DateTime( $new_form_data['data']['end_date'] );

        $until = $date->format( 'Ymd' ) . 'T000000';

        /*
         * Gerando RRULE string com a: 
         * 'DTSTART:20240201T113000\nRRULE:FREQ=WEEKLY;INTERVAL=1;UNTIL=20241201;BYDAY=MO,FR'
         */
        $new_rrule = 'DTSTART:' . $dt_start . '\nRRULE:FREQ=WEEKLY;INTERVAL=1;UNTIL=' . $until . ';BYDAY=' . implode( ',', $byday );

        // Gerando a prop 'duration'
        $start = DateTime::createFromFormat('H:i', $new_form_data['data']['start_time']);
        $end = DateTime::createFromFormat('H:i', $new_form_data['data']['end_time']);

        // Calculate the difference between the two times
        $interval = $start->diff($end);

        // 'duration' é uma prop independente de 'rrule'
        $new_duration = $interval->format('%H:%I');

    } else {

        // Gerando a prop 'dt_dstart' (Data início + Hora início) só com números
        $date = new DateTime( $new_form_data['data']['date'] );

        $time = DateTime::createFromFormat( 'H:i', $new_form_data['data']['start_time'] );

        $dt_start = $date->format( 'Ymd' ) . 'T' . $time->format('His');

        /*
         * Gerando RRULE string com a: 
         * 'DTSTART:20241107T113000\nRRULE:FREQ=DAILY;COUNT=1'
         */
        $new_rrule = 'DTSTART:' . $dt_start . '\nRRULE:FREQ=DAILY;COUNT=1';

        // Gerando a prop 'duration'
        $start = DateTime::createFromFormat('H:i', $new_form_data['data']['start_time']);
        $end = DateTime::createFromFormat('H:i', $new_form_data['data']['end_time']);

        // Calculate the difference between the two times
        $interval = $start->diff($end);

        // 'duration' é uma prop independente de 'rrule'
        $new_duration = $interval->format('%H:%I');

    }


    $skip_check_overlap = false;
    if ( isset( $new_form_data['data']['rrule'] ) && $new_form_data['data']['rrule'] == $new_rrule ) {
    
        $skip_check_overlap = true;

    }

    // É, pois é.... Medo....
    $new_form_data['data']['rrule'] =  $new_rrule;

    $new_form_data['data']['duration'] =  $new_duration;

    /*
     * Sim, eu sei... Isso não é necessário. 
     * Mas é medo de colocar mais de uma forma de sair com sucesso dessa função...
     * Vou mudar re-escrever isso aqui quando o sistema de reservas já estiver bem testado.
     * Na verdade essa função toda....
     */
    if ( ! $skip_check_overlap ) {

        $existing_reservations = intranet_fafar_api_get_reservations_by_place( $new_form_data['data']['place'][0] );

        /* 
        * Gerar as datas dos reservas existentes
        * Array ( [0] => 2024-02-05 00:00:00 [1] => 2024-02-02 00:00:00 [2] => ...
        */ 
        $new_reservation_timestamps = intranet_fafar_rrule_get_all_occurrences( $new_form_data['data']['rrule'] );

        // Aqui temos timestamps das reservas à ser registradas
        foreach ( $new_reservation_timestamps as $new_reservation_timestamp ) {

            foreach ( $existing_reservations as $existing_reservation ) { 

                // Aqui estamos gerando as timestamps de cada evento registrado
                $existing_reservation_timestamps = intranet_fafar_rrule_get_all_occurrences( $existing_reservation['data']['rrule'] );

                foreach ( $existing_reservation_timestamps as $existing_reservation_timestamp ) {

                    $existing = intranet_fafar_api_get_event_start_and_end( $existing_reservation_timestamp, $existing_reservation['data']['duration'] );

                    $new      = intranet_fafar_api_get_event_start_and_end( $new_reservation_timestamp, $new_form_data['data']['duration'] );

                    if ( intranet_fafar_api_does_reservations_overlaps( $existing, $new ) )
                        return array( 'error_msg' => '[423] Sala indisponível nesse horário!' );

                }

            }

        }
    }

    // Se tudo deu certo, então devolve o objeto para ser inserido pelo plugin 'fafar-cf7crud'
    $form_data['data'] = json_encode( $new_form_data['data'] );

    return $form_data;

}

/**
 * This is the function that checks whitch classroom is available
 * 
*/
function intranet_fafar_api_check_for_available_classrooms( ) {

    if ( $new_form_data['data']['frequency'][0] === 'weekly' ) {

        if ( ! isset( $new_form_data['data']['weekdays'] ) || 
             ! isset( $new_form_data['data']['end_date'] ) 
           ) {

            return array( 'error_msg' => '[001] Dia de semana e/ou data de término não informado(s)!' );

        }

        // Gerando a prop 'dt_dstart' (Data início + Hora início) só com números
        $date = new DateTime( $new_form_data['data']['date'] );

        $time = DateTime::createFromFormat( 'H:i', $new_form_data['data']['start_time'] );

        $dt_start = $date->format( 'Ymd' ) . 'T' . $time->format('His');

        // Gerando a prop 'byday' com o array retornado pelos checkboxes do CF7
        $byday = [];

        foreach ( $new_form_data['data']['weekdays'] as $day ) {

            $date = new DateTime();

            $date->setISODate( 2024, 1, $day ); // Using week 1 of 2024

            $byday[] = strtoupper( substr( $date->format('l'), 0, 2 ) ); // Get the first two letters and convert to uppercase
            
        }

        // Gerando a prop 'until' só com números
        $date = new DateTime( $new_form_data['data']['end_date'] );

        $until = $date->format( 'Ymd' ) . 'T000000';

        /*
         * Gerando RRULE string com a: 
         * 'DTSTART:20240201T113000\nRRULE:FREQ=WEEKLY;INTERVAL=1;UNTIL=20241201;BYDAY=MO,FR'
         */
        $new_rrule = 'DTSTART:' . $dt_start . '\nRRULE:FREQ=WEEKLY;INTERVAL=1;UNTIL=' . $until . ';BYDAY=' . implode( ',', $byday );

        // Gerando a prop 'duration'
        $start = DateTime::createFromFormat('H:i', $new_form_data['data']['start_time']);
        $end = DateTime::createFromFormat('H:i', $new_form_data['data']['end_time']);

        // Calculate the difference between the two times
        $interval = $start->diff($end);

        // 'duration' é uma prop independente de 'rrule'
        $new_duration = $interval->format('%H:%I');

    } else {

        // Gerando a prop 'dt_dstart' (Data início + Hora início) só com números
        $date = new DateTime( $new_form_data['data']['date'] );

        $time = DateTime::createFromFormat( 'H:i', $new_form_data['data']['start_time'] );

        $dt_start = $date->format( 'Ymd' ) . 'T' . $time->format('His');

        /*
         * Gerando RRULE string com a: 
         * 'DTSTART:20241107T113000\nRRULE:FREQ=DAILY;COUNT=1'
         */
        $new_rrule = 'DTSTART:' . $dt_start . '\nRRULE:FREQ=DAILY;COUNT=1';

        // Gerando a prop 'duration'
        $start = DateTime::createFromFormat('H:i', $new_form_data['data']['start_time']);
        $end = DateTime::createFromFormat('H:i', $new_form_data['data']['end_time']);

        // Calculate the difference between the two times
        $interval = $start->diff($end);

        // 'duration' é uma prop independente de 'rrule'
        $new_duration = $interval->format('%H:%I');

    }


    $skip_check_overlap = false;
    if ( isset( $new_form_data['data']['rrule'] ) && $new_form_data['data']['rrule'] == $new_rrule ) {
    
        $skip_check_overlap = true;

    }

    // É, pois é.... Medo....
    $new_form_data['data']['rrule'] =  $new_rrule;

    $new_form_data['data']['duration'] =  $new_duration;

    /*
     * Sim, eu sei... Isso não é necessário. 
     * Mas é medo de colocar mais de uma forma de sair com sucesso dessa função...
     * Vou mudar re-escrever isso aqui quando o sistema de reservas já estiver bem testado.
     * Na verdade essa função toda....
     */

    $existing_reservations = intranet_fafar_api_get_reservations_by_place( $new_form_data['data']['place'][0] );
    /* 
    * Gerar as datas dos reservas existentes
    * Array ( [0] => 2024-02-05 00:00:00 [1] => 2024-02-02 00:00:00 [2] => ...
    */ 
    $new_reservation_timestamps = intranet_fafar_rrule_get_all_occurrences( $new_form_data['data']['rrule'] );
    // Aqui temos timestamps das reservas à ser registradas
    foreach ( $new_reservation_timestamps as $new_reservation_timestamp ) {
        foreach ( $existing_reservations as $existing_reservation ) { 
            // Aqui estamos gerando as timestamps de cada evento registrado
            $existing_reservation_timestamps = intranet_fafar_rrule_get_all_occurrences( $existing_reservation['data']['rrule'] );
            foreach ( $existing_reservation_timestamps as $existing_reservation_timestamp ) {
                $existing = intranet_fafar_api_get_event_start_and_end( $existing_reservation_timestamp, $existing_reservation['data']['duration'] );
                $new      = intranet_fafar_api_get_event_start_and_end( $new_reservation_timestamp, $new_form_data['data']['duration'] );
                if ( intranet_fafar_api_does_reservations_overlaps( $existing, $new ) )
                    return array( 'error_msg' => '[423] Sala indisponível nesse horário!' );
            }
        }
    }

}

/**
 * 
 * @param String $timestamp    Normal DateTime str timestamp
 * @param String $duration_str DateTime->format('%H:%I') string format
*/
function intranet_fafar_api_get_event_start_and_end( $timestamp, $duration_str ) {

    if ( ! $timestamp )
        return false;

    if ( ! $duration_str )
        return false;

    // Convert the start timestamp string into a DateTime object
    $startDateTime = new DateTime();

    $startDateTime->setTimestamp( (int) $timestamp );

    // Parse the duration string (e.g., "02:30")
    list( $hours, $minutes ) = explode( ':', $duration_str );

    // Clone the start datetime to avoid modifying the original
    $endDateTime = clone $startDateTime;

    // Add the duration to the start time
    $endDateTime->modify( "+{$hours} hours +{$minutes} minutes" );

    // Return the start and end times as timestamps
    return array(
        'start' => $startDateTime->getTimestamp(),
        'end' => $endDateTime->getTimestamp(),
    );

}

function intranet_fafar_api_does_reservations_overlaps( $reservation_a, $reservation_b ) {
    
  // | ((al/2)+as) - ((bl/2)+bs) | >= (al + bl) / 2 => if true, not overlaped
  // al = length a; as = start a ...
    
  $reservation_a_length_center =
    intranet_fafar_api_get_reservation_length_and_center( $reservation_a );
  $reservation_b_length_center =
    intranet_fafar_api_get_reservation_length_and_center( $reservation_b );
    
  $distance_between_centers = abs( $reservation_a_length_center["center"] - 
                                    $reservation_b_length_center["center"] );
    
  $reservations_length_sums =
    ($reservation_a_length_center["length"] + $reservation_b_length_center["length"]) / 2;
    
  if ($distance_between_centers >= $reservations_length_sums) 
    return false;
        
  return true;
}

function intranet_fafar_api_get_reservation_length_and_center( $reservation ) {
    $length = (int) $reservation["end"] - (int) $reservation["start"];

    $center = $length / 2 + (int) $reservation["start"];

    return array( "length" => $length, "center" => $center );
}

function intranet_fafar_api_get_reservations_by_place( $place ) {
    
    $query = "SELECT * FROM `SET_TABLE_NAME` WHERE `object_name` = 'reservation' AND JSON_CONTAINS(data, '[\"" . $place . "\"]', '$.place')";

    $submissions = intranet_fafar_api_read( $query );

    return $submissions;

}

function intranet_get_reservation_timestamp( $date_obj, $time ) {

    $d = new DateTime( $date_obj->format( "Y-m-d" ), $date_obj->getTimezone() );

    $hours   = (int) explode( ':', $time )[0];
    $minutes = (int) explode( ':', $time )[1];

    $d->setTime( $hours, $minutes );

    return $d->getTimestamp();

}

function intranet_fafar_api_get_timestamp( $date_string ) {

    $d = date_create( $date_string, new DateTimeZone('America/Sao_Paulo') );

    return (int) $d->getTimestamp();

}

function intranet_fafar_api_get_weekday_by_timestamp( $timestamp ) {

    $d = date_create( "now", new DateTimeZone('America/Sao_Paulo') );
    $d->setTimestamp((int) $timestamp);

    return (int) $d->format("w");

}
  
function intranet_fafar_api_get_submission_by_id_handler( $request ) {

    $id = (string) $request['id'];

    $submission = intranet_fafar_api_get_submission_by_id( $id );

    if ( isset( $submission['error_msg'] ) ) {

        return new WP_Error( 'rest_api_sad', esc_html__( $submission['error_msg'], 'intranet-fafar-api' ), $submission['http_status'] );

    }

    return rest_ensure_response( json_encode( $submission ) );

}

function intranet_fafar_api_get_submission_by_id( $id ) {

    global $wpdb;

    $table_name = $wpdb->prefix . 'fafar_cf7crud_submissions';

    if( ! $id ) {

        return array( 'error_msg' => '[0101]No "id" found.', 'http_status' => 400 );

    }
    
    $id = sanitize_text_field( wp_unslash( $id ) );

    $query = "SELECT * FROM `SET_TABLE_NAME` WHERE `id` = '" . $id . "'";

    $submissions = intranet_fafar_api_read( $query );

    if( ! $submissions || count( $submissions ) == 0 ) {

        return array( 'error_msg' => '[0102]No submission found with id "' . ( $id ?? 'UNKNOW_ID') . '"', 'http_status' => 400 );

    }

    if( count( $submissions ) > 1 ) {

        intranet_fafar_logs_register_log( 
            'ERROR', 
            'intranet_fafar_api_get_submission_by_id', 
            '[0102]Submission "id" duplicate:' . ( $id ?? 'UNKNOW_ID')
        );

        return array( 'error_msg' => '[0103]Submission "' . ( $id ?? 'UNKNOW_ID') . '" with duplicated "id"' , 'http_status' => 100 );

    }

    return $submissions[0];
}

function intranet_fafar_api_get_submissions_by_object_name_handler( $request ) {

    $object_name = (string) $request['object'];

    $submissions = intranet_fafar_api_get_submissions_by_object_name( $object_name );

    if ( isset( $submissions['error_msg'] ) ) {

        return new WP_Error( 'rest_api_sad', esc_html__( $submissions['error_msg'], 'intranet-fafar-api' ), $submissions['http_status'] );

    }

    return rest_ensure_response( json_encode( $submissions ) );

}

/*
 * @param array $order_by ( 'orderby_column' => '', 'orderby_json' => '', 'order' => 'ASC' | 'DESC', 'inet_aton' => '1' )
 * @return array $submissions 
 */
function intranet_fafar_api_get_submissions_by_object_name( $object_name, $order_by = array() ) {
    
    global $wpdb;

    $table_name = $wpdb->prefix . 'fafar_cf7crud_submissions';

    if( ! $object_name ) {

        return array( 'error_msg' => '[0201]No "object name" found.', 'http_status' => 500 );

    }

    $object_name = sanitize_text_field( wp_unslash( $object_name ) );
    
    $query = "SELECT * FROM `SET_TABLE_NAME` WHERE `object_name` = '" . $object_name . "'";

    if ( ! empty( $order_by ) ) {

        $order = 'ASC';
        if ( isset( $order_by['order'] ) && $order_by['order'] === 'DESC' ) {
            $order = 'DESC';
        }

        if ( isset( $order_by['orderby_column'] ) ) {

            $query .= ' ORDER BY ' . $order_by['orderby_column'] . ' ' . $order;

        } else if ( isset( $order_by['orderby_json'] ) ) {

            $prop = $order_by['orderby_json'];

            /* 
             * A propriedade 'inet_aton' é usada para 
             * informar ao MySQL que deve-se considerar 
             * a propriedade como número
             */ 
            $inet_function_str = ( ( isset( $order_by['inet_aton'] ) ) ? 'INET_ATON' : '' );

            $query = 'SELECT *, ' . $inet_function_str . '(JSON_UNQUOTE(JSON_EXTRACT(data, "$.' . $prop . '"))) AS json_prop' .
                        ' FROM `SET_TABLE_NAME` ' . 
                        ' WHERE `object_name` = "' . $object_name . '" ' . 
                        ' ORDER BY json_prop ' . $order;

        }

    }

    $submissions = intranet_fafar_api_read( $query );

    if( ! $submissions || count( $submissions ) == 0 ) {

        return array( 'error_msg' => '[0202]No submission found with id "' . ( $id ?? 'UNKNOW_ID') . '"', 'http_status' => 400 );

    }

    return $submissions;
}

function intranet_fafar_api_get_user_by_id_handler( $request ) {

    $id = (string) $request['id'];

    $submission = intranet_fafar_api_get_user_by_id( $id );

    if ( isset( $submission['error_msg'] ) ) {

        return new WP_Error( 'rest_api_sad', esc_html__( $submission['error_msg'], 'intranet-fafar-api' ), $submission['http_status'] );

    }

    return rest_ensure_response( json_encode( $submission ) );

}

function intranet_fafar_api_get_user_by_id( $id ) {

    error_log(print_r($id, true));

    if( ! $id ) {

        return array( 'error_msg' => '[0101]No "id" found.', 'http_status' => 400 );

    }

    $user = (array) get_userdata( intval( $id ) );

    if ( ! $user ) {

        return array( 'error_msg' => '[0101]No user found.', 'http_status' => 400 );

    }
    
    return $user;
}

function intranet_fafar_api_get_reservation_by_id_handler( $request ) {

    $id = (string) $request['id'];

    $reservation = intranet_fafar_api_get_reservation_by_id( $id );

    if ( isset( $reservation['error_msg'] ) ) {

        return new WP_Error( 'rest_api_sad', esc_html__( $reservation['error_msg'], 'intranet-fafar-api' ), $reservation['http_status'] );

    }

    return rest_ensure_response( json_encode( $reservation ) );

}

function intranet_fafar_api_get_equipaments_handler() {

    global $wpdb;

    $table_name = $wpdb->prefix . 'fafar_cf7crud_submissions';
    
    $query = "SELECT * FROM `SET_TABLE_NAME` WHERE `object_name` = 'equipament'";

    $submissions = intranet_fafar_api_read( $query ); 

    if( ! $submissions || count( $submissions ) == 0 ) {

        return array( 'error_msg' => '[0202]No submission found', 'http_status' => 400 );

    }

    /*
     * Substituir os campos que tem ID de outro objeto,
     * pelo objeto de mesmo ID
     */ 
    $submissions_joined = array_map( function ( $s ) {

        $applicant              = get_userdata( $s['data']['applicant'][0] );

        $s['data']['applicant'] = $applicant->get( 'display_name' );

        $s['data']['place']     = intranet_fafar_api_get_submission_by_id( $s['data']['place'][0] );

        $s['data']['ip']        = intranet_fafar_api_get_submission_by_id( $s['data']['ip'][0] );

        return $s;
        
    }, $submissions );



    return rest_ensure_response( json_encode( $submissions_joined ) );

}

function intranet_fafar_api_get_equipament_by_id( $id ) {

    $equipmanet = intranet_fafar_api_get_submission_by_id( $id );

    if ( ! $equipmanet ) 
        return array( 'error_msg' => '[0202]No equipmanet found', 'http_status' => 400 );

    if ( isset( $equipmanet['error_msg'] ) ) 
        return array( 'error_msg' => $equipmanet['error_msg'], 'http_status' => 400 );

    /*
     * Substituir os campos que tem ID de outro objeto,
     * pelo objeto de mesmo ID
     */ 
    if ( isset( $equipmanet['data']['applicant'] ) && is_numeric( $equipmanet['data']['applicant'][0] ) )
        $equipmanet['data']['applicant'] = intranet_fafar_api_get_user_by_id( $equipmanet['data']['applicant'][0] );

    if ( isset( $equipmanet['data']['place'][0] ) )
        $equipmanet['data']['place'] = intranet_fafar_api_get_submission_by_id( $equipmanet['data']['place'][0] );

    if ( isset( $equipmanet['data']['ip'][0] ) )
        $equipmanet['data']['ip'] = intranet_fafar_api_get_submission_by_id( $equipmanet['data']['ip'][0] );

    return $equipmanet;

}

function intranet_fafar_api_get_reservation_by_id( $id ) {
    
    global $wpdb;

    $table_name = $wpdb->prefix . 'fafar_cf7crud_submissions';

    if( ! $id ) {

        return array( 'error_msg' => '[0201]No "ID" found.', 'http_status' => 500 );

    }

    $id = sanitize_text_field( wp_unslash( $id ) );
    
    $query = "SELECT * FROM `SET_TABLE_NAME` WHERE `id` = '" . $id . "'";

    $reservation = intranet_fafar_api_read( $query );

    if( ! $reservation || count( $reservation ) == 0 ) {

        return array( 'error_msg' => '[0202]No reservation found with id "' . ( $id ?? 'UNKNOW_ID') . '"', 'http_status' => 400 );

    }

    $reservation = $reservation[0];

    if ( isset( $reservation['owner'] ) && is_numeric( $reservation['owner'] ) ) {

        $reservation['owner'] = intranet_fafar_api_get_user_by_id( $reservation['owner'] );

    }

    if ( isset( $reservation['data']['applicant'][0] ) && is_numeric( $reservation['data']['applicant'][0] ) ) {

        $reservation['data']['applicant'] = intranet_fafar_api_get_user_by_id( $reservation['data']['applicant'][0] );

    }

    if ( isset( $reservation['data']['discipline'][0] ) ) {

        $reservation['data']['discipline'] = intranet_fafar_api_get_submission_by_id( $reservation['data']['discipline'][0] );

    }

    return $reservation;
}

function intranet_fafar_get_user_by_departament( $role_slug = null ) {

    if ( ! $role_slug )
        return array();

    $users = get_users( 
        array ( 
                'role__not_in' => 'Administrator', 
                'orderby' => 'display_name', 
                'order' => 'ASC', 
                'role' => $role_slug
            ) 
        );

    $options = array();

    foreach ( $users as $user ) {
        $options[esc_attr( $user->ID )] = esc_html( $user->display_name );
    }

    error_log(print_r($options, true));

    return $options;

}

/**
 * SIMPLE CREATE, READ, UPDATE and DELETE FUNCS
 * 
*/

function intranet_fafar_api_create( $submission, $check_permissions = true ) {

    if ( ! isset( $submission['data'] ) )
        return array( 'error_msg' => 'No "data" column informed!' );

    global $wpdb;
  
    $table_name = $wpdb->prefix . 'fafar_cf7crud_submissions';

    $bytes              = random_bytes( 5 );
    $unique_hash        = time().bin2hex( $bytes ); 

    $submission['id']      = $unique_hash;
    $submission['form_id'] = $submission['form_id'] ?? '-2';
    $submission['data']    = json_encode( json_decode( $submission['data'] ) );
  
    $wpdb->insert( $table_name, $submission );

    do_action( 'intranet_fafar_api_after_create', $submission['id'] );

    return array( 'id' => $submission['id'] );
  
}

function intranet_fafar_api_read( $query, $check_permissions = true, $check_is_active = true ){

    if ( ! $query )
        return array( 'error_msg' => 'No query str on "intranet_fafar_api_read"!' );

    global $wpdb;

    $table_name = $wpdb->prefix . 'fafar_cf7crud_submissions';

    $query_completed = str_replace( 'SET_TABLE_NAME', $table_name, $query );
    
    $submissions = $wpdb->get_results( $query_completed, 'ARRAY_A' );

    if ( $submissions === null ) return array();

    if( empty( $submissions ) ) return array();

    $submissions_checked = array();
    foreach( $submissions as $submission ) {
    
        $submission['data'] = json_decode( $submission['data'], true );

        //error_log( print_r( $submission, true ) );

        // Check if 'is active'
        if( $check_is_active && 
            $submission['is_active'] != 1 )
            continue;
    
        // Checks for read permission
        if( $check_permissions &&
            ! intranet_fafar_api_check_read_permission( $submission ) )
            continue;

        /*
         * Checks for read permission.
         * If doesn't, set a 'prevent_write' prop to true
         */
        if( $check_permissions &&
            ! intranet_fafar_api_check_write_permission( $submission ) )
            $submission['data']['prevent_write'] = true;

        /*
         * Checks for read permission.
         * If doesn't, set a 'prevent_exec' prop to true
         */
        if( $check_permissions &&
            ! intranet_fafar_api_check_exec_permission( $submission ) )
            $submission['data']['prevent_exec'] = true;

        array_push( $submissions_checked,  $submission );
    
    }

    return $submissions_checked;

}

function intranet_fafar_api_update( $id, $submission, $check_permissions = true ) {

    if ( ! $id || ! $submission )
        return array( 'error_msg' => 'No ID or obj informed!' );

    if ( ! isset( $submission['data'] ) )
        return array( 'error_msg' => 'No data from obj informed!' );

    global $wpdb;
  
    $table_name = $wpdb->prefix . 'fafar_cf7crud_submissions';

    if ( ! is_string( $submission['data'] ) )
        $submission['data'] = json_encode( $submission['data'] );

    // Excluir 'updated_at', se existir
    if ( isset( $submission['updated_at'] ) )
        unset( $submission['updated_at'] );

    // Excluir 'created_at', se existir
    if ( isset( $submission['created_at'] ) )
        unset( $submission['created_at'] );

    error_log(print_r("========================================================", true));
    error_log(print_r($submission, true));
  
    $wpdb->update( $table_name, $submission, array( 'id' => $id ) );

    do_action( 'intranet_fafar_api_after_update', $id, $submission );

    return array( 'id' => $id, 'submission' => $submission );
  
}

function intranet_fafar_api_delete( $submission, $deactivate = true, $check_permissions = true ) {

    if ( ! isset( $submission['id'] ) )
        return array( 'error_msg' => 'No ID informed!' );

    if ( $check_permissions && ! intranet_fafar_api_check_write_permission( $submission ) )
        return array( 'error_msg' => 'Permission denied!' );

    global $wpdb;
  
    $table_name = $wpdb->prefix . 'fafar_cf7crud_submissions';

    if ( $deactivate ) {

        $res = $wpdb->delete( $table_name, array( 'id' => $submission['id'] ) );

        if ( ! $res ) 
            return array( 'error_msg' => 'No object found!' );

        do_action( 'intranet_fafar_api_after_delete', $submission );
        
    } else {

        if ( ! isset( $submission['is_active'] ) )
            return array( 'error_msg' => 'Submission can not be deactivate!' );

        $submission['is_active'] = '0';

        $wpdb->update( $table_name, array( 'is_active' => $submission['is_active'] ), array( 'id' => $submission['id'] ) );
    
        if ( isset( $submission['error_msg'] ) )
            return $submission;
    
        do_action( 'intranet_fafar_api_after_deactivated', $submission );

    }

    return array( 'submission' => $submission );
  
}

/*
 * <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
 * PERMISSION FUNCTIONS BLOCK
 * START
 * 
 * Permission code digits:
 * 0 = ---
 * 1 = --x
 * 2 = -w-
 * 3 = -wx
 * 4 = r--
 * 5 = r-x
 * 6 = rw-
 * 7 = rwx
 */

function intranet_fafar_api_check_read_permission( $submission, $user_id = null ) {
    
    $READ_DIGIT_VALUES = array( 4, 5, 6, 7 );

    return intranet_fafar_api_check_permissions( $submission, $READ_DIGIT_VALUES, $user_id );

}

function intranet_fafar_api_check_write_permission( $submission, $user_id = null ) {

    $WRITE_DIGIT_VALUES = array( 1, 3, 5, 7 );

    return intranet_fafar_api_check_permissions( $submission, $WRITE_DIGIT_VALUES, $user_id );

}

function intranet_fafar_api_check_exec_permission( $submission, $user_id = null ) {

    $EXEC_DIGIT_VALUES = array( 1, 3, 5, 7 );

    return intranet_fafar_api_check_permissions( $submission, $EXEC_DIGIT_VALUES, $user_id );

}

function intranet_fafar_api_check_permissions( $submission, $permission_digit_values, $user_id = null ) {

    $owner                              = (string) ( $submission['owner'] ?? 0 );
    $group_owner                        = (string) ( $submission['group_owner'] ?? 0 );
    $permissions                        = (string) ( $submission['permissions'] ?? '777' );

    $current_user_id                    = (string) ( $user_id ?? get_current_user_id() );
    $user_meta                          = get_userdata( $current_user_id );
    $user_roles                         = $user_meta->roles; // array( [0] => 'techs', ... )

    $OWNER_PERMISSION_DIGIT_INDEX       = 0;
    $OWNER_GROUP_PERMISSION_DIGIT_INDEX = 1;
    $OTHERS_PERMISSION_DIGIT_INDEX      = 2;

    /**
     * If the current user is the 'administrator', 
     * it gets instant permission.
    */
    if( in_array( 'administrator', $user_roles ) ) return true;

    // Permissions not set
    if ( ! $permissions ) return true;

    // Do not has restriction
    if ( $permissions === '777' ) return true;
    
    // Current user is the owner
    if ( $owner === $current_user_id ) {

        $permission_value = (int) str_split( $permissions )[$OWNER_PERMISSION_DIGIT_INDEX];
        return in_array( $permission_value, $permission_digit_values, true );

    }

    /**
     * Group permissions
     * If user is on $group_owner.
     * $user_roles. Array. array( [0] => 'techs', ... )
     */
    if ( in_array( strtolower( $group_owner ), $user_roles ) )
    {

        $permission_value = (int) str_split( $permissions )[$OWNER_GROUP_PERMISSION_DIGIT_INDEX];
        return in_array( $permission_value, $permission_digit_values, true );
    
    }

    // Others permissions
    $permission_value = (int) str_split( $permissions )[$OTHERS_PERMISSION_DIGIT_INDEX];
    return in_array( $permission_value, $permission_digit_values, true );

}

/*
 * PERMISSION FUNCTIONS BLOCK
 * END
 * >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
 */


/*
 * Functions to sanitize values, array or not, recurvive or not 
 */
function intranet_fafar_api_san( $v ) {

    $v = ( is_array( $v ) ? $v[0] : $v );

    return sanitize_text_field( wp_unslash( $v ) );

}

function intranet_fafar_api_san_recursive( $data ) {
    
    if ( is_array( $data ) ) {
        foreach ( $data as $key => $value ) {
            // Recursively sanitize each value if it's an array
            $data[$key] = intranet_fafar_api_san_recursive( $value );
        }
    } else {
        // Sanitize individual value
        $data = intranet_fafar_api_san( $data );
    }

    return $data;

}

function intranet_fafar_api_san_arr( $arr ) {

    foreach( $arr as $k => $v ) {

        /*
        * Isso aqui pode dar merda porque:
        * - Checks for invalid UTF-8,
        * - Converts single < characters to entities,
        * - Strips all tags, 
        * - Removes line breaks, tabs, and extra whitespace, 
        * - Strips percent-encoded characters,
        */
        $arr[$k] = intranet_fafar_api_san_recursive( $v );

    }

    return $arr;

}