<?php
// Prevent direct access to the file.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Hook to enqueue scripts and styles.
add_action( 'wp_enqueue_scripts', 'intranet_fafar_enqueue_scripts_styles' );
add_action( 'wp_head', 'add_header_custom_scripts' );
add_action( 'wp_footer', 'add_footer_custom_scripts' );
/* 
 * Carregando conteúdo extra com base na página
 */
add_action( 'wp_footer', 'add_footer_custom_scripts_by_page' );

/*
 * Importanto script JS do Bootstrap Alert
 */
add_action( 'wp_enqueue_scripts', function () {

	wp_enqueue_script( 'intranet-fafar-alert', get_stylesheet_directory_uri() . '/assets/js/alert.js', array( 'jquery' ), false, true );

} );

/*
 * Importanto script JS do Bootstrap Modal de confirmação
 */
add_action( 'wp_enqueue_scripts', function () {

	wp_enqueue_script( 'intranet-fafar-confirm-modal', get_stylesheet_directory_uri() . '/assets/js/confirm-modal.js', array( 'jquery' ), false, true );

} );

/*
 * Importanto script JS do Bootstrap Toast
 */
add_action( 'wp_enqueue_scripts', function () {

	wp_enqueue_script( 'intranet-fafar-toast', get_stylesheet_directory_uri() . '/assets/js/toast.js', array( 'jquery' ), false, true );

} );


function add_header_custom_scripts() {

	?>
		<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous" />
		<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" />
        <link href="https://unpkg.com/gridjs/dist/theme/mermaid.min.css" rel="stylesheet" />
		<script src='https://cdn.jsdelivr.net/npm/rrule@2.6.4/dist/es5/rrule.min.js'></script>
		<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js'></script>
		<script src="https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.15/locales-all.global.min.js"></script>
		<script src='https://cdn.jsdelivr.net/npm/@fullcalendar/rrule@6.1.15/index.global.min.js'></script>
		<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.14.4/dist/sweetalert2.min.css" rel="stylesheet" />
	<?php

}

function add_footer_custom_scripts() {

	?>
		<script src="https://unpkg.com/axios/dist/axios.min.js"></script>
		<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script src="https://unpkg.com/gridjs/dist/gridjs.umd.js"></script>
		<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.14.4/dist/sweetalert2.all.min.js"></script>
	<?php

}

function add_footer_custom_scripts_by_page() {

	if( is_page( "logs" ) ){
		echo '<script type="module" src="' . get_stylesheet_directory_uri() . '/assets/js/logs.js"></script>';
	}

	if( is_page( "assistente-de-reservas-de-salas" ) ) {
		echo '<script type="module" src="' . get_stylesheet_directory_uri() . '/assets/js/assistente-de-reservas-de-salas.js"></script>';
	}

}

/*
 * Enqueue theme scripts and styles.
 */
function intranet_fafar_enqueue_scripts_styles() {

    // Enqueue styles.
    wp_enqueue_style(
        'mytheme-main-style', // Handle for the stylesheet.
        get_stylesheet_uri(), // URL to the theme's style.css.
        array(), // Dependencies (none in this case).
        wp_get_theme()->get( 'Version' ) // Version number for cache busting.
    );

    wp_enqueue_style(
        'mytheme-custom-style', // Handle for the custom stylesheet.
        get_template_directory_uri() . '/assets/css/custom.css', // Path to custom.css.
        array( 'mytheme-main-style' ), // Dependencies.
        '1.0.0' // Version number for cache busting.
    );

    // Enqueue scripts.
    wp_enqueue_script(
        'mytheme-main-script', // Handle for the script.
        get_template_directory_uri() . '/assets/js/main.js', // Path to main.js.
        array( 'jquery' ), // Dependencies (e.g., jQuery).
        '1.0.0', // Version number for cache busting.
        true // Load in the footer.
    );

    // Enqueue a script with inline data.
    wp_enqueue_script(
        'mytheme-ajax-script',
        get_template_directory_uri() . '/assets/js/ajax-handler.js',
        array( 'jquery' ),
        '1.0.0',
        true
    );

    // Pass data to the script.
    wp_localize_script(
        'mytheme-ajax-script',
        'mytheme_ajax_object',
        array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'mytheme_nonce' ),
        )
    );
    
}
