<?php

add_filter( 'rest_authentication_errors', '__return_true' );


add_action( 'rest_api_init', 'intranet_fafar_api_register_submission_routes' );

/**
 * This function is where we register our routes for our example endpoint.
 */
function intranet_fafar_api_register_submission_routes() {
    // Here we are registering our route for a collection of submissions and creation of submissions.
    register_rest_route( 'intranet/v1', '/submissions', array(
        array(
            // By using this constant we ensure that when the WP_REST_Server changes, our readable endpoints will work as intended.
            'methods'  => WP_REST_Server::READABLE,
            // Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
            'callback' => 'intranet_fafar_api_get_submissions',
        ),
        array(
            // By using this constant we ensure that when the WP_REST_Server changes, our create endpoints will work as intended.
            'methods'  => WP_REST_Server::CREATABLE,
            // Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
            'callback' => 'intranet_fafar_api_create_submission',
        ),
    ) );


    // READABLE

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


    // DELETABLE


    register_rest_route( 'intranet/v1', '/submissions/(?P<id>[\w]+)', array(
        // By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
        'methods'  => WP_REST_Server::DELETABLE,
        // Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
        'callback' => 'intranet_fafar_api_delete_submission_by_id_handler',
    ) );

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

    return rest_ensure_response( 
            json_encode( 
                intranet_fafar_api_bff_reservations_prepare( 
                    $submissions,
                    array( 'id', 'start', 'end', 'desc', 'discipline', 'owner', 'applicant', 'reservation_group_id', 'place' )
                ) 
            ) 
        );

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
    
    if( ! isset( $form_data['object_name'] ) ) return $form_data;

    if ( $form_data['object_name'] !== 'equipament_loan' ) return $form_data;

    $equipament = intranet_fafar_api_get_submission_by_id( $form_data['data']['equipament'] );

    if ( ! $equipament )
        return array( 'error_msg' => '[001] Equipamento não existe!' );

    if ( $equipament['on_loan'] )
        return array( 'error_msg' => '[002] Equipamento está emprestado!' );

    $equipament['on_loan'] = '1';

    $res = intranet_fafar_api_update( $equipament['id'], $equipament );

    if ( isset( $equipament['error_msg'] ) )
        return array( 'error_msg' => $equipament['error_msg'] );

    return $form_data;
}

