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
 * wp_enqueue_script( 'intranet-fafar-salas-script', get_stylesheet_directory_uri() . '/assets/js/salas.js', array( 'jquery' ), false, false );
 */
wp_enqueue_script_module( 'intranet-fafar-imprimir-mapa-de-sala-script', get_stylesheet_directory_uri() . '/assets/js/imprimir-mapa-de-sala.js', array( 'jquery' ), false, false );

$ID        = sanitize_text_field( wp_unslash( $_GET["id"] ) );

$classroom = intranet_fafar_api_get_submission_by_id( $ID );

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
    
<style>
    #calendar,
    .footer-container {
        width: 1080px !important;
    }

    .footer-container {
        margin-top: 0.5rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
    }

    @page {
        size: auto; /* auto is the initial value */
        margin: 0; /* this affects the margin in the printer settings */
    }
    @media print {

        * {
            -webkit-print-color-adjust: exact !important;   /* Chrome, Safari 6 – 15.3, Edge */
            color-adjust: exact !important;                 /* Firefox 48 – 96 */
            print-color-adjust: exact !important;           /* Firefox 97+, Safari 15.4+ */
        }

        #secondary,
        .fc-header-toolbar,
        .fc-toolbar,
        .fc-toolbar-ltr,
        .fc-toolbar-chunk,
        header {
            display: none !important;
        }

        #calendar {
            height: 456px !important;
        }

        #primary,
        #secondary {
            margin: 0;
            padding: 0;
        }

        div#content > div.ast-container {
            margin: 0;
            padding: 0;
        }

        /* Custom CSS to remove the current day highlight */
        .fc-day-today {
            background-color: transparent !important;
            color: inherit !important;
        }
 
    }
</style>

    <!-- TITLE -->

    <h4 class="mb-3"><?php echo 'Sala: ' . $classroom['data']['number'] . ' • Bloco: ' . $classroom['data']['block'] . ' • Andar: ' . $classroom['data']['floor'] . '.º'; ?></h4>

    <!-- CALENDER -->

    <div id="calendar"></div>

    <!-- FOOTER -->
    <div class="footer-container">
        <img src="<?php echo get_stylesheet_directory_uri() . '/assets/img/logo-dark.png'; ?>" alt="Logo FAFAR escuro">
        <span><?php echo 'Impresso: ' . gmdate('d/m/Y');?></span>
    </div>

        
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
