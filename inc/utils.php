<?php
// Prevent direct access to the file.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function intranet_fafar_utils_render_dropdown_menu($args = []) {
    // Default arguments (following WordPress pattern)
    $defaults = [
        'name'           => '', // Prop da tag. Ex.: <select name="..."
        'id'             => '', // Prop da tag. Ex.: <select id="..."
        'selected'       => '', // Valor selecionado por padrão
        'class'          => '', // Prop da tag. Ex.: <select class="..."
        'options'        => [], // Dados para <options>
        'options_values' => [], // Dados para <options value"...">
        'placeholder'    => '', // Como padrão, não tem uma opção inicial com placeholder
    ];
    $args = wp_parse_args($args, $defaults); // Merge defaults with provided args

    // Apply filters to allow modifications
    $args = apply_filters('intranet_fafar_utils_render_dropdown_menu_args', $args);

    // Begin output buffering
    ob_start();

    // Start the select element
    printf(
        '<select name="%s" id="%s" class="%s">',
        esc_attr($args['name']),
        esc_attr($args['id']),
        esc_attr($args['class'])
    );

    // Add a placeholder option if provided
    if (!empty($args['placeholder'])) {
        echo '<option value="">' . esc_html($args['placeholder']) . '</option>';
    }

    // Checking for options custom values
    $values_passed_correctly = !empty( $args['options_values'] ) 
                               && is_array( $args['options_values'] ) 
                               && is_array( $args['options'] ) 
                               && count( $args['options_values'] ) === count( $args['options'] );

    if( ! $values_passed_correctly ) {
        // End the select element
        echo '</select>';

        return ob_get_clean(); // Return the output
    }

    // Add options
    foreach ( $args['options'] as $key => $option ) {
        printf(
            '<option value="%s" %s>%s</option>',
            esc_attr( $args['options_values'][$key] ),
            selected( $args['options_values'][$key], $args['selected'], false ), // Add "selected" attribute
            esc_html( $option )
        );
    }

    // End the select element
    echo '</select>';

    return ob_get_clean(); // Return the output
}

function intranet_fafar_utils_is_json( $data ) {
    if ( ! is_string( $data ) ) {
        return false;
    }

    json_decode( $data );
    
    return json_last_error() === JSON_ERROR_NONE;
}