/**
 * This function listen a creation of a reservation and checks if 
 * is available.
 * Its uses the '' filter hook of fafar-cf7crud.
 * Returns null to abort the creating
 * 
 *   $data = array( 
 *   'desc' => $desc,
 *   'discipline' => $discipline,
 *   'frequency' => $frequency,
 *   'start_time' => $start_time,
 *   'end_time' => $end_time,
 *   'start_period' => $start_period,
 *   'end_period' => $end_period,
 *   'start' => $start,
 *   'end' => $end,
 *   'owner' => $owner,
 *   'applicant' => $applicant,
 *   'place' => $place,
 *   'object_sub_type' => $object_sub_type,
 *   'reservation_day' => $reservation_day,
 *   'weekday' => $weekday,
 *   'reservation_group_id' => $reservation_group_id,
 *   'post_on_fafar_website' => $post_on_fafar_website,
 *  )
 * 
 * @param $form_data
 * @return FromData | null
*/
function intranet_fafar_api_create_new_event( $form_data, $contact_form ) {

    // 0 - Verificar se se trata se uma reserva
    if( ! isset( $form_data['object_name'] ) ) return $form_data;

    if ( $form_data['object_name'] !== 'reservation' ) return $form_data;

    $form_data_as_arr = intranet_fafar_api_get_submission_as_arr( $form_data );

    $form_data_as_arr = intranet_fafar_api_san_arr( $form_data_as_arr );

    // 1 - Verificar se os dados vieram corretos, conforme cada tipo de reserva
    // 1.1 - Verificação geral

    if ( ! isset( $form_data_as_arr['start'] ) ||
         ! isset( $form_data_as_arr['end'] ) ||
         ! isset( $form_data_as_arr['place'] ) || 
         ! isset( $form_data_as_arr['frequency'] ) )
        return array( 'error_msg' => '[001] Campo(s) inválido(s) de Data, Hora, Lugar e/ou Tipo do Objeto!' );

    if ( intranet_fafar_api_get_timestamp( $form_data_as_arr['start']) > 
         intranet_fafar_api_get_timestamp( $form_data_as_arr['end'] ) )
        return array( 'error_msg' => '[002] Hora Início não pode ser depois de Hora Fim!' );

    if ( intranet_fafar_api_get_timestamp( $form_data_as_arr['start'] ) === 
         intranet_fafar_api_get_timestamp( $form_data_as_arr['end'] ) )
        return array( 'error_msg' => '[003] Hora Início não pode ser igual a Hora Fim!' );

    $new_reservations = array();

    $frequency = ( is_array( $form_data_as_arr['frequency'] ) ? 
        $form_data_as_arr['frequency'][0] : 
        $form_data_as_arr['frequency'] );
    // 1.2.1 Once
    if ( $frequency === 'once' ) {

        $new_reservations[] = array( 'start' => $start, 'end' => $end );

        // compara eventos gerados pelo rrule com os que já existem no BD 
       
    } 
    // 1.2.2 Weekly
    else if ( $frequency === 'weekly' ) {

        if ( ! isset( $form_data_as_arr['weekday'] ) )
            return array( 'error_msg' => '[004] Início, Fim do período e/ou Dia da semana não informado!' );

        if ( ! is_array( $form_data_as_arr['weekday'] ) )
            return array( 'error_msg' => '[005] Dia de semana em formato desconhecido' );

        $by_days = implode( ',', $form_data_as_arr['weekday'] );

        // gera rrule
        $rrule = 'DTSTART:' . $start . '\nRRULE:FREQ=WEEKLY;INTERVAL=1;UNTIL='. $end . ';BYDAY=' . $by_days;

        // gera eventos por rrule
        $new_reservations = intranet_fafar_api_generate_events_from_rrule( $rrule );

        // compara eventos gerados pelo rrule com os que já existem no BD

        // dentro do comparador
            // se o evento tiver rrule
                // gera eventos por rrule
                // compara eventos gerados pelo rrule com os novos

    }
    // 1.2.x Desconhecido
    else {

        return array( 'error_msg' => '[100] Frequência desconhecida!' );

    }

    // 'DTSTART:20241021T133000\nRRULE:TZID=America/Sao_Paulo;FREQ=WEEKLY;INTERVAL=1;UNTIL=20241022T183000'

    // $rruleString = 'FREQ=WEEKLY;COUNT=5;BYDAY=MO,WE,FR;UNTIL=2024-12-31T23:59:59';
    // $startDate = '2024-10-01 09:00:00';
    
    // $events = generateEventsFromRRule($rruleString, $startDate);
    
    // echo 'Generated events:\n';
    // foreach ($events as $event) {
    //     echo $event . '\n';
    // }

    // 2 - 'Gera' as reserva(s)
    $new_reservations = intranet_fafar_api_generate_reservations( $form_data_as_arr );

    if ( empty( $new_reservations ) )
        return array( 'error_msg' => '[007] Não foi possível gerar reservationos!' );

    // 3 - Verificar se todos as reservas podem ser feitas e não colidem com outras
    $place = ( is_array( $form_data_as_arr['place'] ) ? $form_data_as_arr['place'][0] : $form_data_as_arr['place'] );
    $place_id = intranet_fafar_api_san( $place );
    $new_reservations = intranet_fafar_api_is_place_available_for_class_reservation( $new_reservations, $place_id );
    
    //

    if ( isset( $new_reservations['error_msg'] ) )
        return $new_reservations;

    // 4 - Se todas PODEM ser feitas, então faça.
    $form_post_id = $contact_form->id();

    // 4.1 - Mas antes, se for multiplos reservationos, preenche o 'reservation_group_id'
    $reservation_group_id = '';
    if ( sizeof( $new_reservations ) > 1 ) {

        $bytes          = random_bytes(5);
        $unique_hash    = time().bin2hex($bytes); 
        $reservation_group_id = $unique_hash; 

    }
    
    foreach ( $new_reservations as $new_reservation ) {

        $form_data_as_json                   = json_decode( $form_data['data'], true );
        
        $form_data_as_json['reservation_group_id'] = $reservation_group_id;
        $form_data_as_json['start']          = $new_reservation['start'];
        $form_data_as_json['end']            = $new_reservation['end'];
        $form_data['id']                     = false;

        $form_data['data']                   = json_encode( $form_data_as_json );

        intranet_fafar_api_create( $form_data );
  
    }

    return array( 'prreservation_submit' => true );

}

