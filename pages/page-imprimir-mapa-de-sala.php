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

$ID           = sanitize_text_field( wp_unslash( $_GET["id"] ) );
$classroom    = intranet_fafar_api_get_submission_by_id( $ID );
$reservations = intranet_fafar_api_get_reservations_by_place( $ID );

wp_enqueue_script_module( 'intranet-fafar-imprimir-mapa-de-sala-script', get_stylesheet_directory_uri() . '/assets/js/imprimir-mapa-de-sala.js', array( 'jquery' ), false, false );

get_header(); ?>

<script>
  var RESERVAS = <?= json_encode( $reservations, JSON_UNESCAPED_SLASHES ); ?>; 
</script>

<!--
    *
    *
    *
    * Conteúdo customizado da página
    * Início
--> 

<!-- CALENDER -->

<h1 class="numero-sala"><?php echo preg_replace( '/\D/', '', $classroom['data']['number'] ); ?></h1>

<div id="calendar"></div>

<img class="logo-fafar" src="<?= get_stylesheet_directory_uri() ?>/assets/img/logo-fafar-escuro.png" width="64">

<!--
    * Conteúdo customizado da página
    * Fim
    *
    *
    *
-->

<?php get_footer(); ?>
