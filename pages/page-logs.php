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

$user = wp_get_current_user();
$role_slug = ( isset( $user->roles[0] ) ? $user->roles[0] : '' );

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
	<div class="d-flex justify-content-start gap-2 mb-4">
		<a href="/adicionar-sala" class="btn btn-outline-success text-decoration-none">
			<i class="bi bi-plus-lg"></i>
			Adicionar
		</a>
	</div>

	<!-- CHARTS -->

	<!--<div class="d-flex justify-content-around mb-4">
			<div class="card" style="width: 18rem;">intranet_fafar_importar_json
				<canvas id="myChart1"></canvas>
				<div class="card-body">
					<h5 class="card-title">Chart vs Mês</h5>
				</div>
			</div>

			<div class="card" style="width: 18rem;">
				<canvas id="myChart2"></canvas>
				<div class="card-body">
					<h5 class="card-title">Chart vs Ano</h5>
				</div>
			</div>

			<div class="card" style="width: 18rem;">
				<canvas id="myChart3"></canvas>
				<div class="card-body">
					<h5 class="card-title">Chart vs Setor</h5>
				</div>
			</div>
		</div>-->

	<!-- TABS -->

	<!--<ul class="nav nav-tabs">
			<li class="nav-item">
				<a class="nav-link active" aria-current="page" href="#">Active</a>
			</li>
			<li class="nav-item">
				<a class="nav-link" href="#">Link</a>
			</li>
			<li class="nav-item">
				<a class="nav-link" href="#">Link</a>
			</li>
			<li class="nav-item">
				<a class="nav-link disabled" aria-disabled="true">Disabled</a>
			</li>
		</ul>-->

	<!-- TABLES -->

	<div id="table-wrapper"></div>

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