/**
 * 
 * @param String $rruleString
 * @return Array
 * 
 * Param example:
 * 'DTSTART:20241001T133000\nRRULE:TZID=America/Sao_Paulo;FREQ=WEEKLY;INTERVAL=1;UNTIL=20241031T183000;BYDAY=MO,WE'
 * 
 * Return example:
 *   Array
 *   (
 *       [0] => Array
 *           (
 *               [start] => 2024-10-01 13:30:00
 *               [end] => 2024-10-31 18:30:00
 *           )
 *
 *       [1] => Array
 *           (
 *               [start] => 2024-10-08 13:30:00
 *               [end] => 2024-10-31 18:30:00
 *           )
 * 
*/
function intranet_fafar_api_generate_events_from_rrule( $rruleString ) {
    $events = [];
    
    $lines = explode( '\n', $rruleString );
    
    if ( sizeof( $lines ) < 2 )
        return 'RRule bad formatted';
        
    $dtstart_part = $lines[0];
    $rrule_part = explode( ':', $lines[1] )[1];

    // Parse the RRULE string into an array
    $rules = [];
    $rruleParts = explode(';', $rrule_part);
    foreach ($rruleParts as $rule) {
        list($key, $value) = explode('=', $rule);
        $rules[$key] = $value;
    }
    
    $rules['DTSTART'] = explode( ':', $dtstart_part )[1];

    $startDateTime = new DateTime($rules['DTSTART']);
    
    // Determine the frequency of recurrence
    $frequency = isset($rules['FREQ']) ? $rules['FREQ'] : null;
    $count = isset($rules['COUNT']) ? (int)$rules['COUNT'] : null;
    $interval = isset($rules['INTERVAL']) ? (int)$rules['INTERVAL'] : 1;
    $byday = isset($rules['BYDAY']) ? explode(',', $rules['BYDAY']) : [];
    $until = isset($rules['UNTIL']) ? new DateTime($rules['UNTIL']) : null;

    // Event generation loop
    $i = 0;
    while (true) {
        // Stop if UNTIL is defined and we pass it
        if ($until && $startDateTime > $until) {
            break;
        }

        // Stop if COUNT is defined and we've generated enough events
        if ($count && $i >= $count) {
            break;
        }

        // Add the current event date to the array
        $events[] = $startDateTime->format('Y-m-d H:i:s');
        $i++;

        // Generate the next occurrence based on frequency
        switch ($frequency) {
            case 'DAILY':
                $startDateTime->modify("+$interval day");
                break;
            case 'WEEKLY':
                if (!empty($byday)) {
                    // Advance to the next day in BYDAY
                    foreach ($byday as $day) {
                        $currentDay = strtoupper($startDateTime->format('D'));
                        if ($currentDay === substr($day, 0, 2)) {
                            $startDateTime->modify("+$interval week");
                            break;
                        }
                    }
                } else {
                    $startDateTime->modify("+$interval week");
                }
                break;
            case 'MONTHLY':
                $startDateTime->modify("+$interval month");
                break;
            default:
                throw new Exception("Unsupported frequency: $frequency");
        }
    }

    return $events;
}

function intranet_fafar_api_generate_reservations( $data ) {

    if ( ! $data ) return array();
    if ( ! isset( $data['frequency'] ) ) return array();
    
    $frequency = ( is_array( $data['frequency'] ) ? $data['frequency'][0] : $data['frequency'] );

    switch( $frequency ) {

        case 'once':
            return intranet_fafar_api_generate_single_reservation( $data['reservation_day'], $data['start_time'], $data['end_time'] );
        
        case 'weekly':
            return intranet_fafar_api_generate_reservations_weekly( 
                $data['start_period'], 
                $data['end_period'], 
                $data['start_time'], 
                $data['end_time'], 
                $data['weekday'], 
            );

        default:
            return array();

    }
    
}

function intranet_fafar_api_generate_single_reservation( $reservation_day, $start_time, $end_time ) {

    $reservation_day  = intranet_fafar_api_san( $reservation_day );
    $start_time = intranet_fafar_api_san( $start_time );
    $end_time   = intranet_fafar_api_san( $end_time );

    $start = intranet_fafar_api_get_timestamp( $reservation_day . " " . $start_time );
    $end = intranet_fafar_api_get_timestamp( $reservation_day . " " . $end_time );

    return array( 0 => array( 'start' => $start, 'end' => $end ) );
}

