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

    register_rest_route( 'intranet/v1', '/submissions/object/(?P<object>[\w]+)', array(
        // By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
        'methods'  => WP_REST_Server::READABLE,
        // Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
        'callback' => 'intranet_fafar_api_get_submissions_by_object_name',
    ) );

    register_rest_route( 'intranet/v1', '/submissions/place/available', array(
        // By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
        'methods'  => WP_REST_Server::READABLE,
        // Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
        'callback' => 'intranet_fafar_api_get_places_available',
    ) );

    register_rest_route( 'intranet/v1', '/submissions/(?P<place>[\w]+)/events', array(
        // By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
        'methods'  => WP_REST_Server::READABLE,
        // Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
        'callback' => 'intranet_fafar_api_get_place_events',
    ) );


}

function intranet_fafar_api_get_place_events( $request ) {

    if ( ! $request["place"] ) 
        return new WP_Error( 'rest_api_sad', esc_html__( 'Missing place.', 'intranet-fafar-api' ), array( 'status' => 400 ) );

    global $wpdb;

    $table_name = $wpdb->prefix . 'fafar_cf7crud_submissions';
    
    $json_place = json_encode( array( "local" => $request["place"] ) );

    $query = "SELECT * FROM `" . $table_name . "` WHERE `object_name` = 'evento' AND JSON_CONTAINS( data, '" . $json_place . "')";

    $events = $wpdb->get_results( $query );

    return rest_ensure_response( 
            json_encode( 
                intranet_fafar_api_decode_multiple_submissions( 
                    $events 
                    ) 
                ) 
            );

}

function intranet_fafar_api_get_places_available( $request ) {

    $dia = $request->get_param( 'dia' );
    $inicio = $request->get_param( 'inicio' );
    $fim = $request->get_param( 'fim' );
    $capacidade = $request->get_param( 'capacidade' );

    if ( ! $dia    ||
         ! $inicio ||
         ! $fim    ||
         ! $capacidade ) 
        return new WP_Error( 'rest_api_sad', esc_html__( 'Missing attributes.', 'intranet-fafar-api' ), array( 'status' => 400 ) );

    $inicio     = (int) intranet_fafar_api_get_timestamp( $dia . " " . $inicio );
    $fim        = (int) intranet_fafar_api_get_timestamp( $dia . " " . $fim );
    $capacidade = (int) $capacidade;

    $places = intranet_fafar_api_check_for_places_available( 
        array( "inicio" => $inicio, "fim" => $fim, "capacidade" => $capacidade ) 
    );
    
    return rest_ensure_response( json_encode( $places ) );
}
/**
 * 
 * @param array $data array( dia => , inicio => , fim => , capacidade => ) 
*/
function intranet_fafar_api_check_for_places_available( $data ) {

    global $wpdb;

    $table_name = $wpdb->prefix . 'fafar_cf7crud_submissions';
    
    $query = "SELECT * FROM `" . $table_name . "` WHERE `object_name` = 'place'";

    $places = $wpdb->get_results( $query );
  
    $places_available = array();
  
    foreach ( $places as $classroom ) {

        $classroom = intranet_fafar_api_decode_submission_complete( $classroom );

        if ( $classroom["capacidade"] < $data["capacidade"] ) continue;

        $json_place = json_encode( array( "local" => $classroom["id"] ) );

        $query = "SELECT * FROM `" . $table_name . "` WHERE `object_name` = 'evento' AND JSON_CONTAINS( data, '" . $json_place . "')";

        $events = $wpdb->get_results( $query );

        $is_available = true;
        foreach ( $events as $event ) {

            $event = intranet_fafar_api_decode_submission_complete( $event );

            if( intranet_fafar_api_does_events_overlaps( $event, $data ) ) {
                $is_available = false;
                break;
            }

        }

        if( $is_available )
            array_push( $places_available, $classroom );

      }
  
    return $places_available;
}

/**
 * This is our callback function to return our submissions.
 *
 * @param WP_REST_Request $request This function accepts a rest request to process data.
 */
