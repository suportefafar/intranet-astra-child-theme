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

$csv_data = null;
$header = null;
$allow_to_import = false;

$error_count = null;
$duplicates_count = null;
$not_a_fafar_subject = null;
$success_count = null;
$total_count = null;
$above_counter = null;
$combinations = array();

if (
	$_SERVER['REQUEST_METHOD'] === 'POST' &&
	isset( $_POST['old_class_subjects'] ) &&
	isset( $_POST['old_reservations'] )
) {

	$old_class_subjects_json = $_POST["old_class_subjects"];
	$old_reservations_json = $_POST["old_reservations"];

	//$json_d = json_decode( preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $json_string), true );
	$old_class_subjects = json_decode( stripslashes( $old_class_subjects_json ), true );
	$old_reservations = json_decode( stripslashes( $old_reservations_json ), true );

	print_r( "<br/>" );
	print_r( "JSON LAST ERROR: " );

	switch ( json_last_error() ) {
		case JSON_ERROR_NONE:
			echo ' - No errors';
			break;
		case JSON_ERROR_DEPTH:
			echo ' - Maximum stack depth exceeded';
			break;
		case JSON_ERROR_STATE_MISMATCH:
			echo ' - Underflow or the modes mismatch';
			break;
		case JSON_ERROR_CTRL_CHAR:
			echo ' - Unexpected control character found';
			break;
		case JSON_ERROR_SYNTAX:
			echo ' - Syntax error, malformed JSON';
			break;
		case JSON_ERROR_UTF8:
			echo ' - Malformed UTF-8 characters, possibly incorrectly encoded';
			break;
		default:
			echo ' - Unknown error';
			break;
	}

	print_r( "<br/>" );

	$places = intranet_fafar_api_get_submissions_by_object_name( 'place' );
	$class_subjects = intranet_fafar_api_get_submissions_by_object_name( 'class_subject' );
	$reservations = intranet_fafar_api_get_submissions_by_object_name( 'reservation' );

	foreach ( $old_reservations as $old_reservation ) {

		if ( $old_reservation['frequencia'] == 0 )
			continue;

		$old_class_subject = get_old_class_subject_by_id( $old_reservation['cod_disciplina'], $old_class_subjects );

		if ( ! $old_class_subject || $old_class_subject['tipo_disciplina'] !== 'OB' ) {
			continue;
		}

		$class_subject = get_class_subject_by_code( $old_class_subject['cod_disciplina'], $class_subjects );
		if ( ! $class_subject )
			continue;

		$place = get_place_by_old_id( $old_reservation['sala'], $places );
		if ( ! $place )
			continue;

		$total_count++;

		$place_capacity = isset( $place['data']['capacity'] ) ? (int) $place['data']['capacity'] : 0;
		$class_vacancies = isset( $class_subject['data']['number_vacancies_offered'] ) ? (int) $class_subject['data']['number_vacancies_offered'] : 0;

		if ( $place_capacity > ( $class_vacancies + 40 ) ) {
			$combinations[] = array(
				'sala' => $place['data']['number'],
				'capacidade' => $place['data']['capacity'],
				'disciplina' => $class_subject['data']['code'],
				'vagas' => $class_subject['data']['number_vacancies_offered'],
			);
			$above_counter++;
		}
	}

}

function get_place_by_old_id( $id, $places ) {

	foreach ( $places as $place ) {
		if (
			isset( $place['data']['old_id'] ) &&
			$place['data']['old_id'] === $id
		)
			return $place;
	}

	return null;
}


function get_class_subject_by_code( $code, $class_subjects ) {

	foreach ( $class_subjects as $class_subject ) {
		if (
			intranet_fafar_utils_escape_and_clean_to_compare( $class_subject['data']['code'] ) === intranet_fafar_utils_escape_and_clean_to_compare( $code )
		)
			return $class_subject;
	}

	return null;

}

function get_old_class_subject_by_id( $id, $class_subjects ) {

	foreach ( $class_subjects as $class_subject ) {
		if ( $class_subject['id'] === $id )
			return $class_subject;
	}

	return null;
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


	<form class="my-3" action="/levantamento-reservas" method="POST">

		<div class="form-floating mb-3">
			<textarea class="form-control" placeholder="Insira o texto JSON aqui" id="floatingTextarea"
				name="old_class_subjects" rows="15" required></textarea>
			<label for="floatingTextarea">Diciplinas</label>
		</div>

		<div class="form-floating mb-3">
			<textarea class="form-control" placeholder="Insira o texto JSON aqui" id="floatingTextarea"
				name="old_reservations" rows="15" required></textarea>
			<label for="floatingTextarea">Reservas</label>
		</div>

		<button type="submit">
			Processar
		</button>
	</form>

	<hr class="mx-1" />

	<h5><?= $total_count ?> reservas | <?= $above_counter ?>
		ineficientes(<?= round( $above_counter / 2, 2 ) ?>/semestre)</h5>
	<table class="table">
		<thead>
			<tr>
				<th scope="col">Sala</th>
				<th scope="col">Capacidade</th>
				<th scope="col">Disciplina</th>
				<th scope="col">Vagas</th>
			</tr>
		</thead>
		<tbody>
			<?php
			if ( isset( $combinations ) && is_array( $combinations ) && count( $combinations ) > 0 ) {

				foreach ( $combinations as $row ) {
					echo '<tr>';
					echo '<td>' . $row['sala'] . '</td>';
					echo '<td>' . $row['capacidade'] . '</td>';
					echo '<td>' . $row['disciplina'] . '</td>';
					echo '<td>' . $row['vagas'] . '</td>';
					echo '</tr>';
				}
			}
			?>
		</tbody>
	</table>

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