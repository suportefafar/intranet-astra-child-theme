<?php
// Prevent direct access to the file.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/*
 * Mudando o caminho dos arquivos template 'page-NOME_DA_PAGINA.php'
 */
add_filter( 'page_template_hierarchy', 'custom_page_template_hierarchy' );


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