function intranet_fafar_api_get_submissions( $request ) {

    global $wpdb;

    $table_name = $wpdb->prefix . 'fafar_cf7crud_submissions';
    
    $query = "SELECT * FROM `" . $table_name . "`";

    $submissions = $wpdb->get_results( $query );

    $submissions_decoded = array();
    foreach( $submissions as $submission ) {

        array_push( $submissions_decoded, intranet_fafar_api_decode_submission_complete( $submission ) );

    }


    return rest_ensure_response( json_encode( $submissions_decoded ) );
}

function intranet_fafar_api_get_submissions_by_object_name( $request ) {
    
    global $wpdb;

    $table_name = $wpdb->prefix . 'fafar_cf7crud_submissions';

    $object_name = (string) $request['object'];

    if( ! $object_name ) {

        return new WP_Error( 'rest_api_sad', esc_html__( 'No "object name" found.', 'intranet-fafar-api' ), array( 'status' => 500 ) );

    }
    
    $query = "SELECT * FROM `" . $table_name . "` WHERE `object_name` = '" . $object_name . "'";

    $submissions = $wpdb->get_results( $query );

    if( count( $submissions ) < 1 ) {

        return new WP_Error( 'rest_api_sad', esc_html__( 'No submission found.', 'intranet-fafar-api' ), array( 'status' => 500 ) );

    }

    $submissions_decoded = array();
    foreach( $submissions as $submission ) {

        array_push( $submissions_decoded, intranet_fafar_api_decode_submission_complete( $submission ) );

    }


    return rest_ensure_response( json_encode( $submissions_decoded ) );
}

/**
 * This is our callback function to return a single submission.
 *
 * @param WP_REST_Request $request This function accepts a rest request to process data.
 */
function intranet_fafar_api_create_submission( $request ) {
    // In practice this function would create a submission. Here we are just making stuff up.
   return rest_ensure_response( 'submission has been created' );
}



/**
 * This function listen a creation of a event and checks if 
 * is available.
 * Its uses the '' filter hook of fafar-cf7crud.
 * Returns null to abort the creating
 * 
 * @param $form_data
 * @return FromData | null
*/
function intranet_fafar_api_is_place_available_for_class_event( $form_data, $object_name, $contact_form ) {

  error_log( print_r( $form_data, true ) );

    if ( $object_name !== "evento" ) return $form_data;


    if ( ! isset( $form_data["inicio_periodo"] ) ||
            ! isset( $form_data["fim_periodo"] ) ||
            ! isset( $form_data["inicio_hora"] ) ||
            ! isset( $form_data["fim_hora"] ) ||
            ! isset( $form_data["dia_semana"] ) )
            return array( "error_msg" => "[001] Campo(s) inválido(s) de Horário e/ou Data!" );

    // Verify if start > end
    if( intranet_fafar_api_get_timestamp( $form_data["inicio_hora"] ) > 
        intranet_fafar_api_get_timestamp( $form_data["fim_hora"] ) ) 
        return array( "error_msg" => "[002] Início não pode ser depois de Fim!" );

    
    if ( ! isset( $form_data["local"] ) )
        return array( "error_msg" => "[003] Local não informado!" );


    if ( is_array( $form_data["local"] ) && 
            count( $form_data["local"] ) == 0 )
            return array( "error_msg" => "[004] Local não informado!" );
        


    
  /**
   * Generate all the events for the period(semester, in this case)
   * */
  $new_events = intranet_fafar_api_generate_events_by_period( $form_data );

  $events_saved = intranet_fafar_api_get_events_by_place( $form_data["local"] );

  foreach ( $new_events as $new_event ) {
      foreach ( $events_saved as $event_saved ) {

        $event_saved = intranet_fafar_api_decode_submission_complete( $event_saved );
  
        if ( intranet_fafar_api_does_events_overlaps( $new_event, $event_saved ) )
          return array( "error_msg" => "[005] Horário/Data indisponível!" );
  
      }
  }

  $form_post_id = $contact_form->id();
  
  foreach ( $new_events as $new_event ) {
  
    $bytes             = random_bytes( 5 );
    $unique_hash       = time().bin2hex( $bytes ); 
    $form_data_as_json = json_encode( $new_event );

    intranet_fafar_api_create_event( $unique_hash, $form_post_id, $object_name, $form_data_as_json );

  }


	return array( "prevent_submit" => true );

}


