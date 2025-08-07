<?php
// Prevent direct access to the file.

use function PHPSTORM_META\type;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_filter( 'rest_authentication_errors', '__return_true' );
add_action( 'rest_api_init', 'intranet_fafar_api_register_submission_routes' );

/**
 * This function is where we register our routes for our example endpoint.
 */
function intranet_fafar_api_register_submission_routes() {
	register_rest_route( 'intranet/v1', '/submissions/auditorium/reservation/', array(
		// By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
		'methods' => WP_REST_Server::CREATABLE,
		// Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
		'callback' => 'intranet_fafar_api_create_auditorium_reservation_handler',
	) );

	register_rest_route( 'intranet/v1', '/submissions/service_ticket_update', array(
		// By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
		'methods' => WP_REST_Server::CREATABLE,
		// Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
		'callback' => 'intranet_fafar_api_create_service_ticket_update_handler',
	) );

	register_rest_route( 'intranet/v1', '/submission/', array(
		// By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
		'methods' => WP_REST_Server::CREATABLE,
		// Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
		'callback' => 'intranet_fafar_api_create_submission_handler',
	) );

	register_rest_route( 'intranet/v1', '/email/send', array(
		// By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
		'methods' => WP_REST_Server::CREATABLE,
		// Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
		'callback' => 'intranet_fafar_api_send_email',
	) );

	// READABLE

	register_rest_route( 'intranet/v1', '/submissions/service_tickets/by_user', array(
		// By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
		'methods' => WP_REST_Server::READABLE,
		// Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
		'callback' => 'intranet_fafar_api_get_service_tickets_by_user_handler',
	) );

	register_rest_route( 'intranet/v1', '/submissions/service_tickets/by_departament', array(
		// By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
		'methods' => WP_REST_Server::READABLE,
		// Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
		'callback' => 'intranet_fafar_api_get_service_tickets_by_departament_handler',
	) );

	register_rest_route( 'intranet/v1', '/submissions/service_tickets', array(
		// By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
		'methods' => WP_REST_Server::READABLE,
		// Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
		'callback' => 'intranet_fafar_api_get_service_tickets_handler',
	) );

	register_rest_route( 'intranet/v1', '/submissions/service_ticket_updates/by_service_ticket', array(
		// By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
		'methods' => WP_REST_Server::READABLE,
		// Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
		'callback' => 'intranet_fafar_api_get_service_ticket_updates_by_service_ticket_handler',
	) );

	register_rest_route( 'intranet/v1', '/submissions/access_building_request', array(
		// By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
		'methods' => WP_REST_Server::READABLE,
		// Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
		'callback' => 'intranet_fafar_api_get_access_building_request_handler',
	) );

	register_rest_route( 'intranet/v1', '/submissions/access_building_request/mines', array(
		// By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
		'methods' => WP_REST_Server::READABLE,
		// Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
		'callback' => 'intranet_fafar_api_get_access_building_request_by_owner_handler',
	) );

	register_rest_route( 'intranet/v1', '/submissions/equipaments', array(
		// By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
		'methods' => WP_REST_Server::READABLE,
		// Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
		'callback' => 'intranet_fafar_api_get_equipaments_handler',
	) );

	register_rest_route( 'intranet/v1', '/submissions/ips', array(
		// By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
		'methods' => WP_REST_Server::READABLE,
		// Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
		'callback' => 'intranet_fafar_api_get_ips_handler',
	) );

	register_rest_route( 'intranet/v1', '/submissions/ips/(?P<id>[\w]+)/check-result', array(
		// By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
		'methods' => WP_REST_Server::READABLE,
		// Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
		'callback' => 'intranet_fafar_api_get_check_results_handler',
	) );

	register_rest_route( 'intranet/v1', '/submissions/reservations/(?P<id>[\w]+)', array(
		// By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
		'methods' => WP_REST_Server::READABLE,
		// Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
		'callback' => 'intranet_fafar_api_get_reservation_by_id_handler',
	) );

	register_rest_route( 'intranet/v1', '/submissions/auditorium/reservations/', array(
		// By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
		'methods' => WP_REST_Server::READABLE,
		// Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
		'callback' => 'intranet_fafar_api_get_auditorium_reservations_handler',
	) );

	register_rest_route( 'intranet/v1', '/submissions/laboratory-team/(?P<owner_id>[\w]+)', array(
		// By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
		'methods' => WP_REST_Server::READABLE,
		// Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
		'callback' => 'intranet_fafar_api_get_laboratory_team_by_owner_id_handler',
	) );

	register_rest_route( 'intranet/v1', '/submissions/laboratory-team/new_collaborators/(?P<id>[\w]+)', array(
		// By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
		'methods' => WP_REST_Server::READABLE,
		// Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
		'callback' => 'intranet_fafar_api_get_possible_collaborators_laboratory_team_handler',
	) );

	register_rest_route( 'intranet/v1', '/submissions/object/(?P<object>[\w]+)', array(
		// By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
		'methods' => WP_REST_Server::READABLE,
		// Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
		'callback' => 'intranet_fafar_api_get_submissions_by_object_name_handler',
	) );

	register_rest_route( 'intranet/v1', '/users/by_sector/(?P<sector>[\w]+)', array(
		// By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
		'methods' => WP_REST_Server::READABLE,
		// Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
		'callback' => 'intranet_fafar_api_get_users_by_sector_slug_handler',
	) );

	register_rest_route( 'intranet/v1', '/submissions/place/available-for-reservation', array(
		// By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
		'methods' => WP_REST_Server::READABLE,
		// Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
		'callback' => 'intranet_fafar_api_get_available_place_for_reservation_handler',
	) );

	register_rest_route( 'intranet/v1', '/submissions/(?P<place>[\w]+)/reservations', array(
		// By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
		'methods' => WP_REST_Server::READABLE,
		// Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
		'callback' => 'intranet_fafar_api_get_place_reservations_handler',
	) );

	register_rest_route( 'intranet/v1', '/users/(?P<id>[\w]+)', array(
		// By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
		'methods' => WP_REST_Server::READABLE,
		// Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
		'callback' => 'intranet_fafar_api_get_user_by_id_handler',
	) );

	register_rest_route( 'intranet/v1', '/users', array(
		// By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
		'methods' => WP_REST_Server::READABLE,
		// Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
		'callback' => 'intranet_fafar_api_get_users_handler',
	) );

	register_rest_route( 'intranet/v1', '/submissions/(?P<id>[\w]+)', array(
		// By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
		'methods' => WP_REST_Server::READABLE,
		// Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
		'callback' => 'intranet_fafar_api_get_submission_by_id_handler',
	) );

	// EDITABLE

	register_rest_route( 'intranet/v1', '/submissions/laboratory-team/(?P<id>[\w]+)/add/', array(
		// By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
		'methods' => WP_REST_Server::EDITABLE,
		// Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
		'callback' => 'intranet_fafar_api_add_collaborator_on_lab_team_handler',
	) );

	register_rest_route( 'intranet/v1', '/submissions/laboratory-team/(?P<id>[\w]+)/remove/', array(
		// By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
		'methods' => WP_REST_Server::EDITABLE,
		// Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
		'callback' => 'intranet_fafar_api_remove_collaborator_on_lab_team_handler',
	) );

	register_rest_route( 'intranet/v1', '/submissions/reservations/(?P<id>[\w]+)/set_technical', array(
		// By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
		'methods' => WP_REST_Server::EDITABLE,
		// Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
		'callback' => 'intranet_fafar_api_set_reservation_technical_handler',
	) );

	register_rest_route( 'intranet/v1', '/submissions/access_building_request/(?P<id>[\w]+)/register', array(
		// By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
		'methods' => WP_REST_Server::EDITABLE,
		// Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
		'callback' => 'intranet_fafar_api_register_entry_and_exit_handler',
	) );

	register_rest_route( 'intranet/v1', '/submissions/(?P<id>[\w]+)', array(
		// By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
		'methods' => WP_REST_Server::EDITABLE,
		// Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
		'callback' => 'intranet_fafar_api_update_submission_by_id_handler',
	) );

	// DELETABLE

	register_rest_route( 'intranet/v1', '/submissions/(?P<id>[\w]+)', array(
		// By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
		'methods' => WP_REST_Server::DELETABLE,
		// Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
		'callback' => 'intranet_fafar_api_delete_submission_by_id_handler',
	) );

}

function intranet_fafar_api_register_entry_and_exit_handler( $request ) {
	$id = (string) $request['id'];

	// Get data from the request
	$data = $request->get_json_params();

	$submission = intranet_fafar_api_register_entry_and_exit( $id, $data['type'] );

	if ( isset( $submission['error_msg'] ) ) {
		return new WP_Error( 'rest_api_sad', esc_html__( $submission['error_msg'], 'intranet-fafar-api' ), ( ( $submission['http_status'] ) ?? 400 ) );
	}

	return rest_ensure_response( $submission );
}

function intranet_fafar_api_register_entry_and_exit( $id, $type ) {
	if ( ! isset( $id ) )
		return array( 'error_msg' => 'Nenhum ID informado!' );

	if ( ! isset( $type ) )
		return array( 'error_msg' => 'Tipo não informado!' );

	$submission = intranet_fafar_api_get_submission_by_id( $id, false );

	if ( empty( $submission ) )
		return $submission;

	if ( ! isset( $submission['data']['logs'] ) || count( $submission['data']['logs'] ) === 0 ) {
		$submission['data']['logs'] = array( array( 'type' => $type, 'registered_at' => time() ) );
	} else {
		array_push( $submission['data']['logs'], array( 'type' => $type, 'registered_at' => time() ) );
	}

	$submission['data']['status'] = $type;

	return intranet_fafar_api_update( $id, $submission );
}

function intranet_fafar_api_update_submission_by_id_handler( $request ) {

	$id = (string) $request['id'];

	// Get data from the request
	$submission = $request->get_json_params();

	$response = intranet_fafar_api_update( $id, $submission );

	if ( isset( $response['error_msg'] ) ) {

		return new WP_Error( 'rest_api_sad', esc_html__( $response['error_msg'], 'intranet-fafar-api' ), ( ( $response['http_status'] ) ?? 400 ) );

	}

	if ( $submission['object_name'] === 'auditorium_reservation' ) {
		intranet_fafar_mail_on_change_auditorium_reservation_status( $submission );
	}

	return rest_ensure_response( $response );

}

function intranet_fafar_api_delete_submission_by_id_handler( $request ) {

	$id = (string) $request['id'];

	$submission = intranet_fafar_api_delete_submission_by_id( $id );

	if ( isset( $submission['error_msg'] ) ) {

		return new WP_Error( 'rest_api_sad', esc_html__( $submission['error_msg'], 'intranet-fafar-api' ), ( ( $submission['http_status'] ) ?? 400 ) );

	}

	return rest_ensure_response( $submission );

}

function intranet_fafar_api_delete_submission_by_id( $id ) {

	if ( ! isset( $id ) || ! $id ) {

		intranet_fafar_logs_register_log(
			'ERROR',
			'intranet_fafar_api_delete_submission_by_id',
			json_encode(
				array(
					'func' => 'intranet_fafar_api_delete_submission_by_id',
					'msg' => 'ID nor set or falsy, received',
					'obj' => $id,
				),
			),
		);

		return array( 'error_msg' => 'Nenhum ID informado', 'http_status' => 400 );

	}


	$submission = intranet_fafar_api_get_submission_by_id( $id, false );

	if ( empty( $submission ) )
		return $submission;

	$submission = intranet_fafar_api_delete( $submission );

	return $submission;
}

function intranet_fafar_api_get_reservable_places() {
	$places = intranet_fafar_api_get_submissions_by_object_name(
		'place',
		array(
			'orderby_json' => 'number',
		),
		array(),
		false
	);

	if ( empty( $places ) || empty( $places['data'] ) )
		return [];

	$reservables = [ "classroom", "living_room", "computer_lab", "multimedia_room" ];

	return array_filter( $places['data'], function ($place) use ($reservables) {
		return (
			! empty( $place['data']['object_sub_type'][0] ) &&
			in_array( $place['data']['object_sub_type'][0], $reservables )
		);
	} );
}

function intranet_fafar_api_get_not_reservable_places() {
	$places = intranet_fafar_api_get_submissions_by_object_name(
		'place',
		array(
			'orderby_json' => 'number',
		),
		array(),
		false
	);

	if ( empty( $places ) || empty( $places['data'] ) )
		return [];

	$reservables = [ "classroom", "living_room", "computer_lab", "multimedia_room" ];

	return array_filter( $places['data'], function ($place) use ($reservables) {
		return (
			empty( $place['data']['object_sub_type'][0] ) ||
			! in_array( $place['data']['object_sub_type'][0], $reservables )
		);
	} );
}

function intranet_fafar_api_get_place_reservations_handler( $request ) {
	$place_id = (string) $request['place'];

	$submissions = intranet_fafar_api_get_reservations_by_place( $place_id );

	if ( isset( $submissions['error_msg'] ) ) {
		return new WP_Error( 'rest_api_sad', esc_html__( $submissions['error_msg'], 'intranet-fafar-api' ), ( ( $submissions['http_status'] ) ?? 400 ) );
	}

	$submissions = array_map( function ($submission) {

		$role_slug = $submission['group_owner'];

		$submission['group_owner'] = isset( wp_roles()->roles[ $role_slug ] ) ? isset( wp_roles()->roles[ $role_slug ] ) : '';

		return $submission;

	}, $submissions );

	return rest_ensure_response( $submissions );

}

function intranet_fafar_api_bff_reservations_prepare( $reservations, $attr_wl ) {


	$arr = array();
	foreach ( $reservations as $reservation ) {

		$item_arr = array();
		foreach ( $reservation as $key => $value ) {

			if ( ! in_array( $key, $attr_wl ) )
				continue;

			$value = ( is_array( $value ) ? $value[0] : $value );
			if ( $key == 'class_subject' ) {

				$class_subject = (array) intranet_fafar_api_get_submission_by_id( $value );
				$item_arr['class_subject'] = array(
					'id' => $class_subject['id'],
					'code' => $class_subject['code'],
					'name_of_subject' => $class_subject['name_of_subject'],
					'group' => $class_subject['group']
				);

				continue;

			}

			$item_arr[ $key ] = $value;

		}

		array_push( $arr, $item_arr );

	}

	return $arr;

}

function intranet_fafar_api_create_new_loan( $form_data ) {

	// Verificações iniciais
	if ( ! isset( $form_data['object_name'] ) )
		return $form_data;

	if ( $form_data['object_name'] !== 'equipament_loan' )
		return $form_data;

	$form_data['data'] = json_decode( $form_data['data'], true );

	if ( ! isset( $form_data['data']['loan_date'] ) )
		return array( 'error_msg' => 'Data de empréstimo não informada!' );

	$equipament = intranet_fafar_api_get_submission_by_id( $form_data['data']['equipament'], false );

	if ( empty( $equipament ) )
		return array( 'error_msg' => 'Equipamento não existe!' );

	if ( isset( $equipament['data']['on_loan'] ) && $equipament['data']['on_loan'] )
		return array( 'error_msg' => 'Equipamento está emprestado!' );

	$equipament['data']['on_loan'] = '1';

	$equipament = intranet_fafar_api_update( $equipament['id'], $equipament );

	if ( isset( $equipament['error_msg'] ) )
		return array( 'error_msg' => $equipament['error_msg'] );

	$form_data['data'] = json_encode( $form_data['data'] );

	return $form_data;
}

function intranet_fafar_api_register_loan_return( $form_data ) {

	// Verificações iniciais
	if ( ! isset( $form_data['object_name'] ) )
		return $form_data;

	if ( $form_data['object_name'] !== 'equipament_loan_return' )
		return $form_data;

	// Atualizando a propriedade 'on_loan' do equipamento
	$form_data['data'] = json_decode( $form_data['data'], true );

	if ( ! isset( $form_data['data']['return_date'] ) )
		return array( 'error_msg' => 'Data de retorno não informada!' );

	$equipament = intranet_fafar_api_get_submission_by_id( $form_data['data']['equipament'], false );

	if ( empty( $equipament ) )
		return array( 'error_msg' => 'Equipamento não existe!' );

	$equipament['data']['on_loan'] = 0;

	$equipament = intranet_fafar_api_update( $equipament['id'], $equipament );

	if ( isset( $equipament['error_msg'] ) )
		return array( 'error_msg' => $equipament['error_msg'] );

	// Atualizando o status do empréstimo do equipamento
	$loans = intranet_fafar_api_get_loans_by_equipament( $form_data['data']['equipament'] );

	$loan = $loans[0];

	if ( ! $loan )
		return array( 'error_msg' => 'Equipamento atualizado. Porém, ' . $loan['error_msg'] );

	$loan['data']['returned'] = '1';
	$loan['data']['return_date'] = $form_data['data']['return_date']; // Verificado no topo
	$loan['data']['return_desc'] = ( ( $form_data['data']['return_desc'] ) ?? '' );

	$loan = intranet_fafar_api_update( $loan['id'], $loan );

	if ( isset( $loan['error_msg'] ) )
		return array( 'error_msg' => 'Equipamento atualizado. Porém, ' . $loan['error_msg'] );

	// Retorna uma obj genérico para concluir a submissão com sucesso
	return array( 'far_prevent_submit' => true );

}

