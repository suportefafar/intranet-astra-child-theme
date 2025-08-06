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

$ID = sanitize_text_field( wp_unslash( $_GET["id"] ) );
$classroom = intranet_fafar_api_get_submission_by_id( $ID );
$all_reservations = intranet_fafar_api_get_reservations_by_place( $ID );

if ( isset( $all_reservations['error_msg'] ) ) {
	$all_reservations = array();
}

$reservations = array_filter(
	$all_reservations,
	function ($r) {
		return ( isset( $r['data']['class_subject'] ) && $r['data']['class_subject'] );
	}
);

get_header(); ?>

<script>
	var RESERVAS = <?= json_encode( $reservations, JSON_UNESCAPED_SLASHES ); ?>; 
</script>

<?php if ( astra_page_layout() == 'left-sidebar' ) : ?>

	<?php get_sidebar(); ?>

<?php endif ?>

<!--
	*
	*
	*
	* Conteúdo customizado da página
	* Início
-->

<!-- CALENDER -->

<div class="map-printer-container">
	<div class="d-flex justify-content-between mb-3">
		<a href="/reservas?place_id=<?= $ID ?>" class="btn btn-secondary" id="btn_go_back">
			<i class="bi bi-arrow-left"></i>
			Voltar
		</a>
		<a href="#" class="btn btn-primary" id="btn_printer">
			<i class="bi bi-printer"></i>
			Imprimir
		</a>
	</div>

	<h1 class="numero-sala"><?php echo preg_replace( '/\D/', '', $classroom['data']['number'] ); ?></h1>

	<div id="calendar"></div>

	<img class="logo-fafar" src="<?= get_stylesheet_directory_uri() ?>/assets/img/logo-fafar-escuro.png" width="64">
</div>

<!--
	* Conteúdo customizado da página
	* Fim
	*
	*
	*
-->

<?php get_footer(); ?>