function intranet_fafar_api_generate_events_by_period( $event_model ) {

        $start_period = intranet_fafar_api_get_timestamp( $event_model["inicio_periodo"] . " " . $event_model["inicio_hora"] );
        $end_period = intranet_fafar_api_get_timestamp( $event_model["fim_periodo"] . " " . $event_model["fim_hora"] );
      
        $weekday = $event_model["dia_semana"][0];
      
        $current = $start_period;

        $repeated_events = array();
        while ( $current < $end_period ) {

          if ( intranet_fafar_api_get_weekday_by_timestamp( $current ) == $weekday ) {

            $start = intranet_get_event_timestamp( $current, $event_model["inicio_hora"] );
            $end = intranet_get_event_timestamp( $current, $event_model["fim_hora"] );

            $event_model["inicio"] = $start;
            $event_model["fim"] = $end;

            array_push( $repeated_events, $event_model );

          }

          $current += (24 * 3600);

        }
      
        return $repeated_events;
      
}

function intranet_fafar_api_does_events_overlaps( $event_a, $event_b ) {
    
  // | ((al/2)+as) - ((bl/2)+bs) | >= (al + bl) / 2 => if true, not overlaped
  // al = length a; as = start a ...
    
  $event_a_length_center =
    intranet_fafar_api_get_event_length_and_center( $event_a );
  $event_b_length_center =
    intranet_fafar_api_get_event_length_and_center( $event_b );
    
  $distance_between_centers = abs( $event_a_length_center["center"] - 
                                    $event_b_length_center["center"] );
    
  $events_length_sums =
    ($event_a_length_center["length"] + $event_b_length_center["length"]) / 2;
    
  if ($distance_between_centers >= $events_length_sums) 
    return false;
        
  return true;
}

function intranet_fafar_api_get_event_length_and_center( $event ) {
    $length = $event["fim"] - $event["inicio"];

    $center = $length / 2 + $event["inicio"];

    return array( "length" => $length, "center" => $center );
}

function intranet_fafar_api_create_event($id, $form_id, $object_name, $form_data_as_json) {

  global $wpdb;

  $table_name = $wpdb->prefix . 'fafar_cf7crud_submissions';

  $wpdb->insert( $table_name, array(
      'id'          => $id,
      'form_id'     => $form_id,
      'object_name' => $object_name,
      'data'        => $form_data_as_json,
  ) );

}

function intranet_fafar_api_get_events_by_place( $place ) {

    global $wpdb;

    $table_name = $wpdb->prefix . 'fafar_cf7crud_submissions';
    
    $json_place = json_encode( array( "local" => $place ) );

    $query = "SELECT * FROM `" . $table_name . "` WHERE `object_name` = 'evento' AND JSON_CONTAINS( data, '" . $json_place . "')";
    
    $submissions = $wpdb->get_results( $query );

    return $submissions;

}

function intranet_get_event_timestamp( $current, $hour ) {

    $d = date_create( "now", new DateTimeZone('America/Sao_Paulo') );
    $d->setTimestamp((int) $current);

    return intranet_fafar_api_get_timestamp( $d->format("Y-m-d") . " " . $hour );

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

function intranet_fafar_api_decode_multiple_submissions( $submissions ) {

    $submissions_decoded = array();

    foreach ( $submissions as $submission ) {

        array_push( $submissions_decoded, 
            intranet_fafar_api_decode_submission_complete( $submission ) );

    }
  
    return $submissions_decoded;
  }
  
/*
 * This function join all submissions properties(columns and json) 
 * from $wpdb->get_results in one php array.
 *
 * @since 1.0.0
 * @param mixed $submission Return from $wpdb->get_results
 * @return array $submission_joined  Submission joined
*/
function intranet_fafar_api_decode_submission_complete( $submission ) {
    
    foreach($submission as $key => $value) {
    
        if ( $key === 'data' )
            $submission_joined['data'] = json_decode( $value );

        $submission_joined[$key] = $value;
        
    }

    return $submission_joined;
}