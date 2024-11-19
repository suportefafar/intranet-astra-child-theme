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
wp_enqueue_script_module( 'intranet-fafar-minhas-ordens-de-servico-script', get_stylesheet_directory_uri() . '/assets/js/minhas-ordens-de-servico.js', array( 'jquery' ), false, false );


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
            <a href="/adicionar-ordem-de-servico" class="btn btn-outline-success text-decoration-none w-button">
                <i class="bi bi-plus-lg"></i>
                Adicionar
            </a>
        </div>

        <!-- TABLES -->

        <div id="table-wrapper"></div>

        <!-- MODAL -->
        
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