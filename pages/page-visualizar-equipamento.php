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

function fafar_intranet_format_date_local( $dt ) {

	return DateTime::createFromFormat( 'Y-m-d', $dt )->format( 'd/m/Y' );

}

function fafar_intranet_get_status_badge( $status ) {

	$type = "text-bg-info";
	$current_lower = strtolower( $status );

	if ( $current_lower === "emprestado" ) {

		$type = "text-bg-warning";

	} elseif ( $current_lower === "ativado" ) {

		$type = "text-bg-primary";

	} elseif (
		$current_lower === "desativado" ||
		$current_lower === "quebrado" ||
		$current_lower === "desaparecido"
	) {

		$type = "text-bg-danger";

	}

	return sprintf( '<span class="badge %s">%s</span>', $type, esc_html( $status ) );
}

$ID = sanitize_text_field( wp_unslash( $_GET["id"] ) );

$equipament = intranet_fafar_api_get_equipament_by_id( $ID );

$loans = intranet_fafar_api_get_loans_by_equipament( $ID );

$prevent_write = isset( $equipament['data']['prevent_write'] );

if ( isset( $loans['error_msg'] ) )
	$loans = array();

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
				'href="/editar-equipamento/?id=' . $ID . '"'
			)
				?>
			title="Editar">
			<i class="bi bi-pencil"></i>
			Editar
		</a>
		<a class="btn btn-outline-secondary text-decoration-none w-button <?php echo ( $prevent_write ? 'disabled' : '' ) ?>"
			<?php
			echo (
				$prevent_write ?
				'aria-disabled="true"'
				:
				'id="btn_loan"' .
				'data-id="' . $ID . '"'
			)
				?>
			title="Emprestar">
			<i class="bi bi-arrow-up"></i>
			Emprestar
		</a>
		<a class="btn btn-outline-secondary text-decoration-none w-button <?php echo ( $prevent_write ? 'disabled' : '' ) ?>"
			<?php
			echo (
				$prevent_write ?
				'aria-disabled="true"'
				:
				'id="btn_loan_return"' .
				'data-id="' . $ID . '"'
			)
				?>
			title="Devolver">
			<i class="bi bi-arrow-down"></i>
			Devolver
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
			title="Excluir">
			<i class="bi bi-trash"></i>
			Excluir
		</a>
	</div>

	<div class="container-fluid p-0">
		<div class="row">
			<div class="col-lg-8">
				<div class="px-2 py-2 border-bottom border-dark">
					<h5 class="fw-bold p-0 m-0"> Informações </h5>
				</div>
				<table class="table border border-end-0 border-start-0">
					<tbody>
						<tr>
							<td>Responsável</td>
							<td class="fw-medium">
								<?php
								echo (
									( $equipament['data']['applicant']['data'] ) ?
									'<a href="/membros/' . $equipament['data']['applicant']['data']->user_login . '" target="_blank" title="Perfil de ' . $equipament['data']['applicant']['data']->display_name . '">' .
									$equipament['data']['applicant']['data']->display_name .
									'</a>' :
									''
								)
									?>
							</td>
						<tr>
						<tr>
							<td>Status</td>
							<td class="fw-medium">
								<?php
								echo ( ( $equipament['data']['status'][0] ) ?
									fafar_intranet_get_status_badge(
										( $equipament['data']['on_loan'] ) ?
										"Emprestado" :
										$equipament['data']['status'][0]
									)
									:
									''
								)
									?>
							</td>
						<tr>
						<tr>
							<td>Tipo</td>
							<td class="fw-medium"><?php echo ( ( $equipament['data']['object_sub_type'][0] ) ?? '' ) ?>
							</td>
						<tr>
						<tr>
							<td>Patrimônio</td>
							<td class="fw-medium"><?php echo ( ( $equipament['data']['asset'] ) ?? '' ) ?></td>
						<tr>
						<tr>
							<td>Patrimônio Interno</td>
							<td class="fw-medium"><?php echo ( ( $equipament['data']['internal_asset'] ) ?? '' ) ?></td>
						<tr>
						<tr>
							<td>Marca</td>
							<td class="fw-medium"><?php echo ( ( $equipament['data']['brand'] ) ?? '' ) ?></td>
						<tr>
						<tr>
							<td>Modelo</td>
							<td class="fw-medium"><?php echo ( ( $equipament['data']['model'] ) ?? '' ) ?></td>
						<tr>
						<tr>
							<td>Descrição</td>
							<td class="fw-medium"><?php echo ( ( $equipament['data']['desc'] ) ?? '' ) ?></td>
						<tr>
						<tr>
							<td>Sala</td>
							<td class="fw-medium">
								<?php
								echo (
									( ! empty( $equipament['data']['place'] ) ) ?
									'<a 
                                                        href="./visualizar-objeto?id=' . $equipament['data']['place']['id'] . '" 
                                                        target="blank" 
                                                        title="Detalhes da sala"
                                                    >' .
									$equipament['data']['place']['data']['number'] .
									" - Andar: " .
									$equipament['data']['place']['data']['floor'] .
									"ª" .
									" - Bloco: " .
									$equipament['data']['place']['data']['block'] : '' .
									'</a>'
								)
									?>
							</td>
						<tr>
						<tr>
							<td>CPU</td>
							<td class="fw-medium">
								<?php
								echo ( ( $equipament['data']['cpu_brand'][0] ) ?? '' );
								echo " ";
								echo ( ( $equipament['data']['cpu_model'] ) ?? '' );
								echo " ";
								echo ( ( $equipament['data']['cpu_frequency'] ) ? $equipament['data']['cpu_frequency'] . ' GHz' : '' );
								?>
							</td>
						<tr>
						<tr>
							<td>RAM</td>
							<td class="fw-medium">
								<?php
								echo ( ( $equipament['data']['ram_type'][0] ) ?? '' );
								echo " ";
								echo ( ( $equipament['data']['ram_capacity'] ) ? $equipament['data']['ram_capacity'] . ' GB' : '' );
								?>
							</td>
						<tr>
						<tr>
							<td>Discos</td>
							<td class="fw-medium">
								<?php
								if ( $equipament['data']['disk_capacity_1'] ) {
									echo $equipament['data']['disk_capacity_1'];
									echo ' GB ';
									echo '(';
									echo ( ( $equipament['data']['disk_type_1'][0] ) ?? '--' );
									echo ')';
									echo '<br />';
								}

								if ( $equipament['data']['disk_capacity_2'] ) {
									echo $equipament['data']['disk_capacity_2'];
									echo ' GB ';
									echo '(';
									echo ( ( $equipament['data']['disk_type_2'][0] ) ?? '--' );
									echo ')';
									echo '<br />';
								}

								if ( $equipament['data']['disk_capacity_3'] ) {
									echo $equipament['data']['disk_capacity_3'];
									echo ' GB ';
									echo '(';
									echo ( ( $equipament['data']['disk_type_3'][0] ) ?? '--' );
									echo ')';
									echo '<br />';
								}

								if ( $equipament['data']['disk_capacity_4'] ) {
									echo $equipament['data']['disk_capacity_4'];
									echo ' GB ';
									echo '(';
									echo ( ( $equipament['data']['disk_type_4'][0] ) ?? '--' );
									echo ')';
									echo '<br />';
								}
								?>
							</td>
						<tr>
						<tr>
							<td>GPU</td>
							<td class="fw-medium">
								<?php
								echo ( ( $equipament['data']['gpu_brand'][0] ) ?? '' );
								echo " ";
								echo ( ( $equipament['data']['gpu_model'] ) ?? '' );
								echo " ";
								echo ( ( $equipament['data']['gpu_frequency'] ) ? $equipament['data']['gpu_frequency'] . ' GHz' : '' );
								echo " ";
								echo ( ( $equipament['data']['gpu_ram'] ) ? $equipament['data']['gpu_ram'] . ' GB' : '' );
								?>
							</td>
						<tr>
						<tr>
							<td>SO</td>
							<td class="fw-medium">
								<?php
								echo ( ( $equipament['data']['os_type'][0] ) ?? '' );
								echo " ";
								echo ( ( $equipament['data']['os_version'] ) ?? '' );
								echo " ";
								echo ( ( $equipament['data']['os_arch'][0] ) ?? '' );
								?>
							</td>
						<tr>
						<tr>
							<td>IP</td>
							<td class="fw-medium">
								<?php
								echo ( ( isset( $equipament['data']['ip']['data']['address'] ) ) ? $equipament['data']['ip']['data']['address'] : '' )
									?>
							</td>
						<tr>
						<tr>
							<td>Conectado à roteador</td>
							<td class="fw-medium">
								<?php echo ( ( $equipament['data']['is_connected_to_router'][0] ) ?? '' ) ?></td>
						<tr>
					</tbody>
				</table>
			</div>
			<div class="col-lg-4">
				<div class="px-2 py-2 border-bottom border-dark">
					<h5 class="fw-bold p-0 m-0"> Histório de Empréstimos </h5>
				</div>
				<table class="table border border-end-0 border-start-0">
					<tbody>
						<?php

						foreach ( $loans as $loan ) {

							$loan_to = isset( $loan['data']['loan_to'][0] ) ?
								get_userdata( $loan['data']['loan_to'][0] ) : null;

							echo '<tr>';
							echo '<td>Data de empréstimo</td>';
							echo '<td class="fw-medium">' . esc_html( ( isset( $loan['data']['loan_date'] ) ) ? fafar_intranet_format_date_local( $loan['data']['loan_date'] ) : '--/--/----' ) . '</td>';
							echo '<tr>';

							echo '<tr>';
							echo '<td>Emprestado para</td>';
							echo '<td class="fw-medium">' . esc_html( ( $loan_to ? $loan_to->get( 'display_name' ) : '--' ) ) . '</td>';
							echo '<tr>';

							echo '<tr>';
							echo '<td>Descrição de empréstimo</td>';
							echo '<td class="fw-medium">' . esc_html( ( ( $loan['data']['loan_desc'] ) ?? '--' ) ) . '</td>';
							echo '<tr>';

							echo '<tr>';
							echo '<td>Data de devolução</td>';
							echo '<td class="fw-medium">' . esc_html( ( isset( $loan['data']['return_date'] ) ? fafar_intranet_format_date_local( $loan['data']['return_date'] ) : '--/--/----' ) ) . '</td>';
							echo '<tr>';

							echo '<tr>';
							echo '<td>Descrição de devolução</td>';
							echo '<td class="fw-medium">' . esc_html( ( ( $loan['data']['return_desc'] ) ?? '--' ) ) . '</td>';
							echo '<tr>';

							echo '<tr>';
							echo '<td class="border-top"></td>';
							echo '<td class="border-top"></td>';
							echo '<tr>';

						}

						?>
					</tbody>
				</table>
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
		print_r( $equipament );
		echo '</pre>';
		echo '<br />';
		echo '<pre>';
		print_r( $loans );
		echo '</pre>';
	}
	?>


	<!-- Modal para empréstimo de equipamentos -->
	<div class="modal fade" id="intranetFafarLoanModal" tabindex="-1" aria-labelledby="intranetFafarLoanModalLabel"
		aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<h1 class="modal-title fs-5" id="intranetFafarLoanModalLabel">Emprestar</h1>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
					<?php
					echo do_shortcode( '[contact-form-7 id="115a11b" title="Emprestar Equipamento"]' );
					?>
				</div>
			</div>
		</div>
	</div>

	<!-- Modal para empréstimo de equipamentos -->
	<div class="modal fade" id="intranetFafarLoanReturnModal" tabindex="-1"
		aria-labelledby="intranetFafarLoanReturnModalLabel" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<h1 class="modal-title fs-5" id="intranetFafarLoanReturnModalLabel">Devolver</h1>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
					<?php
					echo do_shortcode( '[contact-form-7 id="0dedf40" title="Devolver Equipamento"]' );
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