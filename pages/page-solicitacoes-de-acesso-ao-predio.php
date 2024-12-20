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
 * wp_enqueue_script( 'intranet-fafar-solicitacoes-de-acesso-ao-predio-script', get_stylesheet_directory_uri() . '/assets/js/solicitacoes-de-acesso-ao-predio.js', array( 'jquery' ), false, false );
 */
wp_enqueue_script_module( 'intranet-fafar-solicitacoes-de-acesso-ao-predio-script', get_stylesheet_directory_uri() . '/assets/js/solicitacoes-de-acesso-ao-predio.js', array( 'jquery' ), false, false );


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

        <!-- TABLES -->

        <div id="table-wrapper"></div>


        <!-- Modal visualizar evento -->
        <div class="modal fade" id="intranetFafarAccessRequestDetailsModal" tabindex="-1" aria-labelledby="intranetFafarAccessRequestDetailsModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="intranetFafarAccessRequestDetailsModalLabel">Detalhes</h1>
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
