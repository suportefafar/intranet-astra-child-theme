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

function get_place_type_display_name( $type ) {

	$types = array(
		'auditorium' => 'Auditório',
		'classroom' => 'Sala de aula',
		'general' => 'Geral',
		'lab' => 'Laboratório',
		'multimedia_room' => 'Sala de multimídia',
		'computer_lab' => 'Laboratório de computador',
		'professor_office' => 'Gabinete'
	);

	return $types[ $type ] ?? '--';

}

$ID = sanitize_text_field( wp_unslash( $_GET["id"] ) );
$place = intranet_fafar_api_get_submission_by_id( $ID );

$professor_responsible = '';
if ( ! empty( $place['data']['professor_responsible'][0] ) ) {
	$professor_responsible = intranet_fafar_api_get_user_by_id( $place['data']['professor_responsible'][0] );
}

$tae_responsible = '';
if ( ! empty( $place['data']['tae_responsible'][0] ) ) {
	$tae_responsible = intranet_fafar_api_get_user_by_id( $place['data']['tae_responsible'][0] );
}

$prevent_write = isset( $place['data']['prevent_write'] );

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
				'href="/editar-sala/?id=' . $ID . '"'
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
							<td>Tipo</td>
							<td class="fw-medium">
								<?php
								echo (
									isset( $place['data']['object_sub_type'][0] ) ?
									get_place_type_display_name( $place['data']['object_sub_type'][0] ) : ''
								)
									?>
							</td>
						<tr>
						<tr>
							<td>Número</td>
							<td class="fw-medium"><?php echo ( ( $place['data']['number'] ) ?? '' ) ?></td>
						<tr>
						<tr>
							<td>Descrição</td>
							<td class="fw-medium"><?php echo ( ( $place['data']['desc'] ) ?? '' ) ?></td>
						<tr>
						<tr>
							<td>Bloco</td>
							<td class="fw-medium"><?php echo ( ( $place['data']['block'] ) ?? '' ) ?></td>
						<tr>
						<tr>
							<td>Andar</td>
							<td class="fw-medium"><?php echo ( ( $place['data']['floor'] ) ?? '' ) ?></td>
						<tr>
						<tr>
							<td>Capacidade</td>
							<td class="fw-medium"><?php echo ( ( $place['data']['capacity'] ) ?? '' ) ?></td>
						<tr>
						<tr>
							<td>Dono</td>
							<td class="fw-medium">
								<?php if ( isset( $place['owner']['data'] ) ) : ?>
									<?= $place['owner']['data']->display_name ?>
								<?php endif; ?>
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
		print_r( $place );
		echo '</pre>';
	}
	?>

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