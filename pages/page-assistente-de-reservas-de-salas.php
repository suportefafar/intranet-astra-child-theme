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
 * wp_enqueue_script_module( 'intranet-fafar-visualizar-equipamento-script', get_stylesheet_directory_uri() . '/assets/js/visualizar-equipamento.js', array( 'jquery' ), false, false );
 */
wp_enqueue_script_module( 'intranet-fafar-visualizar-reserva-script', get_stylesheet_directory_uri() . '/assets/js/visualizar-reserva.js', array( 'jquery' ), false, false );

function fafar_intranet_format_date_local( $dt ) {

    return DateTime::createFromFormat( 'Y-m-d', $dt )->format( 'd/m/Y' );

}

function fafar_intranet_get_frequency_display_text( $f ) {

    switch( $f ){

        case 'once':
            return 'Única';
        case 'daily':
            return 'Diáriamente';
        case 'weekly':
            return 'Semanalmente';
        case 'monthly':
            return 'Mensalmente';
        default:
            '--';
            
    }

}

function fafar_intranet_get_weekdays( $wds ) {

    $weekdays = array( 'Domingo', 
                       'Segunda', 
                       'Terça', 
                       'Quarta', 
                       'Quinta', 
                       'Sexta', 
                       'Sábado' );

    $weekdays_arr = array_map( function ( $wd ) use ($weekdays) {
        return $weekdays[$wd];
    }, $wds );

    return implode( ', ', $weekdays_arr );

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

        <form id="form-buscar-salas" class="mb-5">
            <div class="form-group mb-3">
                <label for="dia-do-evento">* Dia do Evento </label>
                <input type="date" 
                    class="form-control" 
                    id="dia-do-evento" 
                    name="dia_evento" 
                    min="2024-09-10" 
                    aria-required="true" 
                    required />
            </div>
            <div class="form-group mb-3">
                <label for="inicio-evento">* Início </label>
                <input type="time" 
                    class="form-control" 
                    id="inicio-evento" 
                    name="inicio_evento" 
                    aria-required="true" 
                    required />
            </div>
            <div class="form-group mb-3">
                <label for="fim-evento">* Fim </label>
                <input type="time" 
                    class="form-control" 
                    id="fim-evento" 
                    name="fim_evento" 
                    aria-required="true" 
                    required />
            </div>
            <div class="form-group mb-3">
                <label for="capacidade">* Capacidade </label>
                <input type="number" 
                    class="form-control" 
                    id="capacidade" 
                    name="capacidade" 
                    min="1" 
                    max="200" 
                    placeholder="20" 
                    aria-required="true" 
                    required />
            </div>
            <button type="submit" class="btn btn-outline-secondary">Buscar Salas</button>
        </form>

        <!-- TABLES -->

        <div id="table-wrapper" class="my-5 d-none"></div>
        
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
