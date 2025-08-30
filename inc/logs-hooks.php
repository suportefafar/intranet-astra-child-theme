<?php
// Prevent direct access to the file.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'wp_login', 'intranet_fafar_logs_login', 10, 2 );

add_action( 'application_password_failed_authentication', 'intranet_fafar_logs_login_password_failed_auth', 10, 1 );

add_action( 'application_password_did_authenticate', 'intranet_fafar_logs_login_password_success_auth', 10, 1 );

add_action( 'retrieve_password', 'intranet_fafar_logs_retrieve_password', 10, 1 );

add_action( 'retrieve_password_key', 'intranet_fafar_logs_retrieve_password_key', 10, 1 );

add_action( 'user_register', 'intranet_fafar_logs_user_register', 10, 2 );

add_action( 'register_new_user', 'intranet_fafar_logs_register_new_user', 10, 1 );

add_action( 'profile_update', 'intranet_fafar_logs_profile_update', 10, 3 );

add_action( 'fafar_cf7crud_after_create', 'intranet_fafar_logs_create_log_from_fafar_cf7crud', 10, 1 );

add_action( 'fafar_cf7crud_after_update', 'intranet_fafar_logs_update_log_from_fafar_cf7crud', 10, 1 );

add_action( 'intranet_fafar_api_after_create', 'intranet_fafar_logs_create_log_from_api', 10, 1 );


function intranet_fafar_logs_login( $user_login, $user ) {

	intranet_fafar_logs_register_log( 'LOGIN', $user->get( 'ID' ), json_encode( array( 'user_login' => $user_login, 'user' => $user ) ) );

}

function intranet_fafar_logs_login_password_fail_auth( $error ) {

	intranet_fafar_logs_register_log( 'LOGIN PASSWORD FAIL AUTH', 0, json_encode( $error ) );

}

function intranet_fafar_logs_retrieve_password( $user_login ) {

	intranet_fafar_logs_register_log( 'RETRIEVE PASSWORD', $user_login, json_encode( $user_login ) );

}

function intranet_fafar_logs_retrieve_password_key( $user_login ) {

	intranet_fafar_logs_register_log( 'RETRIEVE PASSWORD KEY', $user_login, json_encode( $user_login ) );

}

function intranet_fafar_logs_login_password_success_auth( $user ) {

	intranet_fafar_logs_register_log( 'LOGIN PASSWORD SUCCESS AUTH', $user->get( 'ID' ), json_encode( $user ) );

}

function intranet_fafar_logs_user_register( $user_id, $userdata ) {

	intranet_fafar_logs_register_log( 'CREATE USER[user_register]', $user_id, json_encode( $userdata ) );

}

function intranet_fafar_logs_register_new_user( $user_id ) {

	intranet_fafar_logs_register_log( 'CREATE USER FROM FORM[register_new_user]', $user_id, json_encode( $user_id ) );

}

function intranet_fafar_logs_profile_update( $user_id, $old_user_data, $userdata ) {

	intranet_fafar_logs_register_log( 'UPDATE USER', $user_id, json_encode( array( 'old' => $old_user_data, 'new' => $userdata ) ) );

}

function intranet_fafar_logs_create_log_from_fafar_cf7crud( $submission_id ) {

	intranet_fafar_logs_register_log( 'CREATE CF7 SUBMISSION', $submission_id, 'Submission created by CF7 form' );

}

function intranet_fafar_logs_update_log_from_fafar_cf7crud( $submission_id ) {

	intranet_fafar_logs_register_log( 'UPDATE CF7 SUBMISSION', $submission_id, 'Submission updated by CF7 form' );

}

function intranet_fafar_logs_create_log_from_api( $submission_id ) {

	intranet_fafar_logs_register_log( 'CREATE API', $submission_id, 'Submission created by internal API' );

}

function intranet_fafar_logs_register_log( $category, $source, $desc, $user = null ) {

	// Category not informed
	if (
		! isset( $category ) ||
		! $category
	) {
		$category = false;
	}

	// Source not informed
	if (
		! isset( $source ) ||
		! $source
	) {
		$source = false;
	}

	// Desc not informed
	if (
		! isset( $desc ) ||
		! $desc
	) {
		$desc = 'Desc not informed';
	} else {
		if ( ! is_string( $desc ) ) {
			$desc = json_encode( $desc );
		}

		$desc = sanitize_text_field( $desc );
	}

	// User not informed
	if (
		! isset( $user ) ||
		! $user
	) {
		$user = get_current_user_id();
	} else {
		$desc = '[User informed] ' . $desc;
	}


	$submission = array(
		'owner' => 1,
		'group_owner' => 'tecnologia_da_informacao_e_suporte',
		'permissions' => '770',
		'object_name' => 'log',
		'data' => array(
			'category' => $category,
			'source' => $source,
			'desc' => $desc,
			'user' => $user,
		),
	);

	intranet_fafar_api_create( $submission, true, false );

}