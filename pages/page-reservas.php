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
 * wp_enqueue_script( 'intranet-fafar-reservas-script', get_stylesheet_directory_uri() . '/assets/js/reservas.js', array( 'jquery' ), false, false );
 */
wp_enqueue_script_module( 'intranet-fafar-reservas-script', get_stylesheet_directory_uri() . '/assets/js/reservas.js', array( 'jquery' ), false, false );

$places = intranet_fafar_api_get_submissions_by_object_name( 'place', array( 'orderby_json' => 'number', 'order' => 'ASC' ) );

if ( isset( $places['msg_error'] ) )
    $places = array();

$classrooms = array_filter( $places, function ( $place ) {

    if( ! isset( $place['data']['object_sub_type'] ) )
        return false;

    return $place['data']['object_sub_type'] === 'classroom' || 
           ( 
             is_array( $place['data']['object_sub_type'] ) &&
             $place['data']['object_sub_type'][0] === 'classroom' 
           );
});

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
            <a href="/adicionar-reserva" class="btn btn-outline-success text-decoration-none w-button">
                <i class="bi bi-plus-lg"></i>
                Adicionar
            </a>
            <a href="/assistente-de-reservas-de-salas" class="btn btn-outline-warning text-decoration-none">
                <i class="bi bi-magic"></i>
                Assistente
            </a>
            <button id="btn_print_classroom_map" class="btn btn-outline-secondary">
                <i class="bi bi-printer"></i>
                Imprimir
            </button>
        </div>

        <!-- TABS -->

        <ul class="nav nav-tabs ms-0" id="ul_classroom_tabs">
            <?php
            
            $first = true;

            foreach ( $classrooms as $classroom ) {
            
                echo   '<li class="nav-item">
                            <a class="text-decoration-none nav-link ' . ( $first ? ' active' : '' ) . '"' . 
                            ( $first ? ' aria-current="page"' : '' ) . ' 
                            href="#"  
                            data-classroom-id="' . $classroom['id'] . '" 
                            data-classroom-number="' . $classroom['data']['number'] . '">' . 
                            $classroom['data']['number'] .
                            ' </a>
                        </li>';
            
                $first = false;
                
            }

            ?>
        </ul>

        <br />
        
        <!-- CALENDER -->

        <div id="calendar"></div>


        <!-- MODAL -->

        <!-- Modal visualizar evento -->
        <div class="modal fade" id="intranetFafarEventDetailsModal" tabindex="-1" aria-labelledby="intranetFafarEventDetailsModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="intranetFafarEventDetailsModalLabel">Detalhes</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <table class="table table-borderless border-0">
                            <tbody>
                                <tr>
                                    <td class="text-body">Título</td>
                                    <td id="modal_event_title" class="text-body-emphasis">--</td>
                                </tr>
                                <tr>
                                    <td class="text-body">Início</td>
                                    <td id="modal_event_start" class="text-body-emphasis">--</td>
                                </tr>
                                <tr>
                                    <td class="text-body">Fim</td>
                                    <td id="modal_event_end" class="text-body-emphasis">--</td>
                                </tr>
                                <tr>
                                    <td class="text-body">Solicitante</td>
                                    <td id="modal_event_applicant" class="text-body-emphasis">--</td>
                                </tr>
                                <tr>
                                    <td class="text-body">Dono</td>
                                    <td id="modal_event_owner" class="text-body-emphasis">--</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="modal-footer">
                        <a id="btn_event_details_info" class="btn btn-secondary text-decoration-none" href="#" target="_blank" title="Mais detalhes da reserva">
                            <i class="bi bi-info-lg"></i>
                            Mais
                        </a>
                        <a id="btn_event_details_edit" class="btn btn-primary text-decoration-none" href="#" title="Editar reserva">
                            <i class="bi bi-pencil"></i>
                            Editar
                        </a>
                        <button id="btn_event_details_delete" type="button" class="btn btn-danger" title="Excluir reserva" data-id>
                            <i class="bi bi-trash"></i>
                            Excluir
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal adicionar reserva -->
        <div class="modal fade" id="intranetFafarAddEvent" tabindex="-1" aria-labelledby="intranetFafarAddEventLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="intranetFafarAddEventLabel">Adicionar Reserva</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <?php 
                            echo do_shortcode( '[contact-form-7 id="3b3fab8" title="Adicionar Reserva"]' );
                        ?>
                    </div>
                </div>
            </div>
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
