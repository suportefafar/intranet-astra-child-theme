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
 * wp_enqueue_script_module( 'intranet-fafar-visualizador-equipamento-script', get_stylesheet_directory_uri() . '/assets/js/visualizador-equipamento.js', array( 'jquery' ), false, false );
 */
wp_enqueue_script_module( 'intranet-fafar-visualizar-ordem-de-servico-script', get_stylesheet_directory_uri() . '/assets/js/visualizar-ordem-de-servico.js', array( 'jquery' ), false, false );


if( ! isset( $_GET["id"] ) ) {
    echo '<pre> Nenhum ID informado. </pre>';
    return;
}

function fafar_intranet_format_date_local( $datetime ) {

    // Create a DateTime object with the input string, assuming it's in UTC
    $date = new DateTime( $datetime, new DateTimeZone( 'UTC' ) );

    // Change the timezone to GMT-3 (America/Sao_Paulo)
    $date->setTimezone( new DateTimeZone( 'America/Sao_Paulo' ) );

    return $date->format( 'd/m/Y, H:i:s' );

}

function fafar_intranet_get_status_badge( $status ) {

    $type          = "text-bg-info";
    $current_lower = strtolower($status);

    if ( $current_lower === "nova" ) {

        $type = "text-bg-success";

    } elseif ($current_lower === "aguardando") {

        $type = "text-bg-warning";

    } elseif ( $current_lower === "em andamento" ) {
        
        $type = "text-bg-primary";

    } elseif ( $current_lower === "finalizada" ){

        $type = "text-bg-secondary";

    } elseif ( $current_lower === "cancelada" ) {

        $type = "text-bg-danger";

    }

    return sprintf('<span class="badge %s">%s</span>', $type, esc_html($status));
}

$ID             = sanitize_text_field( wp_unslash( $_GET["id"] ) );

$service_ticket = intranet_fafar_api_get_service_ticket_by_id( $ID );

$updates        = intranet_fafar_api_get_service_ticket_updates_by_service_ticket( $ID );

$user           = wp_get_current_user();

$role_slug      = $user->roles[0];

$service_ticket_departament_role_slug = $service_ticket['data']['departament_assigned_to']['role_slug'];

$prevent_insert_update = ( ! isset( $service_ticket_departament_role_slug ) || 
                           $role_slug !== $service_ticket_departament_role_slug );

$prevent_write = isset( $service_ticket['data']['prevent_write'] );

//print_r($updates);

