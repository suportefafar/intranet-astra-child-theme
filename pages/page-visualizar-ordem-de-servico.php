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

if ( ! isset( $_GET["id"] ) ) {
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

	$type = "text-bg-info";
	$current_lower = strtolower( $status );

	if ( $current_lower === "nova" ) {

		$type = "text-bg-success";

	} elseif ( $current_lower === "aguardando" ) {

		$type = "text-bg-warning";

	} elseif ( $current_lower === "em andamento" ) {

		$type = "text-bg-primary";

	} elseif ( $current_lower === "finalizada" ) {

		$type = "text-bg-secondary";

	} elseif ( $current_lower === "cancelada" ) {

		$type = "text-bg-danger";

	}

	return sprintf( '<span class="badge %s">%s</span>', $type, esc_html( $status ) );
}

$ID = sanitize_text_field( wp_unslash( $_GET["id"] ) );

$service_ticket = intranet_fafar_api_get_service_ticket_by_id( $ID );

$updates = intranet_fafar_api_get_service_ticket_updates_by_service_ticket( $ID );

$user = wp_get_current_user();

$role_slug = $user->roles[0];

$service_ticket_departament_role_slug = $service_ticket['data']['departament_assigned_to']['role_slug'];

$prevent_insert_update = ( ! isset( $service_ticket_departament_role_slug ) ||
	$role_slug !== $service_ticket_departament_role_slug );

$prevent_write = isset( $service_ticket['data']['prevent_write'] );

$service_evaluations = intranet_fafar_api_get_service_ticket_evaluation_by_id( $ID );

if ( isset( $updates['error_msg'] ) )
	$updates = array();


// Obtendo IDs da OS anterior e próxima
$service_tickets = intranet_fafar_api_read(
	args: array(
		'filters' => array(
			array(
				'column' => 'object_name',
				'value' => 'service_ticket',
				'operator' => '=',
			),
			array(
				'column' => 'data->departament_assigned_to',
				'value' => '["' . $service_ticket_departament_role_slug . '"]',
				'operator' => '=',
			),
			array(
				'column' => 'data->status',
				'value' => $service_ticket['data']['status'],
				'operator' => '=',
			),
		),
		'order_by' => array(
			'orderby_column' => 'created_at',
			'order' => 'ASC',
		),
	)
);

$previous_ticket_service_url = '#';
$next_ticket_service_url = '#';
foreach ( $service_tickets['data'] as $index => $ticket ) {
	if ( $ticket['id'] === $ID ) {
		if ( ! empty( $service_tickets['data'][ $index - 1 ]['id'] ) ) {
			$previous_ticket_service_url = '/visualizar-ordem-de-servico/?id=' . $service_tickets['data'][ $index - 1 ]['id'];
		}

		if ( ! empty( $service_tickets['data'][ $index + 1 ]['id'] ) ) {
			$next_ticket_service_url = '/visualizar-ordem-de-servico/?id=' . $service_tickets['data'][ $index + 1 ]['id'];
		}

		break;
	}
}

// Tratar dados para o botão de copiar
$OS_NUMBER = ( ! empty( $service_ticket['data']['number'] ) ? $service_ticket['data']['number'] : '' );
$USER_REPORT = ( ! empty( $service_ticket['data']['user_report'] ) ? $service_ticket['data']['user_report'] : '' );

get_header();

