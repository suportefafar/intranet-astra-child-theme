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

$tabs_metadata = array(
	array(
		'text' => 'Minhas',
		'url' => '/service_tickets/by_departament?assigned_to=-1&status=Nova,Aguardando,Em andamento'
	),
	array(
		'text' => 'Novas',
		'url' => '/service_tickets/by_departament?status=Nova'
	),
	array(
		'text' => 'Aguardando',
		'url' => '/service_tickets/by_departament?status=Aguardando'
	),
	array(
		'text' => 'Em andamento',
		'url' => '/service_tickets/by_departament?status=Em andamento'
	),
	array(
		'text' => 'Canceladas',
		'url' => '/service_tickets/by_departament?status=Cancelada'
	),
	array(
		'text' => 'Finalizadas',
		'url' => '/service_tickets/by_departament?status=Finalizada'
	),
	array(
		'text' => 'Todas',
		'url' => '/service_tickets/by_departament'
	),
);

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
		<a href="/filtro-avancado-de-ordem-de-servico/" class="btn btn-outline-secondary text-decoration-none"
			target="_blank">
			<i class="bi bi-funnel"></i>
			Filtro Avançado
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

	<ul id="ul_os_status_tabs" class="nav nav-tabs ms-0">

		<?php

		$first = true;

		foreach ( $tabs_metadata as $tab_metadata ) {

			echo '<li class="nav-item">
                                <a class="text-decoration-none nav-link ' . ( $first ? ' active' : '' ) . '"' .
				( $first ? ' aria-current="page"' : '' ) . ' 
                                href="#"  
                                data-url="' . $tab_metadata["url"] . '">' .
				$tab_metadata["text"] .
				' </a>
                            </li>';

			$first = false;

		}

		?>

	</ul>

	<br />


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