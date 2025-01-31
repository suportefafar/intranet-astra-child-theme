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
wp_enqueue_script_module( 'intranet-fafar-reservas-do-auditorio-script', get_stylesheet_directory_uri() . '/assets/js/reservas-do-auditorio.js', array( 'jquery' ), false, false );


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

        <!-- TABS -->

        <ul id="ul_os_status_tabs" class="nav nav-tabs ms-0">
            <li class="nav-item">
                <a class="nav-link active" aria-current="page" href="#" data-os-status="badge_aguardando_aprovacao">
                    Aguardando aprovação
                    <span class="badge text-bg-light" id="badge_aguardando_aprovacao"></span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#" data-os-status="em badge_aguardando_tecnicos">
                    Aguardando técnicos
                    <span class="badge text-bg-light" id="badge_aguardando_tecnicos"></span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#" data-os-status="badge_aguardando_inicio">
                    Aguardando início
                    <span class="badge text-bg-light" id="badge_aguardando_inicio"></span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#" data-os-status="badge_finalizadas">
                    Finalizadas
                    <span class="badge text-bg-light" id="badge_finalizadas"></span>
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
