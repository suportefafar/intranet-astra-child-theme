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

// Add this to your child theme's functions.php or a custom plugin
function modify_off_canvas_menu($menu_items, $args) {
    // Only target the off-canvas menu
    if ($args->theme_location === 'off_canvas') { // Change to your menu location
        // Modify menu items dynamically
        foreach ($menu_items as &$item) {
            // Example: Add a class to all items
            $item->classes[] = 'dynamic-class';
            
            // Example: Change specific item titles
            if ($item->title === 'Original Text') {
                $item->title = 'New Dynamic Text';
            }
        }
    }
    return $menu_items;
}
// add_filter('wp_nav_menu_objects', 'modify_off_canvas_menu', 10, 2);