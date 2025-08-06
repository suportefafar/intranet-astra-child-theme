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

	<ul id="ul_reservation_status_tabs" class="nav nav-tabs ms-0">
		<li class="nav-item">
			<a class="nav-link" href="#" data-reservation-status="Aguardando aprovação"
				data-reservation-status-slug="Aguardando aprovação" data-reservation-order-by="column:created_at"
				data-reservation-order-how="desc">
				Aguardando aprovação
				<span class="badge text-bg-light" id="badge_aguardando_aprovacao"
					data-reservation-status-slug="Aguardando aprovação"></span>
			</a>
		</li>
		<li class="nav-item">
			<a class="nav-link" href="#" data-reservation-status="Aguardando técnico"
				data-reservation-status-slug="Aguardando técnico" data-reservation-order-by="column:created_at"
				data-reservation-order-how="desc">
				Aguardando técnico
				<span class="badge text-bg-light" id="badge_aguardando_tecnico"></span>
			</a>
		</li>
		<li class="nav-item">
			<a class="nav-link" href="#" data-reservation-status="Aguardando início"
				data-reservation-status-slug="Aguardando início" data-reservation-order-by="json:event_date"
				data-reservation-order-how="asc">
				Aguardando início
				<span class="badge text-bg-light" id="badge_aguardando_inicio"></span>
			</a>
		</li>
		<li class="nav-item">
			<a class="nav-link" href="#" data-reservation-status="Finalizada" data-reservation-status-slug="Finalizada"
				data-reservation-order-by="json:event_date" data-reservation-order-how="desc">
				Finalizadas
				<span class="badge text-bg-light" id="badge_finalizada"></span>
			</a>
		</li>
		<li class="nav-item">
			<a class="nav-link" href="#" data-reservation-status="Todas" data-reservation-status-slug=""
				data-reservation-order-by="json:event_date" data-reservation-order-how="desc">
				Todas
				<span class="badge text-bg-light" id="badge_todas"></span>
			</a>
		</li>
	</ul>

	<br />

	<!-- TABLES -->
	<div id="table-wrapper"></div>

	<!-- Modal mostrar destalhes da reserva -->
	<div class="modal fade" id="intranetFafarReservationDetails" tabindex="-1"
		aria-labelledby="intranetFafarReservationDetailsLabel" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<h1 class="modal-title fs-5" id="intranetFafarReservationDetailsLabel">Detalhes</h1>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
					<table class="table table-borderless border-0">
						<tbody>
							<tr>
								<td class="text-body">Nome do evento</td>
								<td id="modal_event_title" class="text-body-emphasis fw-bold">--</td>
							</tr>
							<tr>
								<td class="text-body">Status</td>
								<td id="modal_event_status" class="text-body-emphasis fw-bold">--</td>
							</tr>
							<tr>
								<td class="text-body">Técnico</td>
								<td id="modal_event_technical" class="text-body-emphasis fw-bold">--</td>
							</tr>
							<tr>
								<td class="text-body">Solicitante</td>
								<td id="modal_event_applicant_name" class="text-body-emphasis fw-bold">--</td>
							</tr>
							<tr>
								<td class="text-body">Email do solicitante</td>
								<td id="modal_event_applicant_email" class="text-body-emphasis fw-bold">--</td>
							</tr>
							<tr>
								<td class="text-body">Telefone do solicitante</td>
								<td id="modal_event_applicant_phone" class="text-body-emphasis fw-bold">--</td>
							</tr>
							<tr>
								<td class="text-body">Notebook próprio</td>
								<td id="modal_event_use_own_notebook" class="text-body-emphasis fw-bold">--</td>
							</tr>
							<tr>
								<td class="text-body">Notebook da FAFAR</td>
								<td id="modal_event_use_fafar_notebook" class="text-body-emphasis fw-bold">--</td>
							</tr>
							<tr>
								<td class="text-body">Acesso à internet</td>
								<td id="modal_event_use_internet_access" class="text-body-emphasis fw-bold">--</td>
							</tr>
							<tr>
								<td class="text-body">Instrumentos musicais</td>
								<td id="modal_event_use_musical_instruments" class="text-body-emphasis fw-bold">--</td>
							</tr>
							<tr>
								<td class="text-body">Solicitado em</td>
								<td id="modal_event_created_at" class="text-body-emphasis fw-bold">--</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>

	<!-- Modal para escalar técnico para a reserva -->
	<div class="modal fade" id="intranetFafarSetTechnical" tabindex="-1"
		aria-labelledby="intranetFafarSetTechnicalLabel" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<h1 class="modal-title fs-5" id="intranetFafarSetTechnicalLabel">Técnico</h1>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
					<label for="selectSetTechnical" class="form-label">Selecione um técnico</label>
					<?php
					$technicals = intranet_fafar_api_get_users( array( 'role' => 'tecnologia_da_informacao_e_suporte' ) );

					//print_r($technicals);
					
					$technicals_id = array_map( function ($technical) {
						return $technical['ID'];
					}, $technicals );

					$technicals_name = array_map( function ($technical) {
						return $technical['display_name'];
					}, $technicals );

					echo intranet_fafar_utils_render_dropdown_menu(
						array(
							'options' => $technicals_name,
							'options_values' => $technicals_id,
							'name' => 'technical',
							'id' => 'selectSetTechnical',
							'placeholder' => 'Selecione...'
						)
					);
					?>
					<input type="hidden" id="reservation_id" value="" />
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
					<button type="button" class="btn btn-primary" id="btn_set_technical">Salvar</button>
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