function intranet_fafar_api_create_service_ticket( $form_data ) {

	// Verificações iniciais
	if ( ! isset( $form_data['object_name'] ) )
		return $form_data;

	if ( $form_data['object_name'] !== 'service_ticket' )
		return $form_data;

	$form_data['data'] = json_decode( $form_data['data'], true );

	/*
	 * Na intranet anterior, era gerado IDs das ordens de serviços de forma incremental.
	 * Foi solicitado que fosse mantido esse padrão.
	 * 
	 * Essa linha cria um parâmetro para receber esse 'ID' incremental, mantendo o padrão.
	 */
	$new_number = get_incremental_service_ticker_number();

	$form_data['data']['number'] = str_pad( $new_number, 6, '0', STR_PAD_LEFT );

	// Se tudo deu certo, então apenas retorna o objeto para ser inserido
	$form_data['data'] = json_encode( $form_data['data'] );

	// Retorna uma obj genérico para concluir a submissão com sucesso
	return $form_data;

}

function get_incremental_service_ticker_number() {

	$service_tickets = intranet_fafar_api_get_submissions_by_object_name( 'service_ticket', array( 'orderby_column' => 'created_at', 'order' => 'DESC' ) );

	if ( isset( $service_tickets['error_msg'] ) )
		return 1;

	if ( ! isset( $service_tickets[0]['data']['number'] ) )
		return 1;

	if ( ! is_numeric( $service_tickets[0]['data']['number'] ) )
		return 1;

	return ( (int) $service_tickets[0]['data']['number'] ) + 1;

}

function intranet_fafar_api_create_rapid_service_ticket( $form_data ) {
	// Verificações iniciais
	if ( ! isset( $form_data['object_name'] ) )
		return $form_data;

	if ( $form_data['object_name'] !== 'rapid_service_ticket' )
		return $form_data;

	$form_data['data'] = json_decode( $form_data['data'], true );

	if (
		empty( $form_data['data']['assigned_to'] ) ||
		empty( $form_data['data']['departament_assigned_to'] ) ||
		empty( $form_data['data']['status'] ) ||
		empty( $form_data['data']['type'][0] ) ||
		empty( $form_data['data']['sub_type'][0] ) ||
		empty( $form_data['data']['user_report'] ) ||
		empty( $form_data['data']['service_report'] )
	)
		return array( 'error_msg' => 'Faltando campos necessários: atribuido, departamento atribuido, status, tipo, sub-tipo, problema e/ ou solução' );

	// Preparando o objeto de ordem de serviço
	$new_service_ticket['object_name'] = 'service_ticket';
	$new_service_ticket['owner'] = ! empty( $form_data['owner'] ) ? $form_data['owner'] : '';
	$new_service_ticket['group_owner'] = ! empty( $form_data['group_owner'] ) ? $form_data['group_owner'] : '';
	$new_service_ticket['permissions'] = ! empty( $form_data['permissions'] ) ? $form_data['permissions'] : '';

	$new_service_ticket['data']['assigned_to'] = $form_data['data']['assigned_to'];
	$new_service_ticket['data']['departament_assigned_to'][0] = $form_data['data']['departament_assigned_to'];
	$new_service_ticket['data']['status'] = $form_data['data']['status'];
	$new_service_ticket['data']['type'] = $form_data['data']['type'][0];
	$new_service_ticket['data']['sub_type'] = $form_data['data']['sub_type'][0];
	$new_service_ticket['data']['user_report'] = $form_data['data']['user_report'];
	$new_service_ticket['data']['place'] = $form_data['data']['place'];

	$new_service_ticket['data'] = json_encode( $new_service_ticket['data'] );

	$new_service_ticket = intranet_fafar_api_create_service_ticket( $new_service_ticket );

	$new_submission = intranet_fafar_api_create( $new_service_ticket );

	if ( isset( $new_submission['error_msg'] ) )
		return $new_submission;

	// Preparando o objeto de atualização da ordem de serviço
	$new_service_ticket_update['object_name'] = 'service_ticket_update';
	$new_service_ticket_update['owner'] = ! empty( $form_data['owner'] ) ? $form_data['owner'] : '';
	$new_service_ticket_update['group_owner'] = ! empty( $form_data['group_owner'] ) ? $form_data['group_owner'] : '';
	$new_service_ticket_update['permissions'] = ! empty( $form_data['permissions'] ) ? $form_data['permissions'] : '';

	$new_service_ticket_update['data']['service_ticket'] = $new_submission['id'];
	$new_service_ticket_update['data']['status'][0] = $form_data['data']['status'];
	$new_service_ticket_update['data']['service_report'] = $form_data['data']['service_report'];

	$new_service_ticket_update['data'] = json_encode( $new_service_ticket_update['data'] );

	/*
	 * Não é necessário passar para 'intranet_fafar_api_insert_update_on_service_ticket'
	 * porque ela só altera o status da OS, e isso já foi feito na criação dela, acima.
	 */
	$new_submission = intranet_fafar_api_create( $new_service_ticket_update );

	if ( isset( $new_submission['error_msg'] ) )
		return $new_submission;

	/*
	 * O retorno não importa porque o formulário já conta com um campo
	 * definindo que será feita a submissão pelo FAFARCF7CRUD: 
	 * [hidden far_prevent_submit "1"]
	 */
	return true;

}

function intranet_fafar_api_insert_update_on_service_ticket( $form_data ) {
	// Verificações iniciais
	if ( ! isset( $form_data['object_name'] ) )
		return $form_data;

	if ( $form_data['object_name'] !== 'service_ticket_update' )
		return $form_data;

	$form_data['data'] = json_decode( $form_data['data'], true );

	// Atualizando a propriedade 'status' da ordem de serviço
	if ( ! isset( $form_data['data']['status'][0] ) )
		return array( 'error_msg' => 'Status não informado!' );

	$service_ticket = intranet_fafar_api_get_submission_by_id( $form_data['data']['service_ticket'], false );

	if ( empty( $service_ticket ) )
		return $service_ticket;

	$service_ticket['data']['status'] = $form_data['data']['status'][0];

	$service_ticket = intranet_fafar_api_update( $service_ticket['id'], $service_ticket );

	if ( isset( $service_ticket['error_msg'] ) )
		return $service_ticket;

	// Se tudo deu certo, então apenas retorna o objeto para ser inserido
	$form_data['data'] = json_encode( $form_data['data'] );

	// Retorna uma obj genérico para concluir a submissão com sucesso
	return $form_data;
}

function intranet_fafar_api_get_loans_by_equipament( $id ) {
	if ( ! isset( $id ) || ! $id ) {

		intranet_fafar_logs_register_log(
			'ERROR',
			'intranet_fafar_api_get_loans_by_equipament',
			json_encode(
				array(
					'func' => 'intranet_fafar_api_get_loans_by_equipament',
					'msg' => 'ID nor set or falsy, received',
					'obj' => $id,
				)
			),
		);

		return array( 'error_msg' => 'Nenhum ID informado', 'http_status' => 400 );

	}

	/* 
	 * Montando a query SQL.
	 * Pesquisa por equipamento com o ID informado e 
	 * ordena do empréstimo mais recente ao mais antigo
	 */
	$query = "SELECT * FROM `SET_TABLE_NAME` WHERE ";

	$query .= 'JSON_CONTAINS( data, \'' . json_encode( array( 'equipament' => $id ) ) . '\')';

	$query .= " ORDER BY created_at DESC";

	// Fluxo padrão de leitura
	$submissions = intranet_fafar_api_read( $query );

	if ( ! $submissions || count( $submissions ) == 0 ) {

		return array( 'error_msg' => 'Nenhum empréstimo encontrado com equipamento de ID "' . ( ( isset( $id ) && $id ) ? $id : 'UNKNOW_ID' ) . '"', 'http_status' => 400 );

	}

	return $submissions;
}

function intranet_fafar_api_get_service_tickets_handler( $request ) {
	$offset = $request->get_param( 'offset' ) ? intval( $request->get_param( 'offset' ) ) : 1;
	$limit = $request->get_param( 'limit' ) ? intval( $request->get_param( 'limit' ) ) : -1;
	$number = $request->get_param( 'number' ) && is_numeric( $request->get_param( 'number' ) ) ? intval( $request->get_param( 'number' ) ) : null;
	$status = $request->get_param( 'status' ) ? sanitize_text_field( $request->get_param( 'status' ) ) : null;
	$departament_assigned_to = $request->get_param( 'departament_assigned_to' ) ? sanitize_text_field( $request->get_param( 'departament_assigned_to' ) ) : null;
	$type = $request->get_param( 'type' ) ? sanitize_text_field( $request->get_param( 'type' ) ) : null;
	$created_at_from = $request->get_param( 'created_at_from' ) ? sanitize_text_field( $request->get_param( 'created_at_from' ) ) : null;
	$created_at_until = $request->get_param( 'created_at_until' ) ? sanitize_text_field( $request->get_param( 'created_at_until' ) ) : null;
	$place = $request->get_param( 'place' ) ? sanitize_text_field( $request->get_param( 'place' ) ) : null;
	$owner = $request->get_param( 'owner' ) && is_numeric( $request->get_param( 'owner' ) ) ? intval( $request->get_param( 'owner' ) ) : null;
	$assigned_to = $request->get_param( 'assigned_to' ) && is_numeric( $request->get_param( 'assigned_to' ) ) ? intval( $request->get_param( 'assigned_to' ) ) : null;
	$user_report = $request->get_param( 'user_report' ) ? sanitize_text_field( $request->get_param( 'user_report' ) ) : null;
	$service_report = $request->get_param( 'service_report' ) ? sanitize_text_field( $request->get_param( 'service_report' ) ) : null;


	$query_params = array(
		'filters' => array(
			array(
				'column' => 'object_name',
				'value' => 'service_ticket',
				'operator' => '=',
			),
		),
		'order_by' => array(
			'orderby_json' => 'number',
			'order' => 'DESC',
		),
		'page' => $offset,
		'per_page' => $limit,
		'relationships' => array(
			'applicant' => array(
				'type' => 'user',
				'local_path' => 'owner',
			),
			'place' => array(
				'type' => 'submission',
				'local_path' => 'data->place',
				'array_compare' => true,
			),
			'assigned_to' => array(
				'type' => 'user',
				'local_path' => 'data->assigned_to',
				'array_compare' => true,
			),
		),
	);

	if ( $number ) {
		$query_params['filters'][] = array(
			'column' => 'data->number',
			'value' => $number,
			'operator' => 'LIKE',
		);
	}

	if ( ! empty( $status ) && is_string( $status ) ) {
		$status_arr = array_map( fn( $s ) => strtolower( $s ), explode( ',', $status ) );

		$query_params['filters'][] = array(
			'column' => 'data->status',
			'value' => $status_arr,
			'operator' => 'IN',
			'case_sensitive' => false,
		);
	}

	if ( $departament_assigned_to ) {
		$query_params['filters'][] = array(
			'column' => 'data->departament_assigned_to',
			'value' => '["' . $departament_assigned_to . '"]',
			'operator' => '=',
		);
	}

	if ( $type ) {
		$query_params['filters'][] = array(
			'column' => 'data->type',
			'value' => $type,
			'operator' => '=',
		);
	}

	if ( $created_at_from ) {
		$query_params['filters'][] = array(
			'column' => 'created_at',
			'value' => $created_at_from,
			'operator' => '>',
		);
	}

	if ( $created_at_until ) {
		$query_params['filters'][] = array(
			'column' => 'created_at',
			'value' => $created_at_until,
			'operator' => '<',
		);
	}

	if ( $place ) {
		$query_params['filters'][] = array(
			'column' => 'data->place',
			'value' => '["' . $place . '"]',
			'operator' => '=',
		);
	}

	if ( $owner ) {
		$query_params['filters'][] = array(
			'column' => 'owner',
			'value' => $owner,
			'operator' => '=',
		);
	}

	if ( $assigned_to ) {
		$query_params['filters'][] = array(
			'column' => 'data->assigned_to',
			'value' => $assigned_to,
			'operator' => '=',
		);
	}

	if ( $user_report ) {
		$query_params['filters'][] = array(
			'column' => 'data->user_report',
			'value' => $user_report,
			'operator' => 'LIKE',
		);
	}

	if ( $service_report ) {
		$query_params['keyword'] = $service_report;
	}

	error_log( print_r( $query_params, true ) );

	$submissions = intranet_fafar_api_read( args: $query_params );

	if ( $submissions === false ) {
		return new WP_Error(
			'rest_api_sad',
			esc_html__( ( ! empty( $submissions['error_msg'] ) ? $submissions['error_msg'] : 'Erro no processamento' ), 'intranet-fafar-api' ),
			( ! empty( $submissions['http_status'] ) ? $submissions['http_status'] : 500 ),
		);
	}

	if ( empty( $submissions ) ) {
		return rest_ensure_response(
			array(
				'count' => 0,
				'next' => null,
				'previous' => null,
				'results' => [],
			)
		);
	}

	$submissions['data'] = array_map( function ($submission) {
		if ( isset( $submission['data']['departament_assigned_to'][0] ) ) {
			// Get the display name of the role
			$role_slug = $submission['data']['departament_assigned_to'][0];

			$role_display_name = '--';

			if ( isset( wp_roles()->roles[ $role_slug ] ) )
				$role_display_name = wp_roles()->roles[ $role_slug ]['name'];

			$submission['relationships']['departament_assigned_to'] = array( 'role_slug' => $role_slug, 'role_display_name' => $role_display_name );
		}

		return $submission;
	}, $submissions['data'] );

	return rest_ensure_response(
		array(
			'count' => $submissions['pagination']['total_items'],
			'next' => null,
			'previous' => null,
			'results' => $submissions['data'],
		)
	);
}

function intranet_fafar_api_get_service_tickets_by_user_handler( $request ) {
	$keyword = $request->get_param( 'keyword' ) ? sanitize_text_field( $request->get_param( 'keyword' ) ) : '';
	$offset = $request->get_param( 'offset' ) ? intval( $request->get_param( 'offset' ) ) : 1;
	$limit = $request->get_param( 'limit' ) ? intval( $request->get_param( 'limit' ) ) : -1;
	$user_id = $request->get_param( 'user_id' ) && is_numeric( $request->get_param( 'user_id' ) ) ? intval( $request->get_param( 'user_id' ) ) : null;

	$submissions = intranet_fafar_api_get_service_tickets_by_user( $user_id, $keyword, $offset, $limit );

	if ( $submissions === false ) {
		return new WP_Error(
			'rest_api_sad',
			esc_html__( ( ! empty( $submissions['error_msg'] ) ? $submissions['error_msg'] : 'Erro no processamento' ), 'intranet-fafar-api' ),
			( ! empty( $submissions['http_status'] ) ? $submissions['http_status'] : 500 ),
		);
	}

	if ( empty( $submissions ) ) {
		return rest_ensure_response(
			array(
				'count' => 0,
				'next' => null,
				'previous' => null,
				'results' => [],
			)
		);
	}

	return rest_ensure_response(
		array(
			'count' => $submissions['pagination']['total_items'],
			'next' => null,
			'previous' => null,
			'results' => $submissions['data'],
		)
	);

}

function intranet_fafar_api_get_service_tickets_by_user(
	$user_id = null,
	$keyword = '',
	$offset = 1,
	$limit = -1,
	$substitute_value = true
) {
	$query_params = array(
		'filters' => array(
			array(
				'column' => 'object_name',
				'value' => 'service_ticket',
				'operator' => '=',
			)
		),
		'order_by' => array(
			'orderby_column' => 'created_at',
			'order' => 'DESC',
		),
		'page' => $offset,
		'per_page' => $limit,
		'keyword' => $keyword,
	);

	if ( ! $user_id )
		$user_id = get_current_user_id();

	// Se não for Administrador
	if ( $user_id !== 1 ) {
		$query_params['filters'][] = array(
			'column' => 'owner',
			'value' => $user_id,
			'operator' => '=',
		);
	}

	$submissions = intranet_fafar_api_read( args: $query_params );

	// null ou []
	if ( empty( $submissions ) )
		return $submissions;

	if ( ! $substitute_value )
		return $submissions;

	$submissions['data'] = array_map( function ($submission) {
		if ( isset( $submission['owner'] ) && is_numeric( $submission['owner'] ) )
			$submission['owner'] = intranet_fafar_api_get_user_by_id( $submission['owner'] );

		if ( isset( $submission['data']['place'][0] ) )
			$submission['data']['place'] = intranet_fafar_api_get_submission_by_id( $submission['data']['place'][0] );

		if ( isset( $submission['data']['departament_assigned_to'][0] ) ) {

			// Get the display name of the role
			$role_slug = $submission['data']['departament_assigned_to'][0];

			$role_display_name = '--';

			if ( isset( wp_roles()->roles[ $role_slug ] ) )
				$role_display_name = wp_roles()->roles[ $role_slug ]['name'];

			$submission['data']['departament_assigned_to'] = array( 'role_slug' => $role_slug, 'role_display_name' => $role_display_name );

		}

		if ( isset( $submission['data']['assigned_to'] ) ) {

			$submission['data']['assigned_to'] = intranet_fafar_api_get_user_by_id( $submission['data']['assigned_to'] );

		}

		return $submission;
	}, $submissions['data'] );

	return $submissions;
}

