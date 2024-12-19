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


/*
 * Importanto script JS customizado
 * wp_enqueue_script( 'intranet-fafar-equipamentos-script', get_stylesheet_directory_uri() . '/assets/js/equipamentos.js', array( 'jquery' ), false, false );
 */
wp_enqueue_script_module( 'intranet-fafar-ordens-de-servico-recebidas-script', get_stylesheet_directory_uri() . '/assets/js/ordens-de-servico-recebidas.js', array( 'jquery' ), false, false );


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
            <li class="nav-item">
                <a class="nav-link active" aria-current="page" href="#" data-os-status="minha">
                    Minhas
                    <span class="badge text-bg-light" id="badge_novas"></span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#" data-os-status="nova">
                    Novas
                    <span class="badge text-bg-light" id="badge_novas"></span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#" data-os-status="aguardando">
                    Aguardando
                    <span class="badge text-bg-light" id="badge_aguardando"></span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#" data-os-status="em andamento">
                    Em Andamento
                    <span class="badge text-bg-light" id="badge_em_andamento"></span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#" data-os-status="finalizada">
                    Finalizadas
                    <span class="badge text-bg-light" id="badge_finalizadas"></span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#" data-os-status="cancelada">
                    Canceladas
                    <span class="badge text-bg-light" id="badge_canceladas"></span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#" data-os-status="todas">
                    Todas
                    <span class="badge text-bg-light" id="badge_todas"></span>
                </a>
            </li>
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
