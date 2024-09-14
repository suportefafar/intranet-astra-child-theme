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
 *	
 *	
 *	
 *	
 *	
 *	
 *	
 *	
 *	
 *	    <<<<<<<<<<<<< START >>>>>>>>>>>
 *		ADDED BY Setor de Suporte e T.I. 
*/

require_once "import-scripts.php";

//require_once "api.php";

require_once "shortcodes.php";

//require_once "logs.php";


//add_filter( 'fafar_cf7crud_before_create', 'intranet_fafar_api_is_place_available_for_class_event', 10, 3 );


require_once 'class-wp-bootstrap-navwalker.php';

