<?php
/**
 * Esse é um arquivo de template
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package Intranet Astra Child Theme
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

get_header(); ?>

<?php if ( astra_page_layout() == 'left-sidebar' ) : ?>

	<?php get_sidebar(); ?>

<?php endif ?>

	<div id="primary" <?php astra_primary_class(); ?>>

		<?php astra_primary_content_top(); ?>

        <?php astra_content_page_loop(); ?>

<!--
    *
    *
    *
    * Conteúdo customizado da página
    * Início
--> 
<?php

$new_reserve = array ( 
    'object_name' => 'reservation',
    'data' => array ( 
        'date'       => $pre_reservation['date'],
        'start_time' => $pre_reservation['start_time'],
        'end_time'   => $pre_reservation['end_time'],
        'frequency'  => ['once'],
        'capacity'   => 60,
    )
);

$places = intranet_fafar_api_get_reservable_places();

// Pre-filter valid candidates to minimize API calls
$required_capacity = $new_reserve['capacity'];
$filtered_places = array_filter ( $places, function( $place ) use ( $reservables, $required_capacity ) {
    // Check capacity requirement
    return ( $place['data']['capacity'] >= $required_capacity );
});

// Prepare common reservation data once
$common_data = array (
    'date' => $pre_reservation['date'],
    'start_time' => $pre_reservation['start_time'],
    'end_time' => $pre_reservation['end_time'],
    'frequency' => ['once'],
);

$availables = [];
foreach ($filtered_places as $place) {
    // Create reservation payload
    $payload = $common_data;
    $payload['place'] = [$place['id']];
    
    // Attempt to create reservation
    $response = intranet_fafar_api_create_or_update_reservation(array(
        'object_name' => 'reservation',
        'data' => json_encode($payload),
    ));

    // Collect available places without errors
    if (!isset($response['error_msg'])) {
        $availables[] = $place;
    }
}

$response = intranet_fafar_api_create_or_update_reservation(array(
    'object_name' => 'reservation',
    'data' => json_encode( $payload ),
));

?>    
<!--
    * Conteúdo customizado da página
    * Fim
    *
    *
    *
-->    

		<?php astra_primary_content_bottom(); ?>

	</div><!-- #primary -->

<?php if ( astra_page_layout() == 'right-sidebar' ) : ?>

	<?php get_sidebar(); ?>

<?php endif ?>

<?php get_footer(); ?>
