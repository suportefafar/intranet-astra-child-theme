<?php
// Prevent direct access to the file.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Hook to enqueue styles
add_action( 'wp_enqueue_scripts', 'intranet_fafar_enqueue_scripts_styles' );

// Hook to enqueue scripts
add_action( 'wp_enqueue_scripts', 'intranet_fafar_conditional_scripts' );

// Enqueue theme scripts and styles with proper dependency management
function intranet_fafar_enqueue_scripts_styles() {
	// Enqueue styles
	wp_enqueue_style(
		'bootstrap',
		'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css',
		array(),
		'5.3.2'
	);

	wp_enqueue_style(
		'bootstrap-icons',
		'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css',
		array(),
		'1.11.3'
	);

	wp_enqueue_style(
		'gridjs',
		'https://cdn.jsdelivr.net/npm/gridjs/dist/theme/mermaid.min.css',
		array(),
		'6.2.0'
	);

	// Enqueue scripts with proper dependencies
	wp_enqueue_script(
		'bootstrap-bundle',
		'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js',
		array(),
		'5.3.2',
		true
	);

	wp_enqueue_script(
		'axios',
		'https://unpkg.com/axios/dist/axios.min.js',
		array(),
		null,
		true
	);

	wp_enqueue_script(
		'rrule',
		'https://cdn.jsdelivr.net/npm/rrule@2.6.4/dist/es5/rrule.min.js',
		array(),
		'2.6.4',
		true
	);

	wp_enqueue_script(
		'fullcalendar',
		'https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js',
		array( 'rrule' ),
		'6.1.15',
		true
	);

	wp_enqueue_script(
		'fullcalendar-locales-all',
		'https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.15/locales-all.global.min.js',
		array( 'rrule' ),
		'6.1.15',
		true
	);

	wp_enqueue_script(
		'fullcalendar-rrule',
		'https://cdn.jsdelivr.net/npm/@fullcalendar/rrule@6.1.15/index.global.min.js',
		array( 'rrule' ),
		'6.1.15',
		true
	);

	wp_enqueue_script(
		'chartjs',
		'https://cdn.jsdelivr.net/npm/chart.js',
		array(),
		'4.4.0',
		true
	);

	wp_enqueue_script(
		'sweetalert2',
		'https://cdn.jsdelivr.net/npm/sweetalert2@11.14.4/dist/sweetalert2.all.min.js',
		array(),
		'11.14.4',
		true
	);

	wp_enqueue_script(
		'gridjs',
		'https://cdn.jsdelivr.net/npm/gridjs@6.2.0/dist/gridjs.umd.js',
		array(),
		'6.2.0',
		true
	);

	// Local scripts
	wp_enqueue_script(
		'intranet-fafar-utils',
		get_stylesheet_directory_uri() . '/assets/js/utils.js',
		array( 'jquery' ),
		filemtime( get_stylesheet_directory() . '/assets/js/utils.js' ),
		true
	);

	// Bootstrap components
	$bootstrap_components = array(
		'alert' => '/assets/js/alert.js',
		'confirm-modal' => '/assets/js/confirm-modal.js',
		'toast' => '/assets/js/toast.js'
	);

	foreach ( $bootstrap_components as $handle => $path ) {
		wp_enqueue_script(
			"intranet-fafar-$handle",
			get_stylesheet_directory_uri() . $path,
			array( 'jquery', 'bootstrap-bundle' ),
			filemtime( get_stylesheet_directory() . $path ),
			true
		);
	}

	/**
	 * Esse arquivo é uma gambiarra porque acredito que o ideal 
	 * era ter essa funcionalidade na página de edição do usuário 
	 * dentro da área de administração do WP.
	 */
	if ( bp_is_user() ) {
		wp_enqueue_script(
			'intranet-fafar-perfil-bp',
			get_stylesheet_directory_uri() . '/assets/js/perfil-bp.js',
			array( 'jquery' ),
			filemtime( get_stylesheet_directory() . '/assets/js/perfil-bp.js' ),
			true
		);
	}
}

/**
 * Handle conditional script loading based on pages
 */
function intranet_fafar_conditional_scripts() {
	// Only proceed if we're on a singular page
	if ( ! is_singular() )
		return;

	// Get current page/post slug
	$slug = get_post_field( 'post_name', get_queried_object_id() );

	// Set paths
	$script_root_path = '/assets/js/';
	$relative_script_path = $script_root_path . $slug . '.js';
	$full_script_path = get_stylesheet_directory() . $relative_script_path;

	// Check if file exists and enqueue
	if ( file_exists( $full_script_path ) ) {
		error_log( "intranet-fafar-{$slug}" );
		wp_enqueue_script(
			"intranet-fafar-{$slug}",
			get_stylesheet_directory_uri() . $relative_script_path,
			array( 'jquery', 'gridjs' ),
			filemtime( $full_script_path ),
			true
		);
	}
}