if ( isset( $updates['error_msg'] ) )
    $updates = array();

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
            <a class="btn btn-outline-primary text-decoration-none w-button <?php echo ( $prevent_write ? 'disabled' : '' ) ?>" 
                <?php 
                    echo ( 
                            $prevent_write ? 
                                'aria-disabled="true"'
                                : 
                                'href="/editar-ordem-de-servico/?id=' . $ID . '"'
                        ) 
                ?>  
                title="Editar" >
                <i class="bi bi-pencil"></i>
                Editar
            </a>
            <a class="btn btn-outline-danger text-decoration-none w-button <?php echo ( $prevent_write ? 'disabled' : '' ) ?>" 
                <?php 
                    echo ( 
                            $prevent_write ? 
                                'aria-disabled="true"'
                                : 
                                'id="btn_delete"' . 
                                'data-id="' . $ID . '"'
                        ) 
                ?>
                title="Excluir" >
                <i class="bi bi-trash"></i>
                Excluir
            </a>
            <?php  
            if ( ! $prevent_insert_update ) :
            ?>
                <button 
                    class="btn btn-info"
                    id="btn_insert_update" 
                    data-id="<?php echo $ID; ?>" 
                    title="Inserir Atualização" >
                        <i class="bi bi-node-plus"></i>
                        Atualizar
                </button>
            <?php
            endif;
            ?>
        </div>

        <div class="container-fluid p-0">
            <div class="row">
                <div class="col-12">
                    <div class="px-2 py-2 border-bottom border-dark">
                        <h5 class="fw-bold p-0 m-0"> Informações </h5>
                    </div>
                    <table class="table table-borderless border border-0">
                        <tbody>
                            <tr>
                                <td>Criado</td>
                                <td class="fw-medium">
                                <?php 
                                    echo fafar_intranet_format_date_local( $service_ticket['created_at'] ); 
                                ?>
                                </td>
                            <tr>
                            <tr>
                                <td>Atualizado</td>
                                <td class="fw-medium">
                                <?php 
                                    echo fafar_intranet_format_date_local( $service_ticket['updated_at'] ); 
                                ?>
                                </td>
                            <tr>
                            <tr>
                                <td>Código</td>
                                <td class="fw-medium">
                                <?php 
                                        echo ( 
                                            isset( $service_ticket['data']['code'] ) ? 
                                                $service_ticket['data']['code'] : 
                                                '' 
                                            ) 
                                    ?>
                                </td>
                            <tr>
                            <tr>
                                <td>Status</td>
                                <td class="fw-medium">
                                <?php 
                                        echo ( 
                                            isset( $service_ticket['data']['status'] ) ? 
                                                fafar_intranet_get_status_badge( $service_ticket['data']['status'] ) : 
                                                '' 
                                            ) 
                                    ?>
                                </td>
                            <tr>
                            <tr>
                                <td>Responsável</td>
                                <td class="fw-medium">
                                <?php 
                                echo ( 
                                    ( $service_ticket['owner']['data'] ) ?
                                    '<a href="/membros/' . $service_ticket['owner']['data']->user_login . '" target="_blank" title="Perfil de ' . $service_ticket['owner']['data']->display_name . '">' .
                                       $service_ticket['owner']['data']->display_name .
                                    '</a>' : 
                                    '' 
                                    ) 
                                ?>
                                </td>
                            <tr>
                            <tr>
                                <td>Departamento</td>
                                <td class="fw-medium">
                                    <?php 
                                        echo ( 
                                            isset( $service_ticket['data']['departament_assigned_to']['role_display_name'] ) ? 
                                                $service_ticket['data']['departament_assigned_to']['role_display_name'] : 
                                                '' 
                                            ) 
                                    ?>
                                </td>
                            <tr>
                            <tr>
                                <td>Tipo</td>
                                <td class="fw-medium">
                                    <?php 
                                        echo ( 
                                            isset( $service_ticket['data']['type'][0] ) ? 
                                                $service_ticket['data']['type'][0] : 
                                                '' 
                                            ) 
                                    ?>
                                </td>
                            <tr>
                            <tr>
                                <td>Patrimônio</td>
                                <td class="fw-medium">
                                    <?php 
                                        echo ( 
                                            isset( $service_ticket['data']['asset'] ) ? 
                                                $service_ticket['data']['asset'] : 
                                                '' 
                                            ) 
                                    ?>
                                </td>
                            <tr>
                            <tr>
                                <td>Relato</td>
                                <td class="fw-medium">
                                    <?php 
                                        echo ( 
                                            isset( $service_ticket['data']['user_report'] ) ? 
                                                $service_ticket['data']['user_report'] : 
                                                '' 
                                            ) 
                                    ?>
                                </td>
                            <tr>
                            <tr>
                                <td>Local</td>
                                <td class="fw-medium">
                                    <?php 
                                        echo ( 
                                                ( isset( $service_ticket['data']['place'] ) && sizeof( $service_ticket['data']['place'] ) ) ?
                                                    '<a 
                                                        href="./visualizar-objeto?id=' . $service_ticket['data']['place']['id'] . '" 
                                                        target="blank" 
                                                        title="Detalhes da sala"
                                                    >' .
                                                    $service_ticket['data']['place']['data']['number'] . 
                                                    " - Andar: " . 
                                                    $service_ticket['data']['place']['data']['floor'] .
                                                    "ª" . 
                                                    " - Bloco: " . 
                                                    $service_ticket['data']['place']['data']['block'] : '' . 
                                                    '</a>'
                                            ) 
                                    ?>
                                </td>
                            <tr>
                            <tr>
                                <td>Prestador</td>
                                <td class="fw-medium">
                                    <select id="select_assigned_to" class="form-select" aria-label="Selecionador para prestador" <?php echo ( ( $prevent_insert_update ) ? "disabled" : "" ); ?> >
                                    <?php
                                        if ( ! isset( $service_ticket['data']['assigned_to'] ) ) $service_ticket['data']['assigned_to'] = 0;

                                        $users_by_departament = intranet_fafar_get_user_by_departament( $service_ticket_departament_role_slug );
                                    ?>

                                        <option value="0" <?php selected( strval( $service_ticket['data']['assigned_to'] ), 0 ); ?> >Selecione um</option>
                                        
                                    <?php

                                        foreach ( $users_by_departament as $key => $value  ): 
                                    ?>

                                        <option value="<?= $key ?>" <?php selected( strval( $service_ticket['data']['assigned_to'] ), strval( $key ) ); ?> ><?= $value ?></option>

                                    <?php 
                                        endforeach; 
                                    ?>
                                    </select>
                                </td>
                            <tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="px-2 py-2 border-bottom border-dark">
                        <h5 class="fw-bold p-0 m-0"> Histórico de Atualizações </h5>
                    </div>
                    <table class="table table-borderless border border-0">
                        <tbody>
                        <?php
                            foreach ( $updates as $update ):
                        ?>
 
                                <tr>
                                    <td>Inserido em</td>
                                    <td class="fw-medium"><?php echo fafar_intranet_format_date_local( $update['created_at'] ); ?></td>
                                <tr>
                                <tr>
                                    <td>Prestador</td>
                                    <td class="fw-medium"><?php echo $update['owner']['data']->display_name; ?></td>
                                <tr>
                                <tr>
                                    <td>Relatório</td>
                                    <td class="fw-medium"><?php echo $update['data']['service_report']; ?></td>
                                <tr>
                                <tr>
                                    <td>Status</td>
                                    <td class="fw-medium"><?php echo fafar_intranet_get_status_badge( $update['data']['status'][0] ); ?></td>
                                <tr>
                                <tr>
                                    <td class="border-top"></td>
                                    <td class="border-top"></td>
                                <tr>
                                
                            <?php
                                endforeach;
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php

            $user_role = intranet_fafar_get_user_slug_role();
            if ( $user_role === 'ti' || $user_role === 'administrator' ) {
                echo '<h5 class="mt-5">Objeto PHP</h5>';
                echo '<pre>';
                    print_r( $service_ticket );
                echo '</pre>';
                echo '<br />';
                echo '<pre>';
                    print_r( $updates );
                echo '</pre>';
            }

        ?>


        <!-- Modal para inserir atualização na O.S. -->
        <div class="modal fade" id="intranetFafarInsertServiceTicketUpdate" tabindex="-1" aria-labelledby="intranetFafarInsertServiceTicketUpdateLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="intranetFafarInsertServiceTicketUpdateLabel">Atualizar Ordem de Serviço</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?php
                        echo do_shortcode( '[contact-form-7 id="0a46270" title="Inserir Atualização em Ordem de Serviço"]' );
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