function intranet_fafar_api_generate_reservations_weekly( $start_period, $end_period, $start_hours, $end_hours, $weekday ) {

    $weekday           = ( is_array( $weekday ) ? $weekday[0] : $weekday );

    $start_period      = intranet_fafar_api_san( $start_period );
    $end_period        = intranet_fafar_api_san( $end_period );
    $start_hours       = intranet_fafar_api_san( $start_hours );
    $end_hours         = intranet_fafar_api_san( $end_hours );
    $weekday           = intranet_fafar_api_san( $weekday );

    $timezone          = new DateTimeZone('America/Sao_Paulo');
    $start_period_date = new DateTime( $start_period, $timezone ); 
    $end_period_date   = new DateTime( $end_period, $timezone );
    $current_date      = $start_period_date;

    $reservations            = array();
    
    while ( $current_date->getTimestamp() <= $end_period_date->getTimestamp() ) {
    
        // Sunday: 0, Monday: 1 ...
        if ( date( "w", $current_date->getTimestamp() ) == $weekday ) {
            
            $start = intranet_get_reservation_timestamp( $current_date, $start_hours );
            $end   = intranet_get_reservation_timestamp( $current_date, $end_hours );
    
            array_push( $reservations, array( 'start' => $start, 'end' => $end ) );
    
        }
    
        $current_date->modify( '+1 day' );
    }
        
    return $reservations;
    
}

function intranet_fafar_api_is_place_available_for_class_reservation( $new_reservations, $place_id ) {
  
    $reservations_saved = intranet_fafar_api_get_reservations_by_place( $place_id );
    
    foreach ( $new_reservations as $new_reservation ) {
        foreach ( $reservations_saved as $reservation_saved ) {
    
          if ( intranet_fafar_api_does_reservations_overlaps( $new_reservation, $reservation_saved ) )
            return array( "error_msg" => "[005] Horário/Data indisponível!" );
    
        }
    }
  
    return $new_reservations;
}