function intranet_fafar_api_get_service_tickets_by_departament_handler( $request ) {
	$keyword = $request->get_param( 'keyword' ) ? sanitize_text_field( $request->get_param( 'keyword' ) ) : '';
	$offset = $request->get_param( 'offset' ) ? intval( $request->get_param( 'offset' ) ) : 1;
	$limit = $request->get_param( 'limit' ) ? intval( $request->get_param( 'limit' ) ) : -1;
	$status = $request->get_param( 'status' ) ? sanitize_text_field( $request->get_param( 'status' ) ) : null;
	$assigned_to = $request->get_param( 'assigned_to' ) && is_numeric( $request->get_param( 'assigned_to' ) ) ? intval( $request->get_param( 'assigned_to' ) ) : null;
	$departament = $request->get_param( 'departament' ) ? sanitize_text_field( $request->get_param( 'departament' ) ) : null;

	$submissions = intranet_fafar_api_get_service_tickets_by_departament( $departament, $status, $assigned_to, $keyword, $offset, $limit );

	if ( $submissions === false ) {
		return new WP_Error(
			'rest_api_sad',
			esc_html__( ( ! empty( $submissions['error_msg'] ) ? $submissions['error_msg'] : 'Erro no processamento' ), 'intranet-fafar-api' ),
			( ! empty( $submissions['http_status'] ) ? $submissions['http_status'] : 500 ),
		);
	}

	if ( empty( $submissions ) ) {
		return rest_ensure_response(
			array(
				'count' => 0,
				'next' => null,
				'previous' => null,
				'results' => [],
			)
		);
	}

	return rest_ensure_response(
		array(
			'count' => $submissions['pagination']['total_items'],
			'next' => null,
			'previous' => null,
			'results' => $submissions['data'],
		)
	);
}

function intranet_fafar_api_get_service_tickets_by_departament(
	$departament = null,
	$status = null,
	$assigned_to = null,
	$keyword = '',
	$offset = 1,
	$limit = -1
) {
	if ( ! $departament ) {
		$user = wp_get_current_user();
		$role_slug = $user->roles[0];
		$departament = $role_slug;
	}

	$query_params = array(
		'filters' => array(
			array(
				'column' => 'object_name',
				'value' => 'service_ticket',
				'operator' => '=',
			),
			array(
				'column' => 'data->departament_assigned_to',
				'value' => '["' . $departament . '"]',
				'operator' => '=',
			),
		),
		'order_by' => array(
			'orderby_json' => 'number',
			'order' => 'DESC',
		),
		'page' => $offset,
		'per_page' => $limit,
		'keyword' => $keyword,
		'relationships' => array(
			'applicant' => array(
				'type' => 'user',
				'local_path' => 'owner',
			),
			'place' => array(
				'type' => 'submission',
				'local_path' => 'data->place',
				'array_compare' => true,
			),
			'assigned_to' => array(
				'type' => 'user',
				'local_path' => 'data->assigned_to',
				'array_compare' => true,
			),
		),
	);

	if ( isset( $assigned_to ) ) {
		if ( $assigned_to == -1 )
			$assigned_to = get_current_user_id();

		$query_params['filters'][] = array(
			'column' => 'data->assigned_to',
			'value' => $assigned_to,
			'operator' => '=',
		);
	}

	if ( ! empty( $status ) && is_string( $status ) ) {
		$status_arr = array_map( fn( $s ) => strtolower( $s ), explode( ',', $status ) );

		$query_params['filters'][] = array(
			'column' => 'data->status',
			'value' => $status_arr,
			'operator' => 'IN',
			'case_sensitive' => false,
		);
	}

	$submissions = intranet_fafar_api_read( args: $query_params );

	// null ou []
	if ( empty( $submissions ) )
		return $submissions;

	$submissions['data'] = array_map( function ($submission) {
		if ( isset( $submission['data']['departament_assigned_to'][0] ) ) {
			// Get the display name of the role
			$role_slug = $submission['data']['departament_assigned_to'][0];

			$role_display_name = '--';

			if ( isset( wp_roles()->roles[ $role_slug ] ) )
				$role_display_name = wp_roles()->roles[ $role_slug ]['name'];

			$submission['data']['relationships']['departament_assigned_to'] = array( 'role_slug' => $role_slug, 'role_display_name' => $role_display_name );
		}

		return $submission;
	}, $submissions['data'] );

	return $submissions;
}

function intranet_fafar_api_get_service_ticket_by_id( $id ) {

	$service_ticket = intranet_fafar_api_get_submission_by_id( $id );

	if ( isset( $service_ticket['error_msg'] ) )
		return array( 'error_msg' => $service_ticket['error_msg'] );

	if ( empty( $service_ticket ) )
		return array( 'error_msg' => 'Nenhuma ordem de serviço encontrada!' );

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

	if ( isset( $service_ticket['data']['assigned_to'] ) ) {

		$service_ticket['data']['assigned_to'] = intranet_fafar_api_get_user_by_id( $service_ticket['data']['assigned_to'] );

	}

	return $service_ticket;

}

function intranet_fafar_api_send_email( $request ) {
	$request_data = $request->get_json_params();

	error_log( print_r( $request_data, true ) );

	if ( ! $request_data ) {
		return new WP_Error(
			'rest_api_sad',
			esc_html__( 'No data', 'intranet-fafar-api' ),
			400,
		);
	}

	if (
		empty( $request_data['to'] ) ||
		empty( $request_data['subject'] ) ||
		empty( $request_data['message'] )
	) {
		return new WP_Error(
			'rest_api_sad',
			esc_html__( 'Missing to, suject and/or message', 'intranet-fafar-api' ),
			400,
		);
	}

	$response = intranet_fafar_mail_notify( $request_data['to'], $request_data['subject'], $request_data['message'] );

	if ( ! $response ) {
		return new WP_Error(
			'rest_api_sad',
			esc_html__( 'Fail to send email!', 'intranet-fafar-api' ),
			400,
		);
	}

	return rest_ensure_response( array( 'msg' => 'Email sent successfully' ) );
}

/**
 * Essa função, diferente das outras, trata dados recebidos por um bot.
 * Tais dados não virão de outra fonte.
 */
function intranet_fafar_api_create_service_ticket_update_handler( $request ) {
	$request_data = $request->get_json_params();

	if ( ! $request_data ) {
		return new WP_Error(
			'rest_api_sad',
			esc_html__( 'No data', 'intranet-fafar-api' ),
			400,
		);
	}

	$service_ticket = intranet_fafar_api_read(
		args: array(
			'filters' => array(
				array(
					'column' => 'object_name',
					'value' => 'service_ticket',
					'operator' => '=',
				),
				array(
					'column' => 'data->number',
					'value' => str_pad( $request_data['intranet_service_ticket_number'], 6, "0", STR_PAD_LEFT ),
					'operator' => '=',
				),
			),
			'single' => true,
		)
	);

	if ( ! $service_ticket ) {
		return new WP_Error(
			'rest_api_sad',
			esc_html__( 'Nenhuma OS encontrado', 'intranet-fafar-api' ),
			400,
		);
	}

	if ( $service_ticket['data']['status'] === 'Finalizada' ) {
		return rest_ensure_response( array( 'status' => 'Finalizada', 'msg' => 'OS conta como Finalizada' ) );
	}

	if ( $service_ticket['data']['status'] === 'Cancelada' ) {
		return rest_ensure_response( array( 'status' => 'Cancelada', 'msg' => 'OS conta como Cancelada' ) );
	}

	if (
		in_array(
			strtolower( $request_data['last_status'] ),
			array(
				'encerrada',
				'cancelada',
			)
		)
	)
		$request_data['last_status'] = 'Finalizada';

	if ( strtolower( $request_data['last_status'] ) === 'aberta' )
		$request_data['last_status'] = 'Em andamento';

	$new_service_ticket_update = array(
		'object_name' => 'service_ticket_update',
		'permissions' => '774',
		'owner' => '1745', // ID da Marilda Coura
		'group_owner' => 'apoio_logistico_e_operacional',
		'data' => array(
			'service_ticket' => $service_ticket['id'],
			'status' => array( $request_data['last_status'] ),
			'service_report' => $request_data['last_full_report'],
		),
	);

	// Atualizando a propriedade 'status' da ordem de serviço
	if ( ! isset( $new_service_ticket_update['data']['status'][0] ) ) {
		return new WP_Error(
			'rest_api_sad',
			esc_html__( 'Status não informado', 'intranet-fafar-api' ),
			400,
		);
	}

	$service_ticket['data']['status'] = $new_service_ticket_update['data']['status'][0];

	$result = intranet_fafar_api_update( $service_ticket['id'], $service_ticket );

	if ( empty( $result ) && isset( $result['error_msg'] ) ) {
		return new WP_Error(
			'rest_api_sad',
			esc_html__( 'Erro no prepararo do objeto', 'intranet-fafar-api' ),
			400,
		);
	}

	$result = intranet_fafar_api_create( $new_service_ticket_update );

	if ( isset( $result['error_msg'] ) ) {
		return new WP_Error(
			'rest_api_sad',
			esc_html__( 'Erro ao criar', 'intranet-fafar-api' ),
			400,
		);
	}

	$new_service_ticket_update['data'] = json_encode( $new_service_ticket_update['data'] );

	intranet_fafar_mail_on_create_service_ticket_update( $new_service_ticket_update );

	return rest_ensure_response( $result );
}

function intranet_fafar_api_get_service_ticket_updates_by_service_ticket_handler( $request ) {
	$service_ticket_id = (string) $request['id'];

	$submissions = intranet_fafar_api_get_service_ticket_updates_by_service_ticket( $service_ticket_id );

	if ( isset( $submissions['error_msg'] ) ) {

		return new WP_Error( 'rest_api_sad', esc_html__( $submissions['error_msg'], 'intranet-fafar-api' ), ( ( $submissions['http_status'] ) ?? 400 ) );

	}

	return rest_ensure_response( $submissions );
}

function intranet_fafar_api_get_service_ticket_updates_by_service_ticket( $service_ticket_id ) {

	if ( ! $service_ticket_id )
		return array( 'error_msg' => 'Nenhum ID informado!' );

	$query = "SELECT * FROM `SET_TABLE_NAME` WHERE `object_name` = 'service_ticket_update' AND JSON_CONTAINS(data, '\"" . $service_ticket_id . "\"', '$.service_ticket') ORDER BY created_at DESC";

	$service_ticket_updates = intranet_fafar_api_read( $query );

	if ( isset( $service_ticket_updates['error_msg'] ) )
		return $service_ticket_updates['error_msg'];

	if ( empty( $service_ticket_updates ) )
		return array( 'error_msg' => 'Nenhuma atualização da ordem de serviço encontrada do usuário atual!' );

	for ( $i = 0; $i < count( $service_ticket_updates ); $i++ ) {

		/*
		 * Substituir os campos que tem ID de outro objeto,
		 * pelo objeto de mesmo ID
		 */
		if ( isset( $service_ticket_updates[ $i ]['owner'] ) && is_numeric( $service_ticket_updates[ $i ]['owner'] ) )
			$service_ticket_updates[ $i ]['owner'] = intranet_fafar_api_get_user_by_id( $service_ticket_updates[ $i ]['owner'] );


	}

	return $service_ticket_updates;

}

function intranet_fafar_api_get_service_ticket_evaluation_by_id( $id ) {

	if (
		! isset( $id ) &&
		! $id
	)
		return array( 'error_msg' => 'Nenhum ID informado' );

	$service_evalutations = intranet_fafar_api_get_submissions_by_object_name( 'service_evaluation' );

	if ( isset( $service_evalutations['error_msg'] ) )
		return $service_evalutations;

	$filtered = array_filter( $service_evalutations, function ($service_evalutations) use ($id) {
		return ( $service_evalutations['data']['service_ticket'] == $id );
	} );

	$filtered = array_values( $filtered );

	return $filtered;

}

function intranet_fafar_api_get_access_building_request_handler( $request ) {
	// Get pagination parameters from the request
	$offset = $request->get_param( 'offset' ) ? intval( $request->get_param( 'offset' ) ) : 1;
	$limit = $request->get_param( 'limit' ) ? intval( $request->get_param( 'limit' ) ) : -1;
	$keyword = $request->get_param( 'keyword' ) ? sanitize_text_field( $request->get_param( 'keyword' ) ) : '';
	$status = $request->get_param( 'status' ) ? sanitize_text_field( $request->get_param( 'status' ) ) : '';

	$submissions = intranet_fafar_api_get_access_building_request( false, $status, $keyword, $offset, $limit, true );

	if ( $submissions === false ) {
		return new WP_Error(
			'rest_api_sad',
			esc_html__( ( ! empty( $submissions['error_msg'] ) ? $submissions['error_msg'] : 'Erro no processamento' ), 'intranet-fafar-api' ),
			( ! empty( $submissions['http_status'] ) ? $submissions['http_status'] : 500 ),
		);
	}

	if ( empty( $submissions ) ) {
		return rest_ensure_response(
			array(
				'count' => 0,
				'next' => null,
				'previous' => null,
				'results' => [],
			)
		);
	}

	return rest_ensure_response(
		array(
			'count' => $submissions['pagination']['total_items'],
			'next' => null,
			'previous' => null,
			'results' => $submissions['data'],
		)
	);
}

function intranet_fafar_api_get_access_building_request_by_owner_handler( $request ) {
	// Get pagination parameters from the request
	$offset = $request->get_param( 'offset' ) ? intval( $request->get_param( 'offset' ) ) : 1;
	$limit = $request->get_param( 'limit' ) ? intval( $request->get_param( 'limit' ) ) : -1;
	$keyword = $request->get_param( 'keyword' ) ? sanitize_text_field( $request->get_param( 'keyword' ) ) : '';

	$submissions = intranet_fafar_api_get_access_building_request( true, $keyword, $offset, $limit, true );

	if ( $submissions === false ) {
		return new WP_Error(
			'rest_api_sad',
			esc_html__( ( ! empty( $submissions['error_msg'] ) ? $submissions['error_msg'] : 'Erro no processamento' ), 'intranet-fafar-api' ),
			( ! empty( $submissions['http_status'] ) ? $submissions['http_status'] : 500 ),
		);
	}

	if ( empty( $submissions ) ) {
		return rest_ensure_response(
			array(
				'count' => 0,
				'next' => null,
				'previous' => null,
				'results' => [],
			)
		);
	}

	return rest_ensure_response(
		array(
			'count' => $submissions['pagination']['total_items'],
			'next' => null,
			'previous' => null,
			'results' => $submissions['data'],
		)
	);
}

function intranet_fafar_api_get_access_building_request( $by_owner = false, $status = '', $keyword = '', $offset = 1, $limit = -1, $substitute_value = true ) {
	$query_params = array(
		'filters' => array(
			array(
				'column' => 'object_name',
				'value' => 'access_building_request',
				'operator' => '=',
			),
		),
		'order_by' => array(
			'orderby_column' => 'created_at',
			'order' => 'DESC',
		),
		'page' => $offset,
		'per_page' => $limit,
		'keyword' => $keyword,
		'relationships' => array(
			'applicant' => array(
				'type' => 'user',
				'local_path' => 'owner',
			),
			'place' => array(
				'type' => 'submission',
				'local_path' => 'data->place',
				'array_compare' => true,
			),
		),
	);

	if ( $by_owner ) {
		$query_params['filters'][] = array(
			'column' => 'owner',
			'value' => strval( get_current_user_id() ),
			'operator' => '=',
		);
	}

	if ( $status ) {
		$query_params['filters'][] = array(
			'column' => 'data->status',
			'value' => $status,
			'operator' => '=',
		);
	}

	$submissions = intranet_fafar_api_read(
		args: $query_params
	);

	// null ou []
	if ( empty( $submissions ) )
		return $submissions;

	if ( ! $substitute_value )
		return $submissions;

	/*
	 * Substituir os campos que tem ID de outro objeto,
	 * pelo objeto de mesmo ID
	 */
	$submissions['data'] = array_map( function ($s) {

		if ( is_array( $s['data']['place'] ) && count( $s['data']['place'] ) > 0 ) {
			$s['data']['place'] = intranet_fafar_api_get_submission_by_id( $s['data']['place'][0] );
		}

		if ( isset( $s['owner'] ) && is_numeric( $s['owner'] ) ) {
			$s['owner'] = intranet_fafar_api_get_user_by_id( $s['owner'] );
		}

		return $s;

	}, $submissions['data'] );

	return $submissions;
}

function intranet_fafar_api_create_submission_handler( $request ) {
	$request_data = $request->get_json_params();

	if (
		! isset( $request_data['object_name'] ) ||
		! isset( $request_data['data'] )
	) {

		return new WP_Error( 'rest_api_sad', esc_html__( 'Faltando atributo(s): object_name e/ou data', 'intranet-fafar-api' ), 400 );

	}

	$submission = array(
		'object_name' => $request_data['object_name'],
		'data' => $request_data['data'],
	);

	$submission = intranet_fafar_api_create( $submission );

	if ( isset( $submission['error_msg'] ) ) {
		return new WP_Error( 'rest_api_sad', esc_html__( 'Erro ao criar:' . $submission['error_msg'], 'intranet-fafar-api' ), 400 );
	}

	return rest_ensure_response( $submission );
}


