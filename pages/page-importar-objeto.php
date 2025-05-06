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

	$building_requests = intranet_fafar_api_read(
		args: array(
			'filters' => array(
				array(
					'column' => 'object_name',
					'value' => 'access_building_request',
					'operator' => '=',
				),
			),
			'order_by' => array(
				'orderby_column' => 'created_at',
				'order' => 'DESC',
			),
		)
	);

	foreach ( $building_requests['data'] as $building_request ) {
		$status = 'pending';

		if ( ! empty( $building_request['data']['logs'] ) ) {
			$last_index = count( $building_request['data']['logs'] ) - 1;
			$status = $building_request['data']['logs'][ $last_index ]['type'];
		}

		print_r( $building_request['data']['status'] );
		print_r( '<br/><br/>' );
	}

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