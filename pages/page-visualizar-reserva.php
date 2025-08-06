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

function fafar_intranet_get_frequency_display_text( $f ) {

	switch ( $f ) {

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

	$weekdays_arr = array_map( function ($wd) use ($weekdays) {
		return $weekdays[ $wd ];
	}, $wds );

	return implode( ', ', $weekdays_arr );

}

$ID = sanitize_text_field( wp_unslash( $_GET["id"] ) );

$reservation = intranet_fafar_api_get_reservation_by_id( $ID );

$prevent_write = isset( $reservation['data']['prevent_write'] );

$place = null;
if ( ! empty( $reservation['data']['place'][0] ) ) {
	$place = intranet_fafar_api_get_submission_by_id( $reservation['data']['place'][0] );
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
	<!-- HEADER BUTTONS -->

	<div class="d-flex justify-content-start gap-2 mb-4">
		<a class="btn btn-outline-primary text-decoration-none w-button <?php echo ( $prevent_write ? 'disabled' : '' ) ?>"
			<?php
			echo (
				$prevent_write ?
				'aria-disabled="true"'
				:
				'href="/editar-reserva/?id=' . $ID . '"'
			)
				?>
			title="Editar">
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
			title="Excluir">
			<i class="bi bi-trash"></i>
			Excluir
		</a>
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
							<td>Descrição</td>
							<td class="fw-medium"><?php echo ( ( $reservation['data']['desc'] ) ?? '' ) ?></td>
						<tr>
						<tr>
							<td>Disciplina</td>
							<td class="fw-medium">
								<?php
								echo (
									isset( $reservation['data']['class_subject']['data'] ) ?
									$reservation['data']['class_subject']['data']['code'] .
									' - ' .
									$reservation['data']['class_subject']['data']['group'] :
									''
								)
									?>
							</td>
						<tr>
						<tr>
							<td>Data</td>
							<td class="fw-medium">
								<?php
								echo (
									( $reservation['data']['date'] ) ?
									fafar_intranet_format_date_local( $reservation['data']['date'] ) :
									''
								)
									?>
							</td>
						<tr>
						<tr>
							<td>Até</td>
							<td class="fw-medium">
								<?php
								echo (
									( $reservation['data']['end_date'] ) ?
									fafar_intranet_format_date_local( $reservation['data']['end_date'] ) :
									''
								)
									?>
							</td>
						<tr>
						<tr>
							<td>Local</td>
							<td class="fw-medium">
								<?php if ( $place ) : ?>
										<a href="/visualizar-sala/?id=<?= $place['id'] ?>" target="_blank"
											title="Perfil da sala">
											<?= $place['data']['number'] . ( $place['data']['desc'] ? ' - ' . $place['data']['desc'] : '' ) ?>
										</a>
								<?php endif; ?>
							</td>
						<tr>
						<tr>
							<td>Início</td>
							<td class="fw-medium">
								<?php
								echo (
									( $reservation['data']['start_time'] ) ?
									$reservation['data']['start_time'] :
									''
								)
									?>
							</td>
						<tr>
						<tr>
							<td>Fim</td>
							<td class="fw-medium">
								<?php
								echo (
									( $reservation['data']['end_time'] ) ?
									$reservation['data']['end_time'] :
									''
								)
									?>
							</td>
						<tr>
						<tr>
							<td>Frequência</td>
							<td class="fw-medium">
								<?php
								echo (
									( $reservation['data']['frequency'][0] ) ?
									fafar_intranet_get_frequency_display_text( $reservation['data']['frequency'][0] ) :
									''
								)
									?>
							</td>
						<tr>
						<tr>
							<td>Dias da semana</td>
							<td class="fw-medium">
								<?php
								echo (
									isset( $reservation['data']['weekdays'][0] ) ?
									fafar_intranet_get_weekdays( $reservation['data']['weekdays'] ) :
									''
								)
									?>
							</td>
						<tr>
						<tr>
							<td>Dono</td>
							<td class="fw-medium">
								<?php
								echo (
									isset( $reservation['owner']['data'] ) ?
									'<a href="/membros/' . $reservation['owner']['data']->user_login . '" target="_blank" title="Perfil de ' . $reservation['owner']['data']->display_name . '">' .
									$reservation['owner']['data']->display_name .
									'</a>' :
									''
								)
									?>
							</td>
						<tr>
						<tr>
							<td>Solicitante</td>
							<td class="fw-medium">
								<?php
								echo (
									isset( $reservation['data']['applicant']['data'] ) ?
									'<a href="/membros/' . $reservation['data']['applicant']['data']->user_login . '" target="_blank" title="Perfil de ' . $reservation['data']['applicant']['data']->display_name . '">' .
									$reservation['data']['applicant']['data']->display_name .
									'</a>' :
									''
								)
									?>
							</td>
						<tr>
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
		print_r( $reservation );
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