/*
Array
(
	[status] => Aguardando aprova\xc3\xa7\xc3\xa3o
	[technical] => 
	[applicant_name] => asdf
	[applicant_email] => asdf@asdf.com
	[applicant_phone] => (12) 34123-4123
	[desc] => 12341
	[public_prediction] => 123
	[use_musical_instruments] => Array
		(
			[0] => Sim
		)
	[use_fafar_notebook] => Array
		(
			[0] => N\xc3\xa3o
		)
	[use_own_notebook] => Array
		(
			[0] => Sim
		)
	[use_internet_access] => Array
		(
			[0] => N\xc3\xa3o
		)
	[event_date__1] => 2025-01-31
	[start_time__1] => 18:00
	[end_time__1] => 22:00
)

How to transform this:

Array
(
	[status] => Reunião
	[event_date__1] => 2025-01-29
	[event_date__2] => 2025-01-30
	[event_date__3] => 2025-01-31
	...
	[created_at] => 13123113
)

Into this

Array
(
	[status] => Reunião
	...
	[event_date] => 2025-01-29
)
Array
(
	[status] => Reunião
	[event_date] => 2025-01-30
	...
)
Array
(
	[status] => Reunião
	[event_date] => 2025-01-31
	...
)
*/

function intranet_fafar_api_create_auditorium_reservation_handler( $request ) {

	$request_data = $request->get_json_params();

	$data = json_decode( $request_data, true );

	$reservations = intranet_fafar_api_pre_create_auditorium_reservation( $data );

	if ( isset( $reservations['error_msg'] ) ) {
		new WP_Error( 'rest_api_sad', esc_html__( $reservations['error_msg'], 'intranet-fafar-api' ), ( ( $reservations['http_status'] ) ?? 400 ) );
	}

	return rest_ensure_response( $reservations );

}

function intranet_fafar_api_pre_create_auditorium_reservation( $raw_reservation ) {

	$base_data = $raw_reservation;

	$event_dates = [];
	$start_times = [];
	$end_times = [];

	foreach ( $raw_reservation as $key => $value ) {
		if ( preg_match( '/^event_date__\d+$/', $key ) ) {
			$event_dates[] = $value;

			unset( $base_data[ $key ] );
		}

		if ( preg_match( '/^start_time__\d+$/', $key ) ) {
			$start_times[] = $value;

			unset( $base_data[ $key ] );
		}

		if ( preg_match( '/^end_time__\d+$/', $key ) ) {
			$end_times[] = $value;

			unset( $base_data[ $key ] );
		}
	}

	if (
		count( $event_dates ) !== count( $start_times ) ||
		count( $event_dates ) !== count( $end_times ) ||
		count( $start_times ) !== count( $end_times )
	) {

		return array( 'error_msg' => 'Quantidades de datas e horas diferentes!' );

	}

	$reservations = array();

	for ( $i = 0; $i < count( $event_dates ); $i++ ) {
		$newEntry = $base_data;

		$newEntry["event_date"] = $event_dates[ $i ];

		$newEntry["start_time"] = $start_times[ $i ];

		$newEntry["end_time"] = $end_times[ $i ];

		$new_reservation = intranet_fafar_api_create_auditorium_reservation( $newEntry );

		if ( isset( $new_reservation['error_msg'] ) ) {

			return $new_reservation['error_msg'];

		}

		$reservations[] = $new_reservation;
	}

	return $reservations;

}

function intranet_fafar_api_create_auditorium_reservation( $auditorium_reservation ) {

	if ( ! $auditorium_reservation ) {
		return array( 'error_msg' => 'Sem reserva de auditório informada' );
	}

	$submission = array(
		'object_name' => 'auditorium_reservation',
		'data' => $auditorium_reservation,
	);

	return intranet_fafar_api_create( $submission );

}

function intranet_fafar_api_get_auditorium_reservations_handler( $request ) {
	// Get all query parameters
	$query_params = $request->get_query_params();

	$submissions = intranet_fafar_api_get_auditorium_reservations(
		( $query_params['status'] ?? null ),
		( $query_params['order'] ?? null )
	);

	if ( isset( $submissions['error_msg'] ) ) {

		return new WP_Error( 'rest_api_sad', esc_html__( $submissions['error_msg'], 'intranet-fafar-api' ), ( ( $submissions['http_status'] ) ?? 400 ) );

	}

	return rest_ensure_response( $submissions );
}

function intranet_fafar_api_get_auditorium_reservations( $status = null, $order = null ) {

	$auditorium_reservations = array();

	if ( $order ) {

		$order_arr = array( 'orderby_column' => 'created_at', 'orderby_json' => '', 'order' => 'ASC' );

		/* Verifica se o padrão usado para attr é 'json:attr' ou 'column:attr' ou apenas 'attr'
		 * Sendo que apenas com o padrão 'json:attr' o attr é tratado 
		 * como se referindo à um submission->data attr
		 */
		if ( count( explode( ':', $order ) ) > 1 &&
			explode( ':', $order )[0] === 'json' ) {

			$order_arr['orderby_column'] = null;

			$order_arr['orderby_json'] = explode( "-", explode( ':', $order )[1] )[0];

		} else if ( count( explode( ':', $order ) ) > 1 ) {

			$order_arr['orderby_column'] = explode( "-", explode( ':', $order )[1] )[0];

		} else {

			$order_arr['orderby_column'] = $order;

		}

		// Verifica existe desejo explicito pelo order-how DESC
		if ( count( explode( '-', $order ) ) > 1 &&
			strtolower( explode( '-', $order )[1] ) === 'desc' ) {

			$order_arr['order'] = 'DESC';

		}

		$auditorium_reservations = intranet_fafar_api_get_submissions_by_object_name( 'auditorium_reservation', $order_arr );

	} else {

		$auditorium_reservations = intranet_fafar_api_get_submissions_by_object_name( 'auditorium_reservation' );

	}


	// Handle potential errors
	if ( isset( $auditorium_reservations['error_msg'] ) ) {

		return array( 'error_msg' => $auditorium_reservations['error_msg'] );

	}

	// Process reservations: add actions and fetch technical details
	$auditorium_reservations_w_actions = array_map( function ($reservation) {

		$reservation['data']['actions'] = intranet_fafar_api_get_auditorium_reservation_actions( $reservation['data']['status'] );

		$reservation['data']['technical'] = ( is_numeric( $reservation['data']['technical'] ) ?
			intranet_fafar_api_get_user_by_id( $reservation['data']['technical'] ) : '' );

		return $reservation;

	}, $auditorium_reservations );

	// Return all reservations if no status is specified
	if ( ! $status ) {

		return $auditorium_reservations_w_actions;

	}

	// Filter reservations by status
	return array_values( array_filter( $auditorium_reservations_w_actions, fn( $res ) => $res['data']['status'] === $status ) );
}

/*
 * Retorna ações permitidas para cada status da reserva de auditório
 * 
 */
function intranet_fafar_api_get_auditorium_reservation_actions( $status ) {
	$actions = [ 
		0 => 'show_details',
		1 => 'approve',
		2 => 'disapprove',
		3 => 'cancel',
		4 => 'set_technical',
		5 => 'finish',
	];

	$actions_indexes_by_status = [ 
		"Aguardando aprovação" => [ 0, 1, 2, 3 ],
		"Aguardando técnico" => [ 0, 3, 4 ],
		"Aguardando início" => [ 0, 3, 4, 5 ],
		"Desaprovada" => [ 0 ],
		"Cancelada" => [ 0 ],
		"Finalizada" => [ 0 ],
		"Padrão" => [ 0, 1, 3 ],
	];

	return array_map( fn( $index ) => $actions[ $index ], $actions_indexes_by_status[ $status ] ?? $actions_indexes_by_status['Padrão'] );
}

function intranet_fafar_api_get_available_place_for_reservation_handler( $request ) {

	$query_params = $request->get_query_params();

	$submissions = intranet_fafar_api_get_available_place_for_reservation( $query_params );

	if ( isset( $submissions['error_msg'] ) ) {

		return new WP_Error( 'rest_api_sad', esc_html__( $submissions['error_msg'], 'intranet-fafar-api' ), ( ( $submissions['http_status'] ) ?? 400 ) );

	}

	return rest_ensure_response( $submissions );

}

function intranet_fafar_api_get_available_place_for_reservation( $pre_reservation ) {
	// Validate required parameters using single isset check
	if ( ! isset( $pre_reservation['date'], $pre_reservation['start_time'], $pre_reservation['end_time'], $pre_reservation['capacity'] ) ) {
		return array( 'error_msg' => 'Faltando atributo(s): data, início, fim e/ou capacidade' );
	}

	// Use hash lookups for reservable types
	$reservables = array_flip( [ "classroom", "living_room", "computer_lab", "multimedia_room" ] );

	// Get places ordered by capacity (descending)
	$places = intranet_fafar_api_get_submissions_by_object_name( 'place', [ 'orderby_json' => 'capacity', 'order' => 'DESC' ] );

	// Pre-filter valid candidates to minimize API calls
	$required_capacity = $pre_reservation['capacity'];
	$filtered_places = array_filter( $places, function ($place) use ($reservables, $required_capacity) {
		// Check if reservable type exists and is valid
		if ( ! isset( $place['data']['object_sub_type'][0] ) || ! isset( $reservables[ $place['data']['object_sub_type'][0] ] ) ) {
			return false;
		}

		// Check capacity requirement
		return ( $place['data']['capacity'] >= $required_capacity );
	} );

	// Prepare common reservation data once
	$common_data = array(
		'date' => $pre_reservation['date'],
		'start_time' => $pre_reservation['start_time'],
		'end_time' => $pre_reservation['end_time'],
		'frequency' => [ 'once' ],
	);

	$availables = [];
	foreach ( $filtered_places as $place ) {
		// Create reservation payload
		$payload = $common_data;
		$payload['place'] = [ $place['id'] ];

		// Attempt to create reservation
		$response = intranet_fafar_api_create_or_update_reservation( array(
			'object_name' => 'reservation',
			'data' => json_encode( $payload ),
		) );

		// Collect available places without errors
		if ( ! isset( $response['error_msg'] ) ) {
			$availables[] = $place;
		}
	}

	return $availables;
}

/**
 * Search for places based on a search term.
 *
 * @param string $search The search term.
 * @return array Array of matching places.
 */
