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

	<?php if (
		isset( $_GET['date'] ) &&
		isset( $_GET['start_time'] ) &&
		isset( $_GET['end_time'] ) &&
		isset( $_GET['capacity'] ) &&
		isset( $_GET['place'] )
	) : ?>
		<h5>2/2 - Informações adicionais</h5>
		<br />
		<?= do_shortcode( '[contact-form-7 id="c567fc2" title="Adicionar Reserva Pelo Assistente"]' ) ?>
	<?php else : ?>
		<h5>1/2 - Informações básicas</h5>
		<br />
		<a href="/sugestao-de-reservas-de-salas" class="btn btn-secondary">Sugestões</a>
		<br />
		<form id="form-buscar-salas" class="mb-5">
			<div class="form-group mb-3">
				<label for="event_date">* Dia do Evento </label>
				<input type="date" class="form-control" id="event_date" name="event_date" min="2024-09-10"
					aria-required="true" required />
			</div>
			<div class="form-group mb-3">
				<label for="start_time">* Início </label>
				<input type="time" class="form-control" id="start_time" name="start_time" aria-required="true" required />
			</div>
			<div class="form-group mb-3">
				<label for="end_time">* Fim </label>
				<input type="time" class="form-control" id="end_time" name="end_time" aria-required="true" required />
			</div>
			<div class="form-group mb-3">
				<label for="capacity">* Capacidade </label>
				<input type="number" class="form-control" id="capacity" name="capacity" min="1" max="200" placeholder="20"
					aria-required="true" required />
			</div>
			<button type="submit" class="btn btn-primary">Buscar Salas</button>
		</form>

		<!-- TABLES -->

		<div id="table-wrapper" class="my-5 d-none"></div>

	<?php endif; ?>

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