function intranet_fafar_api_get_reservations_by_place( $place ) {
    
    $query = "SELECT * FROM `SET_TABLE_NAME` WHERE `object_name` = 'reservation' AND JSON_CONTAINS(data, '[\"" . $place . "\"]', '$.place')";
    
    $submissions = intranet_fafar_api_read( $query );

    return $submissions;

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

function intranet_fafar_api_get_submission_by_id( $id, $check_permissions = true ) {

    global $wpdb;

    $table_name = $wpdb->prefix . 'fafar_cf7crud_submissions';

    if( ! $id ) {

        return array( 'error_msg' => '[0101]No "id" found.', 'http_status' => 400 );

    }
    
    $id = sanitize_text_field( wp_unslash( $id ) );

    $query = "SELECT * FROM `SET_TABLE_NAME` WHERE `id` = '" . $id . "'";

    $submissions = intranet_fafar_api_read( $query, false, false );

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

    $submission = $submissions[0];

    // Check if 'is active'
    if( $submission['is_active'] != 1 )
        return array( 'error_msg' => '[0104]Submission "' . ( $id ?? 'UNKNOW_ID') . '" deactivated/deleted' , 'http_status' => 400 );

    // Check if is allowed to read
    if( $check_permissions && 
        ! intranet_fafar_api_check_read_permission( $submission ) )
        return array( 'error_msg' => '[0105]Permission denied for submission "' . ( $id ?? 'UNKNOW_ID') . '"', 'http_status' => 400 );

    /*
     * Checks for read permission.
     * If doesn't, set a 'prevent_write' prop to true
     */
    if( $check_permissions &&
        ! intranet_fafar_api_check_write_permission( $submission ) )
        $submission['prevent_write'] = true;

    /*
     * Checks for read permission.
     * If doesn't, set a 'prevent_exec' prop to true
     */
    if( $check_permissions &&
        ! intranet_fafar_api_check_exec_permission( $submission ) )
        $submission['prevent_exec'] = true;


    return $submission;
}

function intranet_fafar_api_get_submissions_by_object_name_handler( $request ) {

    $object_name = (string) $request['object'];

    $submissions = intranet_fafar_api_get_submissions_by_object_name( $object_name );

    if ( isset( $submissions['error_msg'] ) ) {

        return new WP_Error( 'rest_api_sad', esc_html__( $submissions['error_msg'], 'intranet-fafar-api' ), $submissions['http_status'] );

    }

    return rest_ensure_response( json_encode( $submissions ) );

}

function intranet_fafar_api_get_submissions_by_object_name( $object_name ) {
    
    global $wpdb;

    $table_name = $wpdb->prefix . 'fafar_cf7crud_submissions';

    if( ! $object_name ) {

        return array( 'error_msg' => '[0201]No "object name" found.', 'http_status' => 500 );

    }

    $object_name = sanitize_text_field( wp_unslash( $object_name ) );
    
    $query = "SELECT * FROM `SET_TABLE_NAME` WHERE `object_name` = '" . $object_name . "'";

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

    if( ! $id ) {

        return array( 'error_msg' => '[0101]No "id" found.', 'http_status' => 400 );

    }

    $user = (array) get_userdata( $id );

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

        $applicant      = get_userdata( $s['applicant'][0] );
        $s['applicant'] = $applicant->get( 'display_name' );

        $s['place'] = intranet_fafar_api_get_submission_by_id( $s['place'][0] );

        return $s;
        
    }, $submissions );



    return rest_ensure_response( json_encode( $submissions_joined ) );

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

    if ( isset( $reservation['owner'] ) && $reservation['owner'] ) {

        $reservation['owner'] = intranet_fafar_api_get_user_by_id( $reservation['owner'] );

    }

    return $reservation;
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

    if( ! $check_permissions && ! $check_is_active )
        return intranet_fafar_api_decode_all_submissions_as_arr( $submissions );

    $submissions_checked = array();
    foreach( $submissions as $submission ) {
    
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
            $submission['prevent_write'] = true;

        /*
         * Checks for read permission.
         * If doesn't, set a 'prevent_exec' prop to true
         */
        if( $check_permissions &&
            ! intranet_fafar_api_check_exec_permission( $submission ) )
            $submission['prevent_exec'] = true;

        array_push( $submissions_checked,  $submission );
    
    }

    return intranet_fafar_api_decode_all_submissions_as_arr( $submissions_checked );

}

function intranet_fafar_api_update( $id, $new_data, $check_permissions = true ) {

    if ( ! $new_data || ! $id )
        return array( 'error_msg' => 'No ID or data informed!' );

    global $wpdb;
  
    $table_name = $wpdb->prefix . 'fafar_cf7crud_submissions';

    if ( ! isset( $new_data['data'] ) )
        $new_data['data'] = json_encode( $new_data['data'] );
  
    $wpdb->update( $table_name, $new_data, array( 'id' => $id ) );

    do_action( 'intranet_fafar_api_after_update', $id, $new_data );

    return array( 'id' => $id, 'new_data' => $new_data );
  
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

/**
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

/**
 * PERMISSION FUNCTIONS BLOCK
 * END
 * >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
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

function intranet_fafar_api_decode_all_submissions_as_arr( $arr ) {
    
    $s_arr = array();

    foreach ( $arr as $item ) {
        array_push( $s_arr, intranet_fafar_api_get_submission_as_arr( $item ) );
    }

    return $s_arr;
}

/*
 * This function join all submissions properties(columns and json) 
 * from $wpdb->get_results in one php array.
 *
 * @since 1.0.0
 * @param mixed $submission Return from $wpdb->get_results
 * @return array $submission_joined  Submission joined
*/
function intranet_fafar_api_get_submission_as_arr( $submission ) {
    
    if ( ! isset( $submission['data'] ) ) {

        intranet_fafar_logs_register_log( 
            'ERROR', 
            'intranet_fafar_api_get_submission_as_arr', 
            'submission ' . ( $submission['id'] ?? 'UNKNOW_ID') . ' do not have "data" column value' 
        );

        return $submission;

    }

    $arr = json_decode( $submission['data'], true );

    foreach ( $submission as $key => $value ) {

        if( $key == 'data' ) continue;

        if ( is_array( $value ) && ! empty( $value ) ) {
            
            $arr[$key] =  $value[0];
        
        } else {

            $arr[$key] = ( $value ) ?? '--';

        }

    }

    return $arr;

}