function intranet_fafar_api_search_place( $search ) {


	// Get all places, ordered by 'number' in ascending order
	$places = intranet_fafar_api_get_submissions_by_object_name( 'place', [ 'orderby_json' => 'number', 'order' => 'ASC' ] );

	// Prepare the search term for comparison
	$search_esc = intranet_fafar_utils_escape_and_clean_to_compare( $search );

	// Filter places that match the search term
	$matches = array_filter( $places, function ($place) use ($search_esc) {
		// Extract and clean place data
		$number_esc = intranet_fafar_utils_escape_and_clean_to_compare( $place['data']['number'] ?? '' );
		$desc_esc = intranet_fafar_utils_escape_and_clean_to_compare( $place['data']['desc'] ?? '' );

		// Check if the search term matches the number or description
		return str_contains( $number_esc, $search_esc ) || str_contains( $desc_esc, $search_esc );
	} );

	// Return the matches as a numerically indexed array
	return array_values( $matches );

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
 *          "class_subject":["1728413739e86f5dc2b8"],
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
	{
	   title: "my recurring STRING event",
	   rrule:
		 "DTSTART:20240201T113000\nRRULE:FREQ=WEEKLY;INTERVAL=1;UNTIL=20241201;BYDAY=MO,FR",
	},
 * 
 * @param $form_data
 * @return FromData | null
*/
function intranet_fafar_api_create_or_update_reservation( $form_data, $submission_id = null ) {

	// error_log( ' -------------------------> ' );
	// error_log( ' CREATE E UPDATE RESERVATION ' );
	// error_log( ' INICIO: ' );
	// error_log( print_r( $form_data, true ) );
	// error_log( print_r( $submission_id, true ) );
	// error_log( ' -------------------------> ' );

	// Verificações iniciais

	if ( empty( $form_data['object_name'] ) || ! is_string( $form_data['object_name'] ) ) {
		return $form_data;
	}

	if ( $form_data['object_name'] !== 'reservation' )
		return $form_data;

	// Professores que não são de colegiados não podem usar reservar
	$public_servant_bond_category = get_user_meta( get_current_user_id(), 'public_servant_bond_category', true );
	$user = wp_get_current_user();
	$role = ! empty( $user->roles ) ? $user->roles[0] : '';
	if (
		strtoupper( $public_servant_bond_category ) === 'DOCENTE' &&
		! in_array( $role, [ 'colegiado_de_graduacao_biomedicina', 'colegiado_de_graduacao_farmacia' ], true )
	) {
		return array( 'error_msg' => 'Não autorizado!' );
	}


	$new_form_data = $form_data;
	if ( is_string( $new_form_data['data'] ) ) {
		$new_form_data['data'] = json_decode( $new_form_data['data'], true );
	}

	if ( ! $new_form_data['data'] )
		return array( 'error_msg' => 'Dados mal formados.' );

	// Verificar se dados necessários foram informados
	if (
		empty( $new_form_data['data']['date'] ) ||
		empty( $new_form_data['data']['start_time'] ) ||
		empty( $new_form_data['data']['end_time'] ) ||
		empty( $new_form_data['data']['frequency'] ) ||
		empty( $new_form_data['data']['place'] )
	) {
		return array( 'error_msg' => 'Data, tempo, frequência ou lugar não informado!' );
	}

	if (
		! is_string( $new_form_data['data']['date'] ) ||
		! is_string( $new_form_data['data']['start_time'] ) ||
		! is_string( $new_form_data['data']['end_time'] ) ||
		! is_array( $new_form_data['data']['frequency'] )
	) {
		return array( 'error_msg' => 'Data, tempo ou frequência do tipo errado!' );
	}

	// Validando formato de data
	$date = DateTime::createFromFormat( 'Y-m-d', $new_form_data['data']['date'] );
	if ( ! $date || $date->format( 'Y-m-d' ) !== $new_form_data['data']['date'] ) {
		return array( 'error_msg' => 'Data de início inválida!' );
	}

	// Verificar se *hora* de fim é posterior ao de início
	$s = new DateTime( $new_form_data['data']['start_time'] );
	$e = new DateTime( $new_form_data['data']['end_time'] );
	if ( $s >= $e )
		return array( 'error_msg' => 'Horário de início não pode ser depois de fim!' );

	/* 
	 * Verificando se sala/lugar existe
	 */
	/*
	 * No assistente de reservas, o ID do lugar é passado por parâmetro em URL.
	 * Isso causa uma treta.... E ai tem que fazer essas coisas: 
	 */
	if ( is_string( $new_form_data['data']['place'] ) ) {
		$decoded = json_decode( stripslashes( $new_form_data['data']['place'] ), true );
		if ( json_last_error() === JSON_ERROR_NONE ) {
			$new_form_data['data']['place'] = $decoded;
		} else {
			return array( 'error_msg' => 'Erro ao processar local da reserva!' );
		}
	}

	if ( empty( $new_form_data['data']['place'][0] ) )
		return array( 'error_msg' => 'Local com dados incorretos' );

	$place = intranet_fafar_api_get_submission_by_id( $new_form_data['data']['place'][0], false );

	if ( empty( $place ) )
		return array( 'error_msg' => 'Lugar desconhecido' );

	/* 
	 * Verificando se o usuário tem permissão de reserva nessa sala 
	 */
	if ( ! intranet_fafar_api_check_write_permission( $place ) ) {
		return array( 'error_msg' => 'Não autorizado!' );
	}

	// Verircar se *data* de fim é posterior ao de início, se houver data de fim
	if ( $new_form_data['data']['frequency'][0] !== 'once' ) {

		if ( empty( $new_form_data['data']['end_date'] ) ) {
			return array( 'error_msg' => 'Data de término não informada!' );
		}

		if ( ! is_string( $new_form_data['data']['end_date'] ) ) {
			return array( 'error_msg' => 'Data de término informada com tipo errado!' );
		}

		// Validando formato de data
		$start_date = DateTime::createFromFormat( 'Y-m-d', $new_form_data['data']['date'] );
		if ( ! $start_date || $start_date->format( 'Y-m-d' ) !== $new_form_data['data']['date'] ) {
			return array( 'error_msg' => 'Data de início inválida!' );
		}

		$end_date = DateTime::createFromFormat( 'Y-m-d', $new_form_data['data']['end_date'] );
		if ( ! $end_date || $end_date->format( 'Y-m-d' ) !== $new_form_data['data']['end_date'] ) {
			return array( 'error_msg' => 'Data de término inválida!' );
		}

		$s = DateTime::createFromFormat( 'H:i', $new_form_data['data']['start_time'] );
		$e = DateTime::createFromFormat( 'H:i', $new_form_data['data']['end_time'] );
		if ( ! $s || ! $e || $s >= $e ) {
			return array( 'error_msg' => 'Horário de início não pode ser depois de fim ou inválido!' );
		}
	}

	$title = 'Reserva ' . time();

	if ( ! empty( $new_form_data['data']['desc'] ) ) {
		$title = $new_form_data['data']['desc'];
	} else if ( isset( $new_form_data['data']['class_subject'][0] ) ) {
		$class_subject = intranet_fafar_api_get_submission_by_id( $new_form_data['data']['class_subject'][0] );

		if ( ! empty( $class_subject ) ) {
			$title = $class_subject['data']['code'] . ' (' . $class_subject['data']['group'] . ')';
		}
	}

	// Setando a prop 'title'
	$new_form_data['data']['title'] = $title;

	if ( $new_form_data['data']['frequency'][0] === 'weekly' ) {

		// Validando weekdays
		if ( empty( $new_form_data['data']['weekdays'][0] ) ) {
			return array( 'error_msg' => 'Dia(s) de semana não informado(s)!' );
		}

		if ( ! is_array( $new_form_data['data']['weekdays'] ) ) {
			return array( 'error_msg' => 'Dia(s) de semana do tipo errado!' );
		}

		foreach ( $new_form_data['data']['weekdays'] as $day ) {
			if ( ! is_numeric( $day ) || $day < 1 || $day > 7 ) {
				return array( 'error_msg' => 'Dia da semana inválido!' );
			}
		}

		// Gerando a prop 'dt_dstart' (Data início + Hora início) só com números
		$date = new DateTime( $new_form_data['data']['date'] );

		$time = DateTime::createFromFormat( 'H:i', $new_form_data['data']['start_time'] );

		$dt_start = $date->format( 'Ymd' ) . 'T' . $time->format( 'His' );

		// Gerando a prop 'byday' com o array retornado pelos checkboxes do CF7
		$byday = [];
		$weekday_map = [ 1 => 'MO', 2 => 'TU', 3 => 'WE', 4 => 'TH', 5 => 'FR', 6 => 'SA', 7 => 'SU' ];
		foreach ( $new_form_data['data']['weekdays'] as $day ) {
			if ( isset( $weekday_map[ $day ] ) ) {
				$byday[] = $weekday_map[ $day ];
			}
		}

		// Gerando a prop 'until' só com números
		$date = new DateTime( $new_form_data['data']['end_date'] );

		/*
		 * '+1 day' para cobrir o dia de encerramento, todo.
		 * Se informado '24102025T000000' cobre até o primeiro segundo de 24/10/2025
		 * O que eu quero é cobrir 24/10/2025 todo, 
		 * então: '25102025T000000'
		 */
		$date->modify( '+1 day' );
		$until = $date->format( 'Ymd' ) . 'T000000';

		/*
		 * Gerando RRULE string com a: 
		 * 'DTSTART:20240201T113000\nRRULE:FREQ=WEEKLY;INTERVAL=1;UNTIL=20241201;BYDAY=MO,FR'
		 */
		$new_rrule = 'DTSTART:' . $dt_start . '\nRRULE:FREQ=WEEKLY;INTERVAL=1;UNTIL=' . $until . ';BYDAY=' . implode( ',', $byday );

		// Gerando a prop 'duration'
		$start = DateTime::createFromFormat( 'H:i', $new_form_data['data']['start_time'] );
		$end = DateTime::createFromFormat( 'H:i', $new_form_data['data']['end_time'] );

		// Calculate the difference between the two times
		$interval = $start->diff( $end );

		// 'duration' é uma prop independente de 'rrule'
		$new_duration = $interval->format( '%H:%I' );

	} else {

		// Gerando a prop 'dt_dstart' (Data início + Hora início) só com números
		$date = new DateTime( $new_form_data['data']['date'] );
		$time = DateTime::createFromFormat( 'H:i', $new_form_data['data']['start_time'] );
		$dt_start = $date->format( 'Ymd' ) . 'T' . $time->format( 'His' );

		/*
		 * Gerando RRULE string com a: 
		 * 'DTSTART:20241107T113000\nRRULE:FREQ=DAILY;COUNT=1'
		 */
		$new_rrule = 'DTSTART:' . $dt_start . '\nRRULE:FREQ=DAILY;COUNT=1';

		// Gerando a prop 'duration'
		$start = DateTime::createFromFormat( 'H:i', $new_form_data['data']['start_time'] );
		$end = DateTime::createFromFormat( 'H:i', $new_form_data['data']['end_time'] );

		// Calculate the difference between the two times
		$interval = $start->diff( $end );

		// 'duration' é uma prop independente de 'rrule'
		$new_duration = $interval->format( '%H:%I' );

	}


	$skip_check_overlap = false;
	if ( ! empty( $new_form_data['data']['rrule'] ) && $new_form_data['data']['rrule'] == $new_rrule ) {

		$skip_check_overlap = true;

	}

	// É, pois é.... Medo....
	$new_form_data['data']['rrule'] = $new_rrule;

	$new_form_data['data']['duration'] = $new_duration;

	/*
	 * Sim, eu sei... Isso não é necessário. 
	 * Mas é medo de colocar mais de uma forma de sair com sucesso dessa função...
	 * Vou mudar re-escrever isso aqui quando o sistema de reservas já estiver bem testado.
	 * Na verdade, essa função toda....
	 */
	if ( ! $skip_check_overlap ) {

		$existing_reservations = intranet_fafar_api_get_reservations_by_place( $new_form_data['data']['place'][0] );

		/* 
		 * Gerar as datas dos reservas existentes
		 * Array ( [0] => 2024-02-05 00:00:00 [1] => 2024-02-02 00:00:00 [2] => ...
		 */
		$new_reservation_timestamps = intranet_fafar_rrule_get_all_occurrences( $new_form_data['data']['rrule'] );

		if ( empty( $new_reservation_timestamps ) ) {
			return array( 'error_msg' => 'RRULE inválido ou sem ocorrências. Confira os dados enviados.' );
		}

		// Aqui temos timestamps das reservas à ser registradas
		foreach ( $new_reservation_timestamps as $new_reservation_timestamp ) {

			foreach ( $existing_reservations as $existing_reservation ) {

				/*
				 * Essa comparação é para quando estamos fazendo atualização de uma reserva.
				 * Nesse caso, nenhum 'timestamp' possível dessa reserva deve ser levado em consideração, 
				 * pois ela será atualizada: suas antigas timestamps não contam mais 
				 */
				if ( $submission_id && $existing_reservation['id'] === $submission_id )
					continue;

				// Aqui estamos gerando as timestamps de cada evento registrado
				$existing_reservation_timestamps = intranet_fafar_rrule_get_all_occurrences( $existing_reservation['data']['rrule'] );

				if ( empty( $existing_reservation_timestamps ) ) {
					continue; // Pula RRULEs inválidas
				}

				foreach ( $existing_reservation_timestamps as $existing_reservation_timestamp ) {

					$existing = intranet_fafar_api_get_event_start_and_end( $existing_reservation_timestamp, $existing_reservation['data']['duration'] );
					$new = intranet_fafar_api_get_event_start_and_end( $new_reservation_timestamp, $new_form_data['data']['duration'] );

					if ( intranet_fafar_api_does_reservations_overlaps( $existing, $new ) )
						return array( 'error_msg' => 'Sala indisponível nesse horário!' );

				}

			}

		}
	}


	// Se tudo deu certo, então devolve o objeto para ser inserido pelo plugin 'fafar-cf7crud'
	$json_data = json_encode( $new_form_data['data'] );
	if ( $json_data === false ) {
		return array( 'error_msg' => 'Erro ao codificar dados finais!' );
	}

	$form_data['data'] = $json_data;

	// error_log( ' -------------------------> ' );
	// error_log( ' CREATE E UPDATE RESERVATION ' );
	// error_log( ' FIM: ' );
	// error_log( print_r( $form_data, true ) );
	// error_log( print_r( $submission_id, true ) );
	// error_log( ' -------------------------> ' );

	return $form_data;
}

/**
 * 
 * @param string $timestamp    Normal DateTime str timestamp
 * @param string $duration_str DateTime->format('%H:%I') string format
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
		( $reservation_a_length_center["length"] + $reservation_b_length_center["length"] ) / 2;

	if ( $distance_between_centers >= $reservations_length_sums )
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

	$hours = (int) explode( ':', $time )[0];
	$minutes = (int) explode( ':', $time )[1];

	$d->setTime( $hours, $minutes );

	return $d->getTimestamp();

}

function intranet_fafar_api_get_timestamp( $date_string ) {

	$d = date_create( $date_string, new DateTimeZone( 'America/Sao_Paulo' ) );

	return (int) $d->getTimestamp();

}

function intranet_fafar_api_get_weekday_by_timestamp( $timestamp ) {

	$d = date_create( "now", new DateTimeZone( 'America/Sao_Paulo' ) );
	$d->setTimestamp( (int) $timestamp );

	return (int) $d->format( "w" );

}

function intranet_fafar_api_get_laboratory_team_by_owner_id_handler( $request ) {
	if ( empty( $request['owner_id'] ) ) {
		return new WP_Error(
			'rest_api_sad',
			esc_html__( 'ID não fornecido', 'intranet-fafar-api' ),
			400
		);
	}

	$owner_id = (string) $request['owner_id'];
	$submission = intranet_fafar_api_get_laboratory_team_by_owner_id( $owner_id );

	if ( ! $submission ) {
		return new WP_Error(
			'rest_api_sad',
			esc_html__( 'Nenhum usuário com ID ' . $owner_id . ' inválido', 'intranet-fafar-api' ),
			404
		);
	}

	return rest_ensure_response( $submission );
}

function intranet_fafar_api_get_laboratory_team_by_owner_id( $owner_id = null ) {
	if ( ! $owner_id )
		return [];

	$category = get_the_author_meta( 'public_servant_bond_category', $owner_id );

	if ( strtolower( $category ) !== 'docente' )
		return [];

	$submission = intranet_fafar_api_read(
		args: array(
			'filters' => array(
				array(
					'column' => 'object_name',
					'value' => 'laboratory_team',
					'operator' => '=',
				),
				array(
					'column' => 'owner',
					'value' => $owner_id,
					'operator' => '=',
				)
			),
			'single' => true,
		)
	);

	// Criamos uma equipe de laboratório para o prof, se já não tiver
	if ( ! $submission ) {

		$user = get_userdata( $owner_id );

		if ( ! $user || empty( $user->roles[0] ) )
			return null;

		$user_role = $user->roles[0];

		$submission = array(
			'object_name' => 'laboratory_team',
			'owner' => $owner_id,
			'group_owner' => $user_role,
			'permissions' => '744',
			'data' => array(
				'collaborators' => [],
			),
		);

		$result = intranet_fafar_api_create( $submission );

		if ( isset( $result['error_msg'] ) ) {
			return null;
		}

		$submission['id'] = $result['id'];
	}

	/*
	 * Substituir os campos que tem ID de outro objeto,
	 * pelo objeto de mesmo ID
	 */
	$submission['data']['collaborators'] = array_map( function ($collaborator_id) {
		$user = intranet_fafar_api_get_user_by_id( $collaborator_id );

		$collaborator = array(
			'ID' => $user['data']->ID,
			'display_name' => $user['data']->display_name,
			'user_login' => $user['data']->user_login,
		);

		return $collaborator;
	}, $submission['data']['collaborators'] );

	return $submission;
}

function intranet_fafar_api_get_possible_collaborators_laboratory_team_handler( $request ) {
	if ( empty( $request['id'] ) ) {
		return new WP_Error(
			'rest_api_sad',
			esc_html__( 'ID não fornecido', 'intranet-fafar-api' ),
			400
		);
	}

	$id = (string) $request['id'];
	$submissions = intranet_fafar_api_get_possible_collaborators_laboratory_team( $id );

	if ( ! $submissions ) {
		return new WP_Error(
			'rest_api_sad',
			esc_html__( 'Nenhum usuário com ID ' . $id . ' inválido', 'intranet-fafar-api' ),
			404
		);
	}

	return rest_ensure_response( $submissions );
}

function intranet_fafar_api_get_possible_collaborators_laboratory_team( $team_id = null ) {
	if ( ! $team_id )
		return null;

	$submission = intranet_fafar_api_read(
		args: array(
			'filters' => array(
				array(
					'column' => 'object_name',
					'value' => 'laboratory_team',
					'operator' => '=',
				),
				array(
					'column' => 'id',
					'value' => $team_id,
					'operator' => '=',
				)
			),
			'single' => true,
		)
	);

	if ( ! $submission )
		return [];

	$new_collaborators = intranet_fafar_api_get_users(
		array(
			'status' => 'ATIVO',
			'category' => 'TAE',
			'role' => $submission['group_owner'],
			'exclude' => $submission['data']['collaborators'],
		),
		false // Old return = false
	);

	return $new_collaborators;
}

function intranet_fafar_api_add_collaborator_on_lab_team_handler( $request ) {
	if ( empty( $request['id'] ) ) {
		return new WP_Error(
			'rest_api_sad',
			esc_html__( 'ID não fornecido', 'intranet-fafar-api' ),
			400
		);
	}

	if ( empty( $request['collaborator_id'] ) ) {
		return new WP_Error(
			'rest_api_sad',
			esc_html__( 'Colaborador não fornecido', 'intranet-fafar-api' ),
			400
		);
	}

	$id = (string) $request['id'];
	$collaborator_id = (string) $request['collaborator_id'];
	$submission = intranet_fafar_api_update_laboratory_team( $id, 'add', $collaborator_id );

	if ( ! $submission ) {
		return new WP_Error(
			'rest_api_sad',
			esc_html__( 'Nenhum usuário com ID ' . $id . ' inválido', 'intranet-fafar-api' ),
			404
		);
	}

	intranet_fafar_mail_on_update_laboratory_team( $submission['submission'], 'add', $collaborator_id );

	return rest_ensure_response( $submission );
}

function intranet_fafar_api_remove_collaborator_on_lab_team_handler( $request ) {
	if ( empty( $request['id'] ) ) {
		return new WP_Error(
			'rest_api_sad',
			esc_html__( 'ID não fornecido', 'intranet-fafar-api' ),
			400
		);
	}

	if ( empty( $request['collaborator_id'] ) ) {
		return new WP_Error(
			'rest_api_sad',
			esc_html__( 'Colaborador não fornecido', 'intranet-fafar-api' ),
			400
		);
	}

	$id = (string) $request['id'];
	$collaborator_id = (string) $request['collaborator_id'];
	$submission = intranet_fafar_api_update_laboratory_team( $id, 'remove', $collaborator_id );

	if ( ! $submission ) {
		return new WP_Error(
			'rest_api_sad',
			esc_html__( 'Nenhum usuário com ID ' . $id . ' inválido', 'intranet-fafar-api' ),
			404
		);
	}

	intranet_fafar_mail_on_update_laboratory_team( $submission['submission'], 'remove', $collaborator_id );

	return rest_ensure_response( $submission );
}

function intranet_fafar_api_update_laboratory_team( $id, $action, $collaborator_id ) {
	$submission = intranet_fafar_api_read(
		args: array(
			'filters' => array(
				array(
					'column' => 'id',
					'value' => $id,
					'operator' => '=',
				),
			),
			'single' => true,
		)
	);

	if ( ! $submission ) {
		return null;
	}

	if ( $submission['owner'] != get_current_user_id() )
		return null;

	if ( $action === 'add' ) {
		$submission['data']['collaborators'][] = $collaborator_id;
		$submission = intranet_fafar_api_update( $id, $submission );
		if ( isset( $submission['error_msg'] ) )
			return null;
	}

	if ( $action === 'remove' ) {
		$submission['data']['collaborators'] = array_filter(
			$submission['data']['collaborators'],
			function ($col_id) use ($collaborator_id) {
				return $col_id !== $collaborator_id;
			}
		);
		$submission['data']['collaborators'] = array_values( $submission['data']['collaborators'] );
		$submission = intranet_fafar_api_update( $id, $submission );
		if ( isset( $submission['error_msg'] ) )
			return null;
	}

	return $submission;
}

function intranet_fafar_api_get_submission_by_id_handler( $request ) {
	if ( empty( $request['id'] ) ) {
		return new WP_Error(
			'rest_api_sad',
			esc_html__( 'ID não fornecido', 'intranet-fafar-api' ),
			400
		);
	}

	$id = (string) $request['id'];
	$submission = intranet_fafar_api_get_submission_by_id( $id );

	if ( ! $submission ) {
		return new WP_Error(
			'rest_api_sad',
			esc_html__( 'Nenhum objeto com ID ' . $id, 'intranet-fafar-api' ),
			404
		);
	}

	return rest_ensure_response( $submission );
}

