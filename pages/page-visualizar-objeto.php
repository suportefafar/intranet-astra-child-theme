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

if ( ! isset( $_GET["id"] ) ) {
	echo '<pre> Nenhum ID informado. </pre>';
	return;
}

$ID = sanitize_text_field( wp_unslash( $_GET["id"] ) );

$submission = intranet_fafar_api_get_submission_by_id( $ID );

$prevent_write = isset( $submission['data']['prevent_write'] );


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
	<!-- HEADER BUTTONS -->

	<!--
		<div class="d-flex justify-content-start gap-2 mb-4">
			<a class="btn btn-outline-primary text-decoration-none w-button <?php echo ( $prevent_write ? 'disabled' : '' ) ?>" 
			   <?php echo ( $prevent_write ? 'aria-disabled="true"' : '' ) ?> 
			   <?php echo ( $prevent_write ? '' : 'href="/editar-disciplina/?id=' . $ID . '"' ) ?>  
			   title="Editar">
				<i class="bi bi-pencil"></i>
				Editar
			</a>
		</div>
		-->

	<br />
	<h5>Dados Brutos</h5>
	<table class="table border border-end-0 border-start-0">
		<tbody>
			<?php

			foreach ( $submission['data'] as $key => $value ) {

				if ( ! $value ||
					( is_array( $value ) && empty( $value ) ) )
					continue;

				echo '<tr>';
				echo '<td>' . str_replace( '_', ' ', ucwords( $key, '_' ) ) . '</td>';
				echo '<td class="fw-medium">' . ( is_array( $value ) ? implode( ', ', $value ) : $value ) . '</td>';
				echo '<tr>';
			}

			foreach ( $submission as $key => $value ) {

				if ( ! $value ||
					( is_array( $value ) && empty( $value ) ) )
					continue;

				if ( $key === 'data' )
					continue;

				echo '<tr>';
				echo '<td>' . str_replace( '_', ' ', ucwords( $key, '_' ) ) . '</td>';
				echo '<td class="fw-medium">' . $value . '</td>';
				echo '<tr>';
			}


			?>
		</tbody>
	</table>

	<?php
	$user_role = intranet_fafar_get_user_slug_role();
	if (
		$user_role === 'tecnologia_da_informacao_e_suporte' ||
		$user_role === 'administrator'
	) {
		echo '<h5 class="mt-5">Objeto PHP</h5>';
		echo '<pre>';
		print_r( $submission );
		echo '</pre>';
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