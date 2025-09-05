<?php
// Prevent direct access to the file.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* 
 * 
 * Shortcodes that returns html for forms, used on 
 * Contact Form 7 - Dynamic Text Extension plugin
 */
add_shortcode( 'intranet_fafar_get_users_as_select_options', 'intranet_fafar_get_users_as_select_options' );

add_shortcode( 'intranet_fafar_get_ips_as_select_options', 'intranet_fafar_get_ips_as_select_options' );

add_shortcode( 'intranet_fafar_get_user_slug_role', 'intranet_fafar_get_user_slug_role' );

add_shortcode( 'intranet_fafar_get_classrooms_as_select_options', 'intranet_fafar_get_classrooms_as_select_options' );

add_shortcode( 'intranet_fafar_get_subjects_as_select_options', 'intranet_fafar_get_subjects_as_select_options' );

add_shortcode( 'intranet_fafar_generate_service_ticket_code', 'intranet_fafar_generate_service_ticket_code' );

add_shortcode( 'intranet_fafar_get_not_classrooms_as_select_options', 'intranet_fafar_get_not_classrooms_as_select_options' );

add_shortcode( 'intranet_fafar_all_places_as_select_options', 'intranet_fafar_all_places_as_select_options' );

function intranet_fafar_get_users_as_select_options_old() {

	$users = get_users(
		array(
			'role__not_in' => 'Administrator',
			'orderby' => 'display_name',
			'order' => 'ASC'
		)
	);

	$options = '<option value=""></option>';
	foreach ( $users as $user ) {

		$options .= '<option value="' . $user->data->ID . '">';
		$options .= $user->data->display_name;
		$options .= '</option>';

	}

	return $options;

}

function intranet_fafar_get_users_as_select_options( $encode = true ) {

	$users = get_users(
		array(
			'role__not_in' => 'Administrator',
			'orderby' => 'display_name',
			'order' => 'ASC'
		)
	);

	$options = array();

	foreach ( $users as $user ) {
		$options[ esc_attr( $user->ID ) ] = esc_html( $user->display_name );
	}

	/**
	 * FAFAR-CF7CRUD plugin is sending array() with 0 element 
	 * So, unless explicit FALSE...
	 */
	if ( $encode !== false ) 
		return json_encode( $options );

	return $options;

}

function intranet_fafar_get_ips_as_select_options() {

	$ips = intranet_fafar_api_get_submissions_by_object_name( 'ip', array(
		'orderby_json' => 'address',
		'inet_aton' => '1',
	) );

	// error_log( print_r( $ips, true ) );

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
			if ( $current_equipament &&
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
			$options[ esc_attr( $ip['id'] ) ] = esc_html( $ip['data']['address'] );

	}

	// error_log( print_r( $options, true ) );

	return json_encode( $options );

}

function intranet_fafar_get_subjects_as_select_options( $encode = true ) {

	$subjects = intranet_fafar_api_get_submissions_by_object_name( 'class_subject', array(
		'orderby_json' => 'code',
	) );

	$options = array();

	if ( isset( $subjects['error_msg'] ) )
		return json_encode( $options );

	foreach ( $subjects as $subject ) {

		if ( isset( $subject['data']['code'] ) ) {
			$options[ esc_attr( $subject['id'] ) ] = esc_html( $subject['data']['code'] ) .
				' (' . esc_html( $subject['data']['group'] ) . ')';
		}

	}

	/**
	 * FAFAR-CF7CRUD plugin is sending array() with 0 element 
	 * So, unless explicit FALSE...
	 */
	if ( $encode !== false ) 
		return json_encode( $options );
	
	return $options;

}

function intranet_fafar_get_classrooms_as_select_options() {
	$places = intranet_fafar_api_get_reservable_places();

	// error_log(print_r($places, true));

	$options = array();

	foreach ( $places as $place ) {
		$desc = $place['data']['number'] . ( ! empty( $place['data']['desc'] ) ? ' ' . $place['data']['desc'] : '' );
		$options[ esc_attr( $place['id'] ) ] = esc_html( $desc );
	}

	return json_encode( $options );
}

function intranet_fafar_get_not_classrooms_as_select_options() {
	$places = intranet_fafar_api_get_not_reservable_places();

	$options = array();

	foreach ( $places as $place ) {
		$desc = $place['data']['number'] . ( ! empty( $place['data']['desc'] ) ? ' ' . $place['data']['desc'] : '' );
		$options[ esc_attr( $place['id'] ) ] = esc_html( $desc );
	}

	return json_encode( $options );
}

function intranet_fafar_all_places_as_select_options() {
	$places = intranet_fafar_api_get_submissions_by_object_name(
		'place',
		array(
			'orderby_json' => 'number',
		),
		array(),
		false
	);

	if ( empty( $places ) || empty( $places['data'] ) )
		return json_encode( [] );

	$options = array();

	foreach ( $places['data'] as $place ) {
		$desc = $place['data']['number'] . ( ! empty( $place['data']['desc'] ) ? ' ' . $place['data']['desc'] : '' );
		$options[ esc_attr( $place['id'] ) ] = esc_html( $desc );
	}

	return json_encode( $options );
}

function intranet_fafar_generate_service_ticket_code() {

	$number_of_letters = 3;
	$number_of_digits = 3;

	$code_used = true;
	$new_code = '------';
	do {

		$new_code = intranet_fafar_generate_code( $number_of_letters, $number_of_digits );

		$query = "SELECT * FROM `SET_TABLE_NAME` WHERE `object_name` = 'service_ticket' AND JSON_CONTAINS(data, '\"" . $new_code . "\"', '$.code')";

		// Obtém todas as ordens de serviços, mesmo que ativas
		$submissions = intranet_fafar_api_read( $query, false, false );

		if ( empty( $submissions ) || isset( $submissions['error_msg'] ) )
			$code_used = false;

	} while ( $code_used );

	// Concatenate letters and numbers
	return $new_code;

}

function intranet_fafar_generate_code( $n_letters, $n_digits ) {

	// Generate three random uppercase letters
	$letters = '';
	for ( $i = 0; $i < $n_letters; $i++ ) {
		$letters .= chr( rand( 65, 90 ) ); // ASCII values for A-Z are 65-90
	}

	// Generate three random digits
	$numbers = '';
	for ( $i = 0; $i < $n_digits; $i++ ) {
		$numbers .= rand( 0, 9 );
	}

	// Concatenate letters and numbers
	return $letters . $numbers;

}

function intranet_fafar_get_user_slug_role() {

	return ( isset( wp_get_current_user()->roles[0] ) ? wp_get_current_user()->roles[0] : '' );

}