function intranet_fafar_api_get_submission_by_id(
	$id,
	$substitute_value = true,
	$check_permissions = true,
	$check_is_active = true,
) {
	$submission = intranet_fafar_api_read(
		args: array(
			'filters' => array(
				array(
					'column' => 'id',
					'value' => $id,
					'operator' => '=',
				),
			),
			'check_permissions' => $check_permissions,
			'check_is_active' => $check_is_active,
			'single' => true,
		)
	);

	if ( ! $submission || ! $substitute_value )
		return $submission;

	if ( isset( $submission['owner'] ) && is_numeric( $submission['owner'] ) ) {
		$submission['owner'] = intranet_fafar_api_get_user_by_id( $submission['owner'] );
	}

	if ( isset( $submission['data']['place'] ) &&
		is_array( $submission['data']['place'] ) &&
		count( $submission['data']['place'] ) > 0 ) {

		$submission['data']['place'] = intranet_fafar_api_get_submission_by_id( $submission['data']['place'][0] );
	}

	return $submission;
}

function intranet_fafar_api_get_submissions_by_object_name_handler( $request ) {
	if ( ! $request->get_param( 'object' ) ) {
		return new WP_Error(
			'rest_api_sad',
			esc_html__( 'Nenhum objeto informado', 'intranet-fafar-api' ),
			400,
		);
	}

	$object_name = (string) $request->get_param( 'object' );

	// Get pagination parameters from the request
	$offset = $request->get_param( 'offset' ) ? intval( $request->get_param( 'offset' ) ) : 1;
	$limit = $request->get_param( 'limit' ) ? intval( $request->get_param( 'limit' ) ) : -1;
	$keyword = $request->get_param( 'keyword' ) ? sanitize_text_field( $request->get_param( 'keyword' ) ) : '';

	$submissions = intranet_fafar_api_get_submissions_by_object_name(
		$object_name,
		array(
			'orderby_json' => 'number',
			'order' => 'ASC',
		),
		array(
			'keyword' => $keyword,
			'offset' => $offset,
			'limit' => $limit,
		),
		false
	);

	if ( $submissions === false ) {
		return new WP_Error(
			'rest_api_sad',
			esc_html__( ( ! empty( $submissions['error_msg'] ) ? $submissions['error_msg'] : 'Erro no processamento' ), 'intranet-fafar-api' ),
			( ! empty( $submissions['http_status'] ) ? $submissions['http_status'] : 500 ),
		);
	}

	if ( empty( $submissions ) ) {
		return rest_ensure_response(
			array(
				'count' => 0,
				'next' => null,
				'previous' => null,
				'results' => [],
			)
		);
	}

	return rest_ensure_response(
		array(
			'count' => $submissions['pagination']['total_items'],
			'next' => null,
			'previous' => null,
			'results' => $submissions['data'],
		)
	);
}

/*
 * @param string $object_name 'place'
 * @param array $order_by ( 'orderby_column' => '', 'orderby_json' => '', 'order' => 'ASC' | 'DESC', 'inet_aton' => '1' )
 * @return array $submissions 
 */
function intranet_fafar_api_get_submissions_by_object_name(
	$object_name,
	$order_by = array(),
	$args = array(),
	$old = true
) {
	// Default parameters
	$defaults = array(
		'order_by' => array(),
		'check_permissions' => true,
		'check_is_active' => true,
		'offset' => 1,
		'limit' => -1,
		'keyword' => '',
		'substitute_value' => true,
		'relationships' => array(),
	);

	// Merge user-provided arguments with defaults
	$args = wp_parse_args( $args, $defaults );

	$submissions = intranet_fafar_api_read(
		args: array(
			'filters' => array(
				array(
					'column' => 'object_name',
					'value' => $object_name,
					'operator' => '=',
				),
			),
			'order_by' => $order_by,
			'check_permissions' => $args['check_permissions'],
			'check_is_active' => $args['check_is_active'],
			'page' => $args['offset'],
			'per_page' => $args['limit'],
			'keyword' => $args['keyword'],
		)
	);

	if ( empty( $submissions ) ) {
		if ( $old )
			return array( 'error_msg' => 'Erro ao processar' );

		return $submissions;
	}

	if ( ! $args['substitute_value'] ) {
		if ( $old )
			$submissions['data'];

		return $submissions;
	}

	/*
	 * Substituir os campos que tem ID de outro objeto,
	 * pelo objeto de mesmo ID
	 */
	$submissions['data'] = array_map( function ($s) {
		if ( isset( $s['owner'] ) && is_numeric( $s['owner'] ) ) {
			$s['owner'] = intranet_fafar_api_get_user_by_id( $s['owner'] );
		}

		return $s;
	}, $submissions['data'] );

	return $old ? $submissions['data'] : $submissions;
}


function intranet_fafar_api_get_user_by_id_handler( $request ) {
	if ( empty( $request['id'] ) ) {
		return new WP_Error(
			'rest_api_sad',
			esc_html__( 'ID não fornecido', 'intranet-fafar-api' ),
			400
		);
	}

	$id = (string) $request['id'];
	$submission = intranet_fafar_api_get_user_by_id( $id );

	if ( ! $submission ) {
		return new WP_Error(
			'rest_api_sad',
			esc_html__( 'Nenhum usuário com ID ' . $id, 'intranet-fafar-api' ),
			404
		);
	}

	return rest_ensure_response( $submission );
}

function intranet_fafar_api_get_user_by_id( $id ) {
	if ( ! isset( $id ) || ! $id ) {
		return array( 'error_msg' => 'Nenhum ID informado', 'http_status' => 400 );
	}

	$user = (array) get_userdata( intval( $id ) );

	if ( ! $user ) {
		return array( 'error_msg' => 'Nenhum usuário encontrado', 'http_status' => 400 );
	}

	return $user;
}

// Handle the GET request
function intranet_fafar_api_get_users_handler( $request ) {
	// Get pagination parameters from the request
	$offset = $request->get_param( 'offset' ) ? intval( $request->get_param( 'offset' ) ) : 0;
	$limit = $request->get_param( 'limit' ) ? intval( $request->get_param( 'limit' ) ) : -1;

	$keyword = $request->get_param( 'keyword' ) ? $request->get_param( 'keyword' ) : '';
	$status = $request->get_param( 'status' ) ? $request->get_param( 'status' ) : '';
	$category = $request->get_param( 'category' ) ? $request->get_param( 'category' ) : '';
	$role = $request->get_param( 'role' ) ? $request->get_param( 'role' ) : '';
	$place = $request->get_param( 'place' ) ? $request->get_param( 'place' ) : '';

	return rest_ensure_response(
		intranet_fafar_api_get_users(
			array(
				'offset' => $offset,
				'limit' => $limit,
				'keyword' => $keyword,
				'status' => $status,
				'category' => $category,
				'role' => $role,
				'place' => $place,
			),
			false // Old return = false
		)
	);
}

function intranet_fafar_api_get_users( $args = array(), $old = true ) {
	// Default parameters
	$defaults = array(
		'status' => 'ATIVO',
		'category' => null,
		'role' => '',
		'place' => null,
		'check_permissions' => true,
		'check_is_active' => true,
		'order_by' => 'display_name',
		'order' => 'ASC',
		'offset' => 1,
		'limit' => -1,
		'keyword' => '',
		'substitute_value' => true,
		'exclude' => array( 1 ),
	);

	// Merge user-provided arguments with defaults
	$args = wp_parse_args( $args, $defaults );

	$args['exclude'] = array_map( fn( $id ) => is_numeric( $id ) ? (int) $id : 0, $args['exclude'] );

	$meta_query = array();
	if ( $args['status'] ) {
		$meta_query[] = array(
			'key' => 'bond_status',
			'value' => $args['status'],
			'compare' => '=',
		);
	}

	if ( $args['category'] ) {
		$meta_query[] = array(
			'key' => 'public_servant_bond_category',
			'value' => $args['category'],
			'compare' => '=',
		);
	}

	if ( $args['place'] ) {
		$match_places = intranet_fafar_api_search_place( $args['place'] );
		$places_ids = array_map( fn( $place ) => $place['id'], $match_places );
		$meta_query[] = array(
			'key' => 'workplace_place',
			'value' => $places_ids,
			'compare' => 'IN',
		);
	}

	$meta_query_count = count( $meta_query );

	if ( $meta_query_count >= 2 )
		$meta_query['relation'] = 'AND';
	else if ( $meta_query_count == 1 ) {
		$meta_query['relation'] = 'OR';
		$meta_query[] = array();
	}

	// Query users with pagination
	$user_query = new WP_User_Query( array(
		'number' => $args['limit'],
		'offset' => $args['offset'],
		'search' => '*' . $args['keyword'] . '*', // Search for users with "john" in their username, email, or display name
		'role' => $args['role'],
		'meta_query' => $meta_query,
		'orderby' => $args['order_by'],
		'order' => $args['order'],
		'exclude' => $args['exclude'], // Exclude Administrator(ID:1)
	) );

	$users = $user_query->get_results();

	// Add pagination headers
	$total_users = $user_query->get_total();

	// Prepare the response
	$results = array_map( function ($user) {
		// Lidando com permissões
		$current_user_role = ( isset( wp_get_current_user()->roles[0] ) ? wp_get_current_user()->roles[0] : '' );

		$can_view_personal_info = false;

		if (
			$current_user_role === 'administrator' ||
			$current_user_role === 'pessoal' ||
			$current_user_role === 'tecnologia_da_informacao_e_suporte' ||
			$user->ID === get_current_user_id()
		)
			$can_view_personal_info = true;


		$user_data = (
			$can_view_personal_info ?
			( (array) $user->data ) :
			array(
				'ID' => $user->ID,
				'display_name' => $user->data->display_name,
				'user_email' => $user->data->user_email,
				'user_login' => $user->data->user_login,
			)
		);

		// Informações abertas
		$user_data['avatar_url'] = get_avatar_url( $user->ID );

		$user_data['public_servant_bond_type'] = esc_attr( get_the_author_meta( 'public_servant_bond_type', $user->ID ) );
		$user_data['public_servant_bond_category'] = esc_attr( get_the_author_meta( 'public_servant_bond_category', $user->ID ) );
		$user_data['public_servant_bond_position'] = esc_attr( get_the_author_meta( 'public_servant_bond_position', $user->ID ) );
		$user_data['public_servant_bond_class'] = esc_attr( get_the_author_meta( 'public_servant_bond_class', $user->ID ) );
		$user_data['public_servant_bond_level'] = esc_attr( get_the_author_meta( 'public_servant_bond_level', $user->ID ) );
		$user_data['role'] = intranet_fafar_api_get_roles( esc_attr( isset( $user->roles[0] ) ? $user->roles[0] : '' ) );
		$user_data['bond_status'] = esc_attr( get_the_author_meta( 'bond_status', $user->ID ) );

		$user_data['workplace_place'] = intranet_fafar_api_get_submission_by_id( esc_attr( get_the_author_meta( 'workplace_place', $user->ID ) ) );
		$user_data['workplace_extension'] = esc_attr( get_the_author_meta( 'workplace_extension', $user->ID ) );

		$user_data['personal_phone'] = esc_attr( get_the_author_meta( 'personal_phone', $user->ID ) );

		$user_data['prevent_read'] = false;
		$user_data['prevent_write'] = true;
		$user_data['prevent_exec'] = true;

		if ( ! $can_view_personal_info )
			return $user_data;

		$user_data['personal_birthday'] = esc_attr( get_the_author_meta( 'personal_birthday', $user->ID ) );
		$user_data['personal_cpf'] = esc_attr( get_the_author_meta( 'personal_cpf', $user->ID ) );
		$user_data['personal_ufmg_registration'] = esc_attr( get_the_author_meta( 'personal_ufmg_registration', $user->ID ) );
		$user_data['personal_siape'] = esc_attr( get_the_author_meta( 'personal_siape', $user->ID ) );

		$user_data['address_cep_code'] = esc_attr( get_the_author_meta( 'address_cep_code', $user->ID ) );
		$user_data['address_uf'] = esc_attr( get_the_author_meta( 'address_uf', $user->ID ) );
		$user_data['address_city'] = esc_attr( get_the_author_meta( 'address_city', $user->ID ) );
		$user_data['address_neighborhood'] = esc_attr( get_the_author_meta( 'address_neighborhood', $user->ID ) );
		$user_data['address_public_place'] = esc_attr( get_the_author_meta( 'address_public_place', $user->ID ) );
		$user_data['address_number'] = esc_attr( get_the_author_meta( 'address_number', $user->ID ) );
		$user_data['address_complement'] = esc_attr( get_the_author_meta( 'address_complement', $user->ID ) );

		$user_data['prevent_read'] = false;
		$user_data['prevent_write'] = false;
		$user_data['prevent_exec'] = false;

		return $user_data;

	}, $users );

	if ( $old )
		return $results;

	return array(
		'count' => $total_users,
		'next' => null,
		'previous' => null,
		'results' => $results,
	);
}

function intranet_fafar_api_get_users_by_sector_slug_handler( $request ) {
	$sector = (string) $request['sector'];

	$submissions = intranet_fafar_api_get_users( array( 'role' => $sector ) );

	if ( ! $submissions ) {
		return new WP_Error( 'rest_api_sad', esc_html__( 'Nenhum usuário encontrado', 'intranet-fafar-api' ), ( ( $submissions['http_status'] ) ?? 400 ) );
	}

	return rest_ensure_response( $submissions );
}

function intranet_fafar_api_get_roles( $slug = null ) {

	global $wp_roles;

	// Ensure the $wp_roles object is loaded.
	if ( empty( $wp_roles ) ) {
		$wp_roles = new WP_Roles();
	}

	if ( $slug ) {
		$role = $wp_roles->roles[ $slug ];

		$role['slug'] = $slug;

		return $role;
	}

	// Return an array of all roles.
	return $wp_roles->roles;

}

function intranet_fafar_api_get_reservation_by_id_handler( $request ) {
	$id = (string) $request['id'];

	$reservation = intranet_fafar_api_get_reservation_by_id( $id );

	if ( isset( $reservation['error_msg'] ) ) {
		return new WP_Error( 'rest_api_sad', esc_html__( $reservation['error_msg'], 'intranet-fafar-api' ), ( ( $reservation['http_status'] ) ?? 400 ) );
	}

	$role_slug = $reservation['group_owner'];
	if ( isset( $role_slug ) ) {
		$reservation['group_owner'] = isset( wp_roles()->roles[ $role_slug ] ) ? wp_roles()->roles[ $role_slug ]['name'] : '';
	}

	return rest_ensure_response( $reservation );
}

function intranet_fafar_api_get_equipaments_handler( $request ) {
	// Get pagination parameters from the request
	$offset = $request->get_param( 'offset' ) ? intval( $request->get_param( 'offset' ) ) : 1;
	$limit = $request->get_param( 'limit' ) ? intval( $request->get_param( 'limit' ) ) : -1;
	$keyword = $request->get_param( 'keyword' ) ? sanitize_text_field( $request->get_param( 'keyword' ) ) : '';

	$submissions = intranet_fafar_api_get_equipaments( $keyword, $offset, $limit );

	if ( $submissions === false ) {
		return new WP_Error(
			'rest_api_sad',
			esc_html__( ( ! empty( $submissions['error_msg'] ) ? $submissions['error_msg'] : 'Erro no processamento' ), 'intranet-fafar-api' ),
			( ! empty( $submissions['http_status'] ) ? $submissions['http_status'] : 500 ),
		);
	}

	if ( empty( $submissions ) ) {
		return rest_ensure_response(
			array(
				'count' => 0,
				'next' => null,
				'previous' => null,
				'results' => [],
			)
		);
	}

	return rest_ensure_response(
		array(
			'count' => $submissions['pagination']['total_items'],
			'next' => null,
			'previous' => null,
			'results' => $submissions['data'],
		)
	);
}

function intranet_fafar_api_get_equipaments( $keyword = '', $offset = 1, $limit = -1 ) {
	$submissions = intranet_fafar_api_read(
		args: array(
			'filters' => array(
				array(
					'column' => 'object_name',
					'value' => 'equipament',
					'operator' => '=',
				),
			),
			'order_by' => array(
				'orderby_column' => 'created_at',
				'order' => 'DESC',
			),
			'page' => $offset,
			'per_page' => $limit,
			'keyword' => $keyword,
			'relationships' => [ 
				'applicant' => [ 
					'type' => 'user',
					'local_path' => 'data->applicant',
					'array_compare' => true,
					'meta_fields' => [ 'workplace_extension' ],
				],
				'place' => [ 
					'type' => 'submission',
					'local_path' => 'data->place',
					'array_compare' => true,
				],
				'ip' => [ 
					'type' => 'submission',
					'local_path' => 'data->ip',
					'array_compare' => true,
				],
			],
		)
	);

	// null ou []
	if ( empty( $submissions ) )
		return $submissions;

	return $submissions;
}