wp_localize_script( 'intranet-fafar-visualizar-ordem-de-servico', 'OS_NUMBER', $OS_NUMBER );
wp_localize_script( 'intranet-fafar-visualizar-ordem-de-servico', 'USER_REPORT', $USER_REPORT );
?>

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
		<?php if ( ! $prevent_write ) : ?>
			<a href="/editar-ordem-de-servico/?id=<?= $ID ?>" class="btn btn-outline-primary text-decoration-none w-button"
				title="Editar">
				<i class="bi bi-pencil"></i>
				Editar
			</a>
			<a class="btn btn-outline-danger text-decoration-none w-button" id="btn_delete" data-id="<?= $ID ?>"
				title="Excluir">
				<i class="bi bi-trash"></i>
				Excluir
			</a>
		<?php endif; ?>
		<?php if ( ! $prevent_insert_update ) : ?>

			<button class="btn btn-secondary" id="btn_copy_data" title="Copiar dados da OS">
				<i class="bi bi-clipboard"></i>
				Copiar
			</button>

			<div class="btn-group" role="group" aria-label="Basic checkbox toggle button group">
				<a href="<?= $previous_ticket_service_url ?>" class="btn btn-outline-secondary" id="btn_copy_data"
					title="Ir para OS anterior">
					<i class="bi bi-chevron-left"></i>
				</a>

				<a href="<?= $next_ticket_service_url ?>" class="btn btn-outline-secondary" id="btn_copy_data"
					title="Ir para próxima OS">
					<i class="bi bi-chevron-right"></i>
				</a>
			</div>
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
				<table class="table border border-end-0 border-start-0">
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
							<td>Número</td>
							<td class="fw-medium">
								<?php
								echo (
									$OS_NUMBER ?
									'<mark>' .
									$OS_NUMBER .
									'</mark>'
									:
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
							<td>Ramal</td>
							<td class="fw-medium">
								<?php
								echo (
									( $service_ticket['owner']['data'] ) ?
									get_the_author_meta( 'workplace_extension', $service_ticket['owner']['data']->ID ) :
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
									isset( $service_ticket['data']['type'] ) ?
									$service_ticket['data']['type'] :
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
									$USER_REPORT ?
									$USER_REPORT
									:
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
									(
										isset( $service_ticket['data']['place'] ) &&
										isset( $service_ticket['data']['place']['id'] ) &&
										! isset( $service_ticket['data']['place']['error_msg'] )
									) ?
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
									$service_ticket['data']['place']['data']['block'] .
									'</a>'
									:
									''
								)
									?>
							</td>
						<tr>
						<tr>
							<td>Prestador</td>
							<td class="fw-medium">
								<select id="select_assigned_to" class="form-select"
									aria-label="Selecionador para prestador" <?php echo ( ( $prevent_insert_update ) ? "disabled" : "" ); ?>>
									<?php
									if ( ! isset( $service_ticket['data']['assigned_to'] ) )
										$service_ticket['data']['assigned_to'] = 0;

									$users_by_departament = intranet_fafar_get_users_by_departament_as_options( $service_ticket_departament_role_slug, 'ATIVO' );
									?>

									<option value="0" <?php selected( strval( $service_ticket['data']['assigned_to'] ), 0 ); ?>>Selecione um</option>

									<?php

									foreach ( $users_by_departament as $key => $value ) :
										?>

										<option value="<?= $key ?>" <?php selected( strval( $service_ticket['data']['assigned_to'] ), strval( $key ) ); ?>><?= $value ?>
										</option>

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
		
		<?php if( ! $prevent_insert_update ): ?>
		<div class="row">
			<div class="col-12">
				<div class="d-flex justify-content-between px-2 py-2 border-bottom border-dark mb-2">
					<h5 class="fw-bold p-0 m-0"> Inserir Atualização </h5>
				</div>
				<div class="px-2 py-3">
					<?= do_shortcode( '[contact-form-7 id="0a46270" title="Inserir Atualização em Ordem de Serviço"]' ) ?>
				</div>
			</div>
		</div>
		<?php endif; ?>

		<div class="row">
			<div class="col-12">
				<div class="d-flex justify-content-between px-2 py-2 border-bottom border-dark mb-2">
					<h5 class="fw-bold p-0 m-0"> Histórico de Atualizações </h5>
				</div>
				<div class="px-2 py-3">
					<?php if ( count( $updates ) > 0 ) : ?>
						<table class="table border border-end-0 border-start-0">
							<tbody>
								<?php
								foreach ( $updates as $index => $update ) :
									?>

									<tr>
										<td class="fw-medium text-decoration-underline">Inserido em</td>
										<td class="fw-medium text-decoration-underline">
											<?php echo fafar_intranet_format_date_local( $update['created_at'] ); ?>
										</td>
									<tr>
									<tr>
										<td>Prestador</td>
										<td class="fw-medium"><?php echo $update['owner']['data']->display_name; ?></td>
									<tr>
									<tr>
										<td>Relatório</td>
										<td class="fw-medium"><?php echo str_replace( ';', '<br /> *', str_replace( '<<<>>>', '<br />', $update['data']['service_report'] ) ); ?></td>
									<tr>
									<tr>
										<td>Status</td>
										<td class="fw-medium">
											<?php echo fafar_intranet_get_status_badge( $update['data']['status'][0] ); ?>
										</td>
									<tr>

										<?php if ( count( $updates ) > ( $index + 1 ) ) : ?>

										<tr>
											<td class="bg-light"></td>
											<td class="bg-light"></td>
										<tr>

										<?php endif ?>


										<?php
								endforeach;
								?>
							</tbody>
						</table>
					<?php else : ?>
						<p class="text-muted">Nenhuma atualização inserida.</p>
					<?php endif; ?>
				</div>
			</div>
		</div>

		<div class="row">
			<div class="col-12">
				<div class="px-2 py-2 border-bottom border-dark mb-2">
					<h5 class="fw-bold p-0 m-0"> Avaliação </h5>
				</div>
				<div class="px-2 py-3">
					<?php if (
						! isset( $service_evaluations['error_msg'] ) &&
						isset( $service_evaluations[0] )
					) : ?>
						<div class="evaluation-header">
							<div>
								<h5 class="mb-1"><?= htmlspecialchars( $service_ticket['owner']['data']->display_name ) ?>
								</h5>
								<small
									class="text-muted"><?= date( "d M Y", strtotime( $service_evaluations[0]['created_at'] ) ) ?></small>
							</div>
						</div>
						<div class="mt-2">
							<?php for ( $i = 1; $i <= 5; $i++ ) : ?>
								<span
									class="star"><?= $i <= $service_evaluations[0]['data']['rate'] ? '<i class="bi bi-star-fill"></i>' : '<i class="bi bi-star"></i>' ?></span>
							<?php endfor; ?>
							<span
								class="ms-2 text-muted"><?= number_format( $service_evaluations[0]['data']['rate'], 1 ) ?></span>
						</div>
						<p class="mt-2"><?= htmlspecialchars( $service_evaluations[0]['data']['comment'] ) ?></p>

					<?php else : ?>
						<p class="text-muted">Nenhuma avaliação disponível.</p>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</div>

	<?php
	$user_role = intranet_fafar_get_user_slug_role();
	if (
		$user_role === 'tecnologia_da_informacao_e_suporte' ||
		$user_role === 'administrator'
	) {
		echo '<h5 class="mt-5">Objeto PHP</h5>';
		echo '<pre>';
		print_r( $service_ticket );
		echo '</pre>';
		echo '<br />';
		echo '<pre>';
		print_r( $updates );
		echo '</pre>';
		echo '<pre>';
		print_r( $service_evaluations );
		echo '</pre>';
	}
	?>

	<!-- Modal para inserir atualização na O.S. -->
	<div class="modal fade" id="intranetFafarInsertServiceTicketUpdate" tabindex="-1"
		aria-labelledby="intranetFafarInsertServiceTicketUpdateLabel" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<h1 class="modal-title fs-5" id="intranetFafarInsertServiceTicketUpdateLabel">Atualizar Ordem de
						Serviço</h1>
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