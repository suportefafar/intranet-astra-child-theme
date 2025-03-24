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

$tabs_metadata = array( 
    array(
        'text' => 'Todas',
        'url' => '/service_tickets/by_departament?assigned_to=-1&status=Nova,Aguardando,Em andamento'
    ),
    array(
        'text' => 'Entrada',
        'url' => '/service_tickets/by_departament?status=Nova'
    ),
    array(
        'text' => 'Pendente',
        'url' => '/service_tickets/by_departament?status=Aguardando'
    ),
    array(
        'text' => 'Saída',
        'url' => '/service_tickets/by_departament?status=Em andamento'
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
        <!-- TABS -->

        <ul id="ul_os_status_tabs" class="nav nav-tabs ms-0">

        <?php

            $first = true;

            foreach ( $tabs_metadata as $tab_metadata ) {
            
                echo   '<li class="nav-item">
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


        <!-- Modal visualizar evento -->
        <div class="modal fade" id="intranetFafarAccessBuildingRequestDetailsModal" tabindex="-1" aria-labelledby="intranetFafarAccessBuildingRequestDetailsModalLabel" aria-hidden="true">
            <!-- Scrollable modal -->
            <div class="modal-dialog modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="intranetFafarAccessBuildingRequestDetailsModalLabel">Detalhes</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <table class="table table-borderless border-0">
                            <tbody>
                                <tr>
                                    <td class="text-body">Solicitado em</td>
                                    <td id="access_building_request_created_at" class="text-body-emphasis fw-semibold">--</td>
                                </tr>
                                <tr>
                                    <td class="text-body">Válido de</td>
                                    <td id="access_building_request_start_date" class="text-body-emphasis fw-semibold">--</td>
                                </tr>
                                <tr>
                                    <td class="text-body">Válido até</td>
                                    <td id="access_building_request_end_date" class="text-body-emphasis fw-semibold">--</td>
                                </tr>
                                <tr>
                                    <td class="text-body">Dono</td>
                                    <td id="access_building_request_owner" class="text-body-emphasis fw-semibold">--</td>
                                </tr>
                                <tr>
                                    <td class="text-body">Tipo</td>
                                    <td id="access_building_request_type" class="text-body-emphasis fw-semibold">--</td>
                                </tr>
                                <tr>
                                    <td class="text-body">Nome do Terceiro</td>
                                    <td id="access_building_request_third_party_name" class="text-body-emphasis fw-semibold">--</td>
                                </tr>
                                <tr>
                                    <td class="text-body">Setor de Origem do Terceiro</td>
                                    <td id="access_building_request_third_party_sector" class="text-body-emphasis fw-semibold">--</td>
                                </tr>
                                <tr>
                                    <td class="text-body">Local</td>
                                    <td id="access_building_request_place" class="text-body-emphasis fw-semibold">--</td>
                                </tr>
                                <tr>
                                    <td class="text-body">Justificativa</td>
                                    <td id="access_building_request_justification" class="text-body-emphasis fw-semibold">--</td>
                                </tr>
                            </tbody>
                        </table>

                        <br />

                        <table id="access_building_request_logs" class="table border-start-0">
                            <thead>
                                <tr>
                                    <th scope="col">Data/Hora</th>
                                    <th scope="col">Ação</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
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