function intranet_fafar_api_get_equipament_by_id( $id ) {

	$equipmanet = intranet_fafar_api_get_submission_by_id( $id );

	if ( ! $equipmanet )
		return array( 'error_msg' => 'Nenhum equipamento encontrado', 'http_status' => 400 );

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

	if ( ! isset( $id ) || ! $id ) {

		intranet_fafar_logs_register_log(
			'ERROR',
			'intranet_fafar_api_get_reservation_by_id',
			json_encode(
				array(
					'func' => 'intranet_fafar_api_get_reservation_by_id',
					'msg' => 'ID nor set or falsy, received',
					'obj' => $id,
				)
			),
		);

		return array( 'error_msg' => 'Nenhum ID informado', 'http_status' => 500 );

	}

	$id = sanitize_text_field( wp_unslash( $id ) );

	$query = "SELECT * FROM `SET_TABLE_NAME` WHERE `id` = '" . $id . "'";

	$reservation = intranet_fafar_api_read( $query );

	if ( ! $reservation || count( $reservation ) == 0 ) {

		intranet_fafar_logs_register_log(
			'ERROR',
			'intranet_fafar_api_get_reservation_by_id',
			json_encode(
				array(
					'func' => 'intranet_fafar_api_get_reservation_by_id',
					'msg' => 'No reservation found with ID',
					'obj' => $id,
				)
			),
		);

		return array( 'error_msg' => 'Nenhuma reserva encontrada com ID "' . ( ( isset( $id ) && $id ) ? $id : 'UNKNOW_ID' ) . '"', 'http_status' => 400 );

	}

	$reservation = $reservation[0];

	if ( isset( $reservation['owner'] ) && is_numeric( $reservation['owner'] ) ) {

		$reservation['owner'] = intranet_fafar_api_get_user_by_id( $reservation['owner'] );

	}

	if ( isset( $reservation['data']['applicant'][0] ) && is_numeric( $reservation['data']['applicant'][0] ) ) {

		$reservation['data']['applicant'] = intranet_fafar_api_get_user_by_id( $reservation['data']['applicant'][0] );

	}

	if ( isset( $reservation['data']['class_subject'][0] ) ) {

		$reservation['data']['class_subject'] = intranet_fafar_api_get_submission_by_id( $reservation['data']['class_subject'][0] );

	}

	return $reservation;
}

function intranet_fafar_api_set_reservation_technical_handler( $request ) {
	$id = (string) $request['id'];

	// Get data from the request
	$request_data = $request->get_json_params();

	$reservation = intranet_fafar_api_set_reservation_technical( $id, $request_data['technical_id'] );

	if ( isset( $reservation['error_msg'] ) ) {

		return new WP_Error( 'rest_api_sad', esc_html__( $reservation['error_msg'], 'intranet-fafar-api' ), ( ( $reservation['http_status'] ) ?? 400 ) );

	}

	return rest_ensure_response( $reservation );
}

function intranet_fafar_api_set_reservation_technical( $reservation_id, $technical_id ) {

	if ( ! $reservation_id ) {
		return array( 'error_msg' => 'ID de reserva de auditório não informado!', 'http_status' => 500 );
	}

	if ( ! $technical_id ) {
		return array( 'error_msg' => 'ID do técnico não informado!', 'http_status' => 500 );
	}

	$reservation = intranet_fafar_api_get_submission_by_id( $reservation_id, false );

	if ( ! $reservation ) {
		return array( 'error_msg' => 'Reserva de auditório não encontrado!', 'http_status' => 500 );
	}

	$reservation['data']['technical'] = $technical_id;

	$reservation['data']['status'] = 'Aguardando início';

	$submission = intranet_fafar_api_update( $reservation['id'], $reservation );

	if ( isset( $submission['error_msg'] ) )
		return $submission;

	intranet_fafar_mail_on_set_auditorium_reservation_technical( $reservation );

	return $submission;
}

function intranet_fafar_get_users_by_departament( $role_slug = null, $status = null, $category = null, $exclude = array() ) {
	if ( ! $role_slug )
		return array();

	$args = array(
		'role' => $role_slug,
		'status' => $status,
		'category' => $category,
	);

	if ( ! empty( $exclude ) ) {
		$args['exclude'] = $exclude;
	}

	return intranet_fafar_api_get_users( $args );
}

function intranet_fafar_get_users_by_departament_as_options( $role_slug = null, $status = null, $category = null, $exclude = array() ) {
	if ( ! $role_slug )
		return array();

	$args = array(
		'role' => $role_slug,
		'status' => $status,
		'category' => $category,
	);

	if ( ! empty( $exclude ) ) {
		$args['exclude'] = $exclude;
	}

	$users = intranet_fafar_api_get_users( $args );

	$options = array();

	foreach ( $users as $user ) {
		if (
			$status &&
			strtolower( $status ) !== strtolower( get_user_meta( $user['ID'], 'bond_status', true ) )
		)
			continue;

		$options[ esc_attr( $user['ID'] ) ] = esc_html( $user['display_name'] );
	}

	return $options;
}

function intranet_fafar_api_get_ips_handler( $request ) {
	$submissions = intranet_fafar_api_get_ips();

	if ( isset( $submissions['error_msg'] ) ) {

		return new WP_Error( 'rest_api_sad', esc_html__( $submissions['error_msg'], 'intranet-fafar-api' ), ( ( $submissions['http_status'] ) ?? 400 ) );

	}

	return rest_ensure_response( $submissions );
}

function intranet_fafar_api_get_ips() {
	$ips = intranet_fafar_api_get_submissions_by_object_name( 'ip', array( 'orderby_json' => 'address' ) );

	if ( isset( $ips['error_msg'] ) )
		return [ 'error_msg' => 'aqui' ];

	$equipaments = intranet_fafar_api_get_submissions_by_object_name( 'equipament' );

	if ( isset( $equipaments['error_msg'] ) )
		$equipaments = array();

	// Map equipment IPs for quick lookup
	$ip_to_equipament = [];
	foreach ( $equipaments as $equipament ) {
		if ( ! empty( $equipament['data']['ip'][0] ) ) {
			$ip_to_equipament[ $equipament['data']['ip'][0] ] = $equipament['id'];
		}
	}

	// Replace equipament_id in IPs
	return array_map( function ($ip) use ($ip_to_equipament) {
		$ip['data']['equipament_id'] = $ip_to_equipament[ $ip['id'] ] ?? null;

		return $ip;
	}, $ips );
}

function intranet_fafar_api_get_check_results_handler( $request ) {
	$keyword = $request->get_param( 'keyword' ) ? sanitize_text_field( $request->get_param( 'keyword' ) ) : '';
	$offset = $request->get_param( 'offset' ) ? intval( $request->get_param( 'offset' ) ) : 1;
	$limit = $request->get_param( 'limit' ) ? intval( $request->get_param( 'limit' ) ) : -1;
	$status = $request->get_param( 'status' ) ? sanitize_text_field( $request->get_param( 'status' ) ) : null;
	$ip = $request->get_param( 'id' ) ? sanitize_text_field( $request->get_param( 'id' ) ) : null;

	$submissions = intranet_fafar_api_get_check_results( $ip, $status, $keyword, $offset, $limit );

	if ( $submissions === false ) {
		return new WP_Error(
			'rest_api_sad',
			esc_html__( ( ! empty( $submissions['error_msg'] ) ? $submissions['error_msg'] : 'Erro no processamento' ), 'intranet-fafar-api' ),
			( ! empty( $submissions['http_status'] ) ? $submissions['http_status'] : 500 ),
		);
	}

	if ( empty( $submissions ) ) {
		return rest_ensure_response(
			array(
				'count' => 0,
				'next' => null,
				'previous' => null,
				'results' => [],
			)
		);
	}

	return rest_ensure_response(
		array(
			'count' => $submissions['pagination']['total_items'],
			'next' => null,
			'previous' => null,
			'results' => $submissions['data'],
		)
	);
}

function intranet_fafar_api_get_check_results(
	$ip = null,
	$status = null,
	$keyword = '',
	$offset = 1,
	$limit = -1
) {
	$query_params = array(
		'filters' => array(
			array(
				'column' => 'object_name',
				'value' => 'ip_check_result',
				'operator' => '=',
			),
		),
		'order_by' => array(
			'orderby_column' => 'created_at',
			'order' => 'DESC',
		),
		'page' => $offset,
		'per_page' => $limit,
		'keyword' => $keyword,
	);

	if ( ! empty( $ip ) && is_string( $ip ) ) {
		$query_params['filters'][] = array(
			'column' => 'data->ip',
			'value' => $ip,
			'operator' => '=',
		);
	}

	if ( ! empty( $status ) && is_string( $status ) ) {
		$query_params['filters'][] = array(
			'column' => 'data->status',
			'value' => $status,
			'operator' => '=',
			'case_sensitive' => false,
		);
	}

	$submissions = intranet_fafar_api_read( args: $query_params );

	return $submissions;
}


/*
 * SIMPLE CREATE, READ, UPDATE and DELETE FUNCS
 */
function intranet_fafar_api_create( $submission, $check_permissions = true, $do_action = true ) {

	if ( ! isset( $submission['data'] ) )
		return array( 'error_msg' => 'No "data" column informed!' );

	global $wpdb;

	$table_name = $wpdb->prefix . 'fafar_cf7crud_submissions';

	$bytes = random_bytes( 5 );
	$unique_hash = time() . bin2hex( $bytes );

	$submission['id'] = $unique_hash;

	if (
		! isset( $submission['form_id'] ) ||
		! $submission['form_id'] ||
		! is_numeric( $submission['form_id'] )
	) {
		$submission['form_id'] = '-2';
	}

	if (
		! isset( $submission['owner'] ) ||
		! $submission['owner'] ||
		! is_numeric( $submission['owner'] )
	) {
		$submission['owner'] = get_current_user_id();
	}

	if ( ! intranet_fafar_utils_is_json( $submission['data'] ) ) {
		$submission['data'] = json_encode( $submission['data'] );
	}

	$wpdb->insert( $table_name, $submission );

	if ( $do_action )
		do_action( 'intranet_fafar_api_after_create', $submission['id'] );

	return array( 'id' => $submission['id'] );

}

/**
 * Retrieves and processes submissions from the custom database table with advanced filtering, ordering, and pagination.
 * Supports searching for a keyword across all JSON properties.
 *
 * @since 1.0.0
 *
 * @param array $args {
 *     An array of arguments for customizing the query and behavior of the function.
 *
 *     @type bool   $check_permissions Optional. Whether to check permissions for each submission. Default true.
 *     @type bool   $check_is_active   Optional. Whether to filter out inactive submissions. Default true.
 *     @type int    $page              Optional. The current page number for pagination. Default 1.
 *     @type int    $per_page          Optional. The number of items to return per page. Default 10.
 *     @type array  $filters           Optional. An array of filters to apply to the query. Each filter is an associative array
 *                                    with the keys 'column', 'operator', 'value', and 'case_sensitive'.
 *                                    For JSON data, use 'column' as 'data->key'.
 *                                    The 'value' can be a single value or an array of values for the 'IN' operator.
 *                                    The 'case_sensitive' key determines if the comparison is case-sensitive (default: true).
 *     @type array  $order_by          Optional. An array specifying the ordering of results. Supports ordering by columns or JSON data.
 *                                    Example: array( 'orderby_column' => 'created_at', 'order' => 'DESC' ) or
 *                                    array( 'orderby_json' => 'age', 'order' => 'ASC', 'inet_aton' => true ).
 *     @type string $keyword           Optional. A keyword to search across all JSON properties.
 *     @type bool   $return_count_only Optional. If true, returns only the row count and pagination metadata. Default false.
 *     @type int    $single            Optional. If provided, returns a single submission.
 * }
 *
 * @return array|array|null {
 *     An array containing the processed submissions and pagination metadata, or a single submission if single_id is provided.
 *
 *     @type array $data {
 *         An array of processed submissions. Empty if `return_count_only` is true.
 *
 *         @type array $submission {
 *             A single submission with decoded data and optional permission flags.
 *
 *             @type array  $data          The decoded JSON data from the submission.
 *             @type bool   $prevent_write Optional. Set to true if write permission is denied.
 *             @type bool   $prevent_exec  Optional. Set to true if execute permission is denied.
 *         }
 *     }
 *     @type array $pagination {
 *         Pagination metadata.
 *
 *         @type int $page        The current page number.
 *         @type int $per_page    The number of items per page.
 *         @type int $total_items The total number of items available.
 *         @type int $total_pages The total number of pages.
 *     }
 * }
 */
