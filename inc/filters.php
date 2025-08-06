<?php
// Prevent direct access to the file.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Mudando o caminho dos arquivos template 'page-NOME_DA_PAGINA.php'
add_filter( 'page_template_hierarchy', 'custom_page_template_hierarchy' );

// Mudando o tempo de expiração de uma sessão para até o browser ser fechado 
add_filter( 'auth_cookie_expiration', 'set_session_to_browser_close' );

function custom_page_template_hierarchy( $templates ) {
	$new_templates = array();

	// Add custom directory for page templates
	foreach ( $templates as $template ) {
		$new_templates[] = 'pages/' . $template;
	}

	// Merge with default template hierarchy
	$new_templates = array_merge( $new_templates, $templates );

	return $new_templates;
}

function set_session_to_browser_close() {
	return YEAR_IN_SECONDS; // Session expires when the browser is closed
}