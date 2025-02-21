<?php
/**
 * astra-intranet Theme functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package astra-intranet
 * @since 1.0.0
 */

/**
 * Define Constants
 */
define( 'CHILD_THEME_ASTRA_INTRANET_VERSION', '1.0.0' );

/**
 * Enqueue styles
 */
function child_enqueue_styles() {

	wp_enqueue_style( 'astra-intranet-theme-css', get_stylesheet_directory_uri() . '/style.css', array('astra-theme-css'), CHILD_THEME_ASTRA_INTRANET_VERSION, 'all' );

}

add_action( 'wp_enqueue_scripts', 'child_enqueue_styles', 15 );

/*
 * <<<<<<<<<<<<< START >>>>>>>>>>>
 * ADDED BY Setor de Suporte e T.I. 
 */

require_once get_stylesheet_directory() . '/inc/utils.php';

require_once get_stylesheet_directory() . '/inc/setup-on-activation.php';

require_once get_stylesheet_directory() . '/inc/rrule.php';

require_once get_stylesheet_directory() . '/inc/api-functions.php';

require_once get_stylesheet_directory() . '/inc/filters.php';

require_once get_stylesheet_directory() . '/inc/class-wp-bootstrap-navwalker.php';

require_once get_stylesheet_directory() . '/inc/enqueue-scripts.php';

require_once get_stylesheet_directory() . '/inc/shortcodes.php';

require_once get_stylesheet_directory() . '/inc/sidebar.php';

require_once get_stylesheet_directory() . '/inc/custom-user-fields.php';

require_once get_stylesheet_directory() . '/inc/template-tags.php';

//require_once get_stylesheet_directory() . '/inc/logs-hooks.php';

/*
 * Isso é uma gambiarra para lidar com um problema no CF7:
 * https://stackoverflow.com/questions/78101215/contact-form-undefined-value-was-submitted-through-this-field
 * 
 * Para mim, o problema no campo IP de um equipamento, no formulário de editar. 
 * Dava erro ao concluir a edição com o mesmo IP que já estava.
 * De tempos em tempos eu venho aqui para ver se já foi corrigido.
 * 
 * Re-visitado em: --/--/----
 */
remove_action( 'wpcf7_swv_create_schema', 'wpcf7_swv_add_select_enum_rules', 20, -1 );

/*
 * Adicionando checagem para criação de reservas
 */
add_filter( 'fafar_cf7crud_before_create', 'intranet_fafar_api_create_or_update_reservation', 10, 2 );

/*
 * Adicionando checagem para atualização de reservas
 */
add_filter( 'fafar_cf7crud_before_update', 'intranet_fafar_api_create_or_update_reservation', 10, 2 );

/*
 * Adicionando checagem para criação de empréstimos de equipamentos
 */
add_filter( 'fafar_cf7crud_before_create', 'intranet_fafar_api_create_new_loan', 10, 2 );

/*
 * Adicionando checagem para registrar o retorno de um empréstimo
 */
add_filter( 'fafar_cf7crud_before_update', 'intranet_fafar_api_register_loan_return', 10, 2 );

/*
 * Adicionando checagem para registrar atualização de ordem de serviço
 */
add_filter( 'fafar_cf7crud_before_create', 'intranet_fafar_api_insert_update_on_service_ticket', 10, 2 );