function intranet_fafar_api_read( $query = '', $check_permissions = true, $check_is_active = true, $args = array() ) {

	if ( empty( $args ) )
		return intranet_fafar_api_old_read( $query, $check_permissions, $check_is_active );

	// Default parameters
	$defaults = array(
		'check_permissions' => true, // Whether to check permissions
		'check_is_active' => true, // Whether to check if submissions are active
		'page' => 1,    // Current page for pagination
		'per_page' => -1,   // Number of items per page. -1: unlimited
		'filters' => array(), // Filters for table columns and JSON data
		'order_by' => array(), // Ordering configuration
		'keyword' => '',    // Keyword to search across all JSON properties
		'return_count_only' => false, // If true, returns only the row count
		'single' => false,  // If provided, returns the single submission or the first
		'relationships' => array(),   // For relations with others submissions and WP users
	);

	// Merge user-provided arguments with defaults
	$args = wp_parse_args( $args, $defaults );

	global $wpdb;

	// Construct the table name
	$table_name = $wpdb->prefix . 'fafar_cf7crud_submissions';

	// Base query with enhanced relationship support
	$select_fields = "$table_name.*";
	$join_clauses = '';
	$additional_selects = '';
	$temporary_tables = array();
	$index_temporary_tables = array();

	// Handle relationships - more flexible version
	if ( ! empty( $args['relationships'] ) ) {
		foreach ( $args['relationships'] as $rel_name => $relationship ) {
			$relation_type = $relationship['type'] ?? 'submission'; // 'submission' or 'user'
			$local_path = $relationship['local_path']; // e.g., 'owner' or 'place' or 'data->place'

			// Determine if we're accessing a direct field or JSON path
			$is_json_path = strpos( $local_path, 'data->' ) === 0;
			$join_alias = "rel_$rel_name";

			if ( $relation_type === 'submission' ) {

				// Cria tabela temporária para cada obj relacionado
				$temporary_tables[] = "CREATE TEMPORARY TABLE temp_$rel_name AS SELECT id, data FROM $table_name WHERE $table_name.object_name = '$rel_name'; ";

				$index_temporary_tables[] = "ALTER TABLE temp_$rel_name ADD INDEX (id); ";

				// Join with another submission
				$join_clauses .= " LEFT JOIN temp_$rel_name AS $join_alias ";

				if ( $is_json_path ) {
					$json_key = substr( $local_path, 6 ); // Remove 'data->'
					if ( isset( $relationship['array_compare'] ) && $relationship['array_compare'] ) {
						$join_clauses .= "ON JSON_CONTAINS($table_name.data, JSON_QUOTE($join_alias.id), '$.$json_key') ";
					} else {
						$join_clauses .= "ON $join_alias.id = CAST( JSON_UNQUOTE(JSON_EXTRACT($table_name.data, '$.$json_key')) AS UNSIGNED) ";
					}
				} else {
					// Direct column reference
					$join_clauses .= "ON $join_alias.id = $table_name.$local_path ";
				}

				// Add fields from related submission
				$additional_selects .= ", $join_alias.data AS {$rel_name}_data";
				$additional_selects .= ", $join_alias.id AS {$rel_name}_id";

			} elseif ( $relation_type === 'user' ) {
				// Join with WordPress users table
				$users_table = $wpdb->users;
				$usermeta_table = $wpdb->usermeta;
				$join_clauses .= " LEFT JOIN $users_table AS $join_alias ";

				if ( $is_json_path ) {
					$json_key = substr( $local_path, 6 ); // Remove 'data->'
					if ( isset( $relationship['array_compare'] ) && $relationship['array_compare'] ) {
						$join_clauses .= "ON JSON_CONTAINS($table_name.data, CAST($join_alias.ID AS CHAR), '$.$json_key') OR ";
						$join_clauses .= "JSON_CONTAINS($table_name.data, JSON_QUOTE(CAST($join_alias.ID AS CHAR)), '$.$json_key') ";
					} else {
						$join_clauses .= "ON $join_alias.ID = CAST( JSON_UNQUOTE(JSON_EXTRACT($table_name.data, '$.$json_key')) AS UNSIGNED) ";
					}
				} else {
					// Direct column reference
					$join_clauses .= "ON $join_alias.ID = CAST( $table_name.$local_path AS UNSIGNED ) ";
				}

				// Add user fields
				$additional_selects .= ", $join_alias.display_name AS {$rel_name}_display_name";
				$additional_selects .= ", $join_alias.user_login AS {$rel_name}_user_login";
				$additional_selects .= ", $join_alias.user_email AS {$rel_name}_user_email";
				$additional_selects .= ", $join_alias.ID AS {$rel_name}_ID";

				// Optional: join usermeta for additional user fields
				if ( ! empty( $relationship['meta_fields'] ) ) {
					foreach ( $relationship['meta_fields'] as $meta_index => $meta_key ) {
						$meta_alias = "um_{$rel_name}_{$meta_index}";
						$join_clauses .= " LEFT JOIN $usermeta_table AS $meta_alias ";
						$join_clauses .= "ON ($meta_alias.user_id = $join_alias.ID AND $meta_alias.meta_key = '$meta_key') ";
						$additional_selects .= ", $meta_alias.meta_value AS {$rel_name}_{$meta_key}";
					}
				}
			}
		}
	}

	// Build the WHERE clause from filters
	$where_clause = '';
	if ( ! empty( $args['filters'] ) ) {
		$where_conditions = array();
		foreach ( $args['filters'] as $filter ) {
			$column = $filter['column'];
			$operator = strtoupper( $filter['operator'] );
			$value = $filter['value'];
			$case_sensitive = isset( $filter['case_sensitive'] ) ? (bool) $filter['case_sensitive'] : true;

			// Validate operator
			if ( ! in_array( $operator, array( '=', '!=', '>', '<', 'LIKE', 'IN' ) ) ) {
				continue; // Skip invalid operators
			}

			// Handle JSON data filtering
			if ( strpos( $column, 'data->' ) === 0 ) {
				$json_key = substr( $column, 6 ); // Extract the JSON key

				if ( $operator === 'IN' ) {
					// Handle IN operator for JSON data
					if ( ! is_array( $value ) ) {
						continue; // Skip if value is not an array
					}

					// Escape and prepare values for IN clause
					$escaped_values = array_map( function ($val) use ($wpdb) {
						return $wpdb->prepare( '%s', $val );
					}, $value );

					$in_values = implode( ',', $escaped_values );

					if ( $case_sensitive ) {
						$where_conditions[] = "JSON_UNQUOTE(JSON_EXTRACT($table_name.data, '$.$json_key')) IN ($in_values)";
					} else {
						$where_conditions[] = "LOWER(JSON_UNQUOTE(JSON_EXTRACT($table_name.data, '$.$json_key'))) IN ($in_values)";
					}
				} else if ( $operator === 'LIKE' ) {
					// Handle other operators for JSON data
					$escaped_value = $wpdb->esc_like( $value );


					if ( $case_sensitive ) {
						$where_conditions[] = "JSON_UNQUOTE(JSON_EXTRACT($table_name.data, '$.$json_key')) LIKE '%$escaped_value%'";
					} else {
						$where_conditions[] = "LOWER(JSON_UNQUOTE(JSON_EXTRACT($table_name.data, '$.$json_key'))) LIKE LOWER('%$escaped_value%')";
					}
				} else {
					// Handle other operators for JSON data
					$escaped_value = $wpdb->prepare( '%s', $value );

					if ( $case_sensitive ) {
						$where_conditions[] = "JSON_UNQUOTE(JSON_EXTRACT($table_name.data, '$.$json_key')) $operator $escaped_value";
					} else {
						$where_conditions[] = "LOWER(JSON_UNQUOTE(JSON_EXTRACT($table_name.data, '$.$json_key'))) $operator LOWER($escaped_value)";
					}
				}
			} else {
				// Handle regular column filtering
				if ( $operator === 'IN' ) {
					// Handle IN operator for regular columns
					if ( ! is_array( $value ) ) {
						continue; // Skip if value is not an array
					}

					// Escape and prepare values for IN clause
					$escaped_values = array_map( function ($val) use ($wpdb) {
						return $wpdb->prepare( '%s', $val );
					}, $value );

					$in_values = implode( ',', $escaped_values );

					if ( $case_sensitive ) {
						$where_conditions[] = "$table_name.$column IN ($in_values)";
					} else {
						$where_conditions[] = "LOWER($table_name.$column) IN ($in_values)";
					}
				} else {
					// Handle other operators for regular columns
					$escaped_value = $wpdb->prepare( '%s', $value );

					if ( $case_sensitive ) {
						$where_conditions[] = "$table_name.$column $operator $escaped_value";
					} else {
						$where_conditions[] = "LOWER($table_name.$column) $operator LOWER($escaped_value)";
					}
				}
			}
		}

		if ( ! empty( $where_conditions ) ) {
			$where_clause = ' WHERE ' . implode( ' AND ', $where_conditions );
		}
	}

	// Enhanced keyword search including relationships
	if ( ! empty( $args['keyword'] ) ) {
		$keyword = $wpdb->esc_like( strtolower( $args['keyword'] ) );
		$keyword_conditions = array(
			"JSON_SEARCH(LOWER($table_name.data), 'one', '%$keyword%') IS NOT NULL"
		);

		// Add relationship fields to keyword search
		if ( ! empty( $args['relationships'] ) ) {
			foreach ( $args['relationships'] as $rel_name => $relationship ) {
				if ( $relationship['type'] === 'submission' ) {
					$keyword_conditions[] = "JSON_SEARCH(LOWER(rel_$rel_name.data), 'one', LOWER('%$keyword%')) IS NOT NULL";
				} elseif ( $relationship['type'] === 'user' ) {
					$keyword_conditions[] = "LOWER(rel_$rel_name.display_name) LIKE LOWER('%$keyword%')";
					$keyword_conditions[] = "LOWER(rel_$rel_name.user_login) LIKE LOWER('%$keyword%')";
					$keyword_conditions[] = "LOWER(rel_$rel_name.user_email) LIKE LOWER('%$keyword%')";

					// Search in user meta fields if specified
					if ( ! empty( $relationship['meta_fields'] ) ) {
						foreach ( $relationship['meta_fields'] as $meta_index => $meta_key ) {
							$meta_alias = "um_{$rel_name}_{$meta_index}";
							$keyword_conditions[] = "LOWER($meta_alias.meta_value) LIKE LOWER('%$keyword%')";
						}
					}
				}
			}
		}

		$keyword_condition = '(' . implode( ' OR ', $keyword_conditions ) . ')';

		if ( empty( $where_clause ) ) {
			$where_clause = " WHERE $keyword_condition";
		} else {
			$where_clause .= " AND $keyword_condition";
		}
	}

	if ( $args['check_is_active'] ) {
		$where_clause .= ' AND is_active = 1 ';
	}

	$select_fields = " $table_name.* ";

	// Build the ORDER BY clause
	$order_by_clause = "";
	if ( ! empty( $args['order_by'] ) ) {
		$order = isset( $args['order_by']['order'] ) && strtoupper( $args['order_by']['order'] ) === 'DESC' ? 'DESC' : 'ASC';

		if ( isset( $args['order_by']['orderby_column'] ) ) {
			// Order by table column
			$order_by_clause = ' ORDER BY ' . $args['order_by']['orderby_column'] . ' ' . $order;
		} elseif ( isset( $args['order_by']['orderby_json'] ) ) {
			// Order by JSON data
			$json_key = $args['order_by']['orderby_json'];
			$inet_function = isset( $args['order_by']['inet_aton'] ) && $args['order_by']['inet_aton'] ? 'INET_ATON' : '';

			// Modify the query to include the JSON property for ordering
			$select_fields .= ", $inet_function(JSON_UNQUOTE(JSON_EXTRACT($table_name.data, '$.$json_key'))) AS json_prop ";
			$order_by_clause = ' ORDER BY json_prop ' . $order;
		}
	}

	$query_head = "SELECT SQL_CALC_FOUND_ROWS $select_fields $additional_selects FROM $table_name $join_clauses";

	$full_query = $query_head . $where_clause . $order_by_clause;

	// If single is true
	if ( $args['single'] ) {
		// When searching by single ID, we only want one result
		$args['per_page'] = 1;
	}

	// Add pagination to the query
	if ( $args['per_page'] > -1 && ! $args['return_count_only'] ) {
		$offset = ( $args['page'] - 1 ) * $args['per_page'];
		$full_query .= " LIMIT {$args['per_page']} OFFSET $offset";
	}

	error_log( $full_query );

	// Criar as tabelas temporárias e indexá-las, se necessário
	if ( ! empty( $temporary_tables ) && ! empty( $index_temporary_tables ) ) {
		foreach ( $temporary_tables as $temporary_table_query ) {
			$wpdb->query( $temporary_table_query );
		}

		foreach ( $index_temporary_tables as $index_temporary_tables_query ) {
			$wpdb->query( $index_temporary_tables_query );
		}
	}

	// Execute the query
	if ( ! $args['return_count_only'] ) {
		$submissions = $wpdb->get_results( $full_query, 'ARRAY_A' );

		// Handle query errors or empty results
		if ( $submissions === null ) {
			error_log( 'Database query failed!' );
			return false;
		}

		if ( empty( $submissions ) ) {
			// If we're looking for a single submission and found nothing, return null
			if ( $args['single'] ) {
				return null;
			}
			return array();
		}
	}

	// Get the total number of submissions using FOUND_ROWS()
	$total_submissions = $wpdb->get_var( 'SELECT FOUND_ROWS()' );
	$total_pages = ceil( $total_submissions / $args['per_page'] );

	// If only the count is requested, return early
	if ( $args['return_count_only'] ) {
		return array(
			'data' => array(), // Empty data array
			'pagination' => array(
				'page' => $args['page'],
				'per_page' => $args['per_page'],
				'total_items' => $total_submissions,
				'total_pages' => $total_pages,
			),
		);
	}

	// Process submissions with enhanced relationship handling
	$submissions_checked = array();
	foreach ( $submissions as $submission ) {
		// Decode the JSON data field
		$submission['data'] = json_decode( $submission['data'], true );

		// Checks for read permission
		if ( $args['check_permissions'] &&
			! intranet_fafar_api_check_read_permission( $submission ) )
			continue;

		/*
		 * Checks for read permission.
		 * If doesn't, set a 'prevent_write' prop to true
		 */
		if ( $args['check_permissions'] &&
			! intranet_fafar_api_check_write_permission( $submission ) )
			$submission['data']['prevent_write'] = true;

		/*
		 * Checks for read permission.
		 * If doesn't, set a 'prevent_exec' prop to true
		 */
		if ( $args['check_permissions'] &&
			! intranet_fafar_api_check_exec_permission( $submission ) )
			$submission['data']['prevent_exec'] = true;

		// Process relationships
		if ( ! empty( $args['relationships'] ) ) {
			$submission['relationships'] = array();

			foreach ( $args['relationships'] as $rel_name => $relationship ) {
				$relation_type = $relationship['type'] ?? 'submission';

				if ( $relation_type === 'submission' && isset( $submission[ "{$rel_name}_data" ] ) ) {
					$submission['relationships'][ $rel_name ] = array(
						'id' => $submission[ "{$rel_name}_id" ],
						'data' => json_decode( $submission[ "{$rel_name}_data" ], true )
					);
					unset( $submission[ "{$rel_name}_data" ], $submission[ "{$rel_name}_id" ] );

				} elseif ( $relation_type === 'user' ) {
					$user_data = array(
						'ID' => $submission[ "{$rel_name}_ID" ],
						'display_name' => $submission[ "{$rel_name}_display_name" ] ?? '',
						'user_email' => $submission[ "{$rel_name}_user_email" ] ?? '',
						'user_login' => $submission[ "{$rel_name}_user_login" ] ?? '',

					);

					// Add user meta fields if requested
					if ( ! empty( $relationship['meta_fields'] ) ) {
						foreach ( $relationship['meta_fields'] as $meta_key ) {
							$user_data[ $meta_key ] = $submission[ "{$rel_name}_{$meta_key}" ] ?? null;
						}
					}

					$submission['relationships'][ $rel_name ] = $user_data;
					unset(
						$submission[ "{$rel_name}_ID" ],
						$submission[ "{$rel_name}_display_name" ],
						$submission[ "{$rel_name}_user_email" ],
						$submission[ "{$rel_name}_user_login" ]
					);

					// Clean up meta fields
					if ( ! empty( $relationship['meta_fields'] ) ) {
						foreach ( $relationship['meta_fields'] as $meta_key ) {
							unset( $submission[ "{$rel_name}_{$meta_key}" ] );
						}
					}
				}
			}
		}


		// [Rest of the processing remains the same]
		array_push( $submissions_checked, $submission );
	}

	// If we're looking for a single submission, return just that submission or null
	if ( $args['single'] ) {
		return ! empty( $submissions_checked ) ? $submissions_checked[0] : null;
	}

	// Return the results with pagination metadata
	return array(
		'data' => $submissions_checked,
		'pagination' => array(
			'page' => $args['page'],
			'per_page' => $args['per_page'],
			'total_items' => $total_submissions,
			'total_pages' => $total_pages,
		),
	);
}

function intranet_fafar_api_old_read( $query, $check_permissions = true, $check_is_active = true ) {

	if ( ! $query )
		return array( 'error_msg' => 'No query str on "intranet_fafar_api_read"!' );

	global $wpdb;

	$table_name = $wpdb->prefix . 'fafar_cf7crud_submissions';

	$query_completed = str_replace( 'SET_TABLE_NAME', $table_name, $query );

	$submissions = $wpdb->get_results( $query_completed, 'ARRAY_A' );

	if ( $submissions === null )
		return array();

	if ( empty( $submissions ) )
		return array();

	$submissions_checked = array();
	foreach ( $submissions as $submission ) {

		$submission['data'] = json_decode( $submission['data'], true );

		// Check if 'is active'
		if ( $check_is_active &&
			$submission['is_active'] != 1 )
			continue;

		// Checks for read permission
		if ( $check_permissions &&
			! intranet_fafar_api_check_read_permission( $submission ) )
			continue;

		/*
		 * Checks for read permission.
		 * If doesn't, set a 'prevent_write' prop to true
		 */
		if ( $check_permissions &&
			! intranet_fafar_api_check_write_permission( $submission ) )
			$submission['data']['prevent_write'] = true;

		/*
		 * Checks for read permission.
		 * If doesn't, set a 'prevent_exec' prop to true
		 */
		if ( $check_permissions &&
			! intranet_fafar_api_check_exec_permission( $submission ) )
			$submission['data']['prevent_exec'] = true;

		array_push( $submissions_checked, $submission );

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

	$submission['data'] = intranet_fafar_utils_is_json( $submission['data'] ) ?
		$submission['data'] : json_encode( $submission['data'] );

	// Excluir 'updated_at', se existir
	if ( isset( $submission['updated_at'] ) )
		unset( $submission['updated_at'] );

	// Excluir 'created_at', se existir
	if ( isset( $submission['created_at'] ) )
		unset( $submission['created_at'] );

	if ( ! is_string( $submission['owner'] ) ) {
		unset( $submission['owner'] );
	}

	$wpdb->update( $table_name, $submission, array( 'id' => $id ) );

	do_action( 'intranet_fafar_api_after_update', $id, $submission );

	return array( 'id' => $id, 'submission' => $submission );

}

function intranet_fafar_api_delete( $submission, $delete_permanently = false, $check_permissions = true ) {

	if ( ! isset( $submission['id'] ) )
		return array( 'error_msg' => 'No ID informed!' );

	if ( $check_permissions && ! intranet_fafar_api_check_write_permission( $submission ) )
		return array( 'error_msg' => 'Permission denied!' );

	global $wpdb;

	$table_name = $wpdb->prefix . 'fafar_cf7crud_submissions';

	if ( $delete_permanently ) {

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
	$owner = (string) ( $submission['owner'] ?? 0 );
	// Caso receba um objeto submission com o valor do owner substituito pelos dados do owner
	$owner = (string) ( isset( $submission['owner']['ID'] ) ? $submission['owner']['ID'] : $submission['owner'] );
	$group_owner = (string) ( $submission['group_owner'] ?? 0 );
	$permissions = (string) ( $submission['permissions'] ?? '777' );

	$current_user_id = (string) ( $user_id ?? get_current_user_id() );
	$user_meta = get_userdata( (int) $current_user_id );
	$user_roles = ( $user_meta ? $user_meta->roles : [] ); // array( [0] => 'techs', ... )

	$OWNER_PERMISSION_DIGIT_INDEX = 0;
	$OWNER_GROUP_PERMISSION_DIGIT_INDEX = 1;
	$OTHERS_PERMISSION_DIGIT_INDEX = 2;

	/**
	 * If the current user is the 'administrator', 
	 * it gets instant permission.
	 */
	if ( in_array( 'administrator', $user_roles ) )
		return true;

	// Permissions not set
	if ( ! $permissions )
		return true;

	// Do not has restriction
	if ( $permissions === '777' )
		return true;

	// Current user is the owner
	if ( $owner === $current_user_id ) {

		$permission_value = (int) str_split( $permissions )[ $OWNER_PERMISSION_DIGIT_INDEX ];
		return in_array( $permission_value, $permission_digit_values, true );

	}

	/**
	 * Group permissions
	 * If user is on $group_owner.
	 * $user_roles. Array. array( [0] => 'techs', ... )
	 */
	if ( in_array( strtolower( $group_owner ), $user_roles ) ) {

		$permission_value = (int) str_split( $permissions )[ $OWNER_GROUP_PERMISSION_DIGIT_INDEX ];
		return in_array( $permission_value, $permission_digit_values, true );

	}

	// Others permissions
	$permission_value = (int) str_split( $permissions )[ $OTHERS_PERMISSION_DIGIT_INDEX ];
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
			$data[ $key ] = intranet_fafar_api_san_recursive( $value );
		}
	} else {
		// Sanitize individual value
		$data = intranet_fafar_api_san( $data );
	}

	return $data;

}

function intranet_fafar_api_san_arr( $arr ) {

	foreach ( $arr as $k => $v ) {

		/*
		 * Isso aqui pode dar merda porque:
		 * - Checks for invalid UTF-8,
		 * - Converts single < characters to entities,
		 * - Strips all tags, 
		 * - Removes line breaks, tabs, and extra whitespace, 
		 * - Strips percent-encoded characters,
		 */
		$arr[ $k ] = intranet_fafar_api_san_recursive( $v );

	}

	return $arr;

}