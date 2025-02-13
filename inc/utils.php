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


function intranet_fafar_utils_get_all_roles() {

    global $wp_roles;

    // Ensure the $wp_roles object is loaded.
    if ( empty( $wp_roles ) ) {
        $wp_roles = new WP_Roles();
    }

    // Return an array of all roles.
    return $wp_roles->roles;

}

// Replace accented characters with their non-accented counterparts
function intranet_fafar_utils_remove_accents($str) {
    $unwanted = [
        'Á'=>'A', 'À'=>'A', 'Â'=>'A', 'Ä'=>'A', 'Ã'=>'A', 'Å'=>'A', 'Ā'=>'A', 'Ă'=>'A', 'Ą'=>'A',
        'á'=>'a', 'à'=>'a', 'â'=>'a', 'ä'=>'a', 'ã'=>'a', 'å'=>'a', 'ā'=>'a', 'ă'=>'a', 'ą'=>'a',
        'É'=>'E', 'È'=>'E', 'Ê'=>'E', 'Ë'=>'E', 'Ē'=>'E', 'Ĕ'=>'E', 'Ė'=>'E', 'Ę'=>'E', 'Ě'=>'E',
        'é'=>'e', 'è'=>'e', 'ê'=>'e', 'ë'=>'e', 'ē'=>'e', 'ĕ'=>'e', 'ė'=>'e', 'ę'=>'e', 'ě'=>'e',
        'Í'=>'I', 'Ì'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ĩ'=>'I', 'Ī'=>'I', 'Ĭ'=>'I', 'Į'=>'I', 'İ'=>'I',
        'í'=>'i', 'ì'=>'i', 'î'=>'i', 'ï'=>'i', 'ĩ'=>'i', 'ī'=>'i', 'ĭ'=>'i', 'į'=>'i', 'ı'=>'i',
        'Ó'=>'O', 'Ò'=>'O', 'Ô'=>'O', 'Ö'=>'O', 'Õ'=>'O', 'Ō'=>'O', 'Ŏ'=>'O', 'Ő'=>'O',
        'ó'=>'o', 'ò'=>'o', 'ô'=>'o', 'ö'=>'o', 'õ'=>'o', 'ō'=>'o', 'ŏ'=>'o', 'ő'=>'o',
        'Ú'=>'U', 'Ù'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ũ'=>'U', 'Ū'=>'U', 'Ŭ'=>'U', 'Ů'=>'U', 'Ű'=>'U', 'Ų'=>'U',
        'ú'=>'u', 'ù'=>'u', 'û'=>'u', 'ü'=>'u', 'ũ'=>'u', 'ū'=>'u', 'ŭ'=>'u', 'ů'=>'u', 'ű'=>'u', 'ų'=>'u',
        'Ý'=>'Y', 'Ÿ'=>'Y', 'Ŷ'=>'Y', 'ý'=>'y', 'ÿ'=>'y', 'ŷ'=>'y',
        'Ç'=>'C', 'Ć'=>'C', 'Ĉ'=>'C', 'Ċ'=>'C', 'Č'=>'C',
        'ç'=>'c', 'ć'=>'c', 'ĉ'=>'c', 'ċ'=>'c', 'č'=>'c',
        'Ñ'=>'N', 'Ń'=>'N', 'Ņ'=>'N', 'Ň'=>'N',
        'ñ'=>'n', 'ń'=>'n', 'ņ'=>'n', 'ň'=>'n',
        'Š'=>'S', 'Ś'=>'S', 'Ŝ'=>'S', 'Ş'=>'S',
        'š'=>'s', 'ś'=>'s', 'ŝ'=>'s', 'ş'=>'s',
        'Ž'=>'Z', 'Ź'=>'Z', 'Ż'=>'Z', 'Ž'=>'Z',
        'ž'=>'z', 'ź'=>'z', 'ż'=>'z', 'ž'=>'z',
        'Ð'=>'D', 'đ'=>'d',
        'Þ'=>'Th', 'þ'=>'th',
        'ß'=>'ss'
    ];
    return strtr($str, $unwanted);
}