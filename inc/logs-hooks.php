<?php
// Prevent direct access to the file.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/*
 *
 * Logger do Grid.JS
 * Tem alguma coisa pra copiar?
 * 
class Logger {
    private format(message: string, type: string): string {
      return `[Grid.js] [${type.toUpperCase()}]: ${message}`;
    }
  
    error(message: string, throwException = false): void {
      const msg = this.format(message, 'error');
  
      if (throwException) {
        throw Error(msg);
      } else {
        console.error(msg);
      }
    }
  
    warn(message: string): void {
      console.warn(this.format(message, 'warn'));
    }
  
    info(message: string): void {
      console.info(this.format(message, 'info'));
    }
  }
  
  export default new Logger();
  *
 */

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

    intranet_fafar_logs_register_log( 'LOGIN PASSWORD SUCCESS AUTH', $user->get( 'ID' ) , json_encode( $user ) );

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

    intranet_fafar_logs_register_log( 'CREATE CF7 SUBMISSION', $submission_id , 'Submission created by CF7 form' );

}

function intranet_fafar_logs_update_log_from_fafar_cf7crud( $submission_id ) {

    intranet_fafar_logs_register_log( 'UPDATE CF7 SUBMISSION', $submission_id , 'Submission updated by CF7 form' );

}

function intranet_fafar_logs_create_log_from_api( $submission_id ) {

    intranet_fafar_logs_register_log( 'CREATE API', $submission_id , 'Submission created by internal API' );

}

function intranet_fafar_logs_register_log( $category, $source, $desc, $user = null ) {

    global $wpdb;

    $has_register_log_correct = true;

    $desc = sanitize_text_field( $desc );

    if ( ! $category ) {

        $has_register_log_correct = false;
        $desc .= '[Category not informed] ';
        $category = false;

    }

    if ( ! $source ) {

        $has_register_log_correct = false;
        $desc .= '[Source not informed] ';
        $source = false;
        
    }

    if ( ! $desc ) {

        $has_register_log_correct = false;
        $desc .= '[Desc not informed] ';
        
    }

    if ( $user ) {

        $desc .= '[User informed] ';
        
    } else {

        $user = get_current_user_id();

    }

    $table_name = $wpdb->prefix . 'fafar_cf7crud_submissions';
    /*
     * Generating unique hash for submission 'id'
     */
    $bytes             = random_bytes(5);
    $unique_hash       = time().bin2hex($bytes); 
    $form_post_id      = '-1';
    $form_data_as_json = stripslashes( json_encode( 
        array( 
            'category' => $category,
            'source' => $source,
            'desc' => $desc,
            'user' => $user,
            ) 
    ) );
    $id_wp_adm_user              = '1';
    $it_role_name                = 'informatica';
    /** 
     * Total access to owner(adm user) and users on 'informatica' role
     * */ 
    $log_permissions_code_access = '770';


    $wpdb->insert( $table_name, array(
        'id'          => $unique_hash,
        'data'        => $form_data_as_json,
        'object_name' => 'log',
        'form_id'     => $form_post_id,
        'owner'       => $id_wp_adm_user,
        'group_owner' => $it_role_name,
        'permissions' => $log_permissions_code_access,
    ) );

    return $has_register_log_correct;
}