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

$class_subject_title = null;
$reservations_by_subject = array();

if ( isset( $_GET['id'] ) ) {
	$id = intranet_fafar_utils_escape_and_clean_to_compare( $_GET['id'] );
	$reservations = intranet_fafar_api_get_submissions_by_object_name( 'reservation', [ 'orderby_column' => 'created_at', 'order' => 'ASC' ] );
	$reservations_by_subject = array_filter( $reservations, function ($reservation) use ($id) {
		return isset( $reservation['data']['class_subject'][0] ) && $reservation['data']['class_subject'][0] === $id;
	} ) ?? array();

	$reservations_by_subject = array_map( function ($reservation) {
		$reservation['data']['place'] = intranet_fafar_api_get_submission_by_id( $reservation['data']['place'][0] );
		return $reservation;
	}, $reservations_by_subject );


	$class_subject = intranet_fafar_api_get_submission_by_id( $id );

	if ( $class_subject && isset( $class_subject['data'] ) ) {

		$class_subject_title = $class_subject['data']['code'] . ' ' .
			$class_subject['data']['name_of_subject'] . ' ' .
			$class_subject['data']['group'];

	}
}

function parse_weekday_to_name( $number ) {

	if ( ! $number )
		return 'N/A';

	if ( ! is_numeric( $number ) )
		return $number;

	return array(
		0 => 'Dom',
		1 => 'Seg',
		2 => 'Ter',
		3 => 'Qua',
		4 => 'Qui',
		5 => 'Sex',
		6 => 'Sáb',
	)[ (int) $number ] ?? '--';
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

	<!-- TABLES -->
	<h4>Reservas para <?= ( $class_subject_title ?? '--' ) ?></h4>
	<h5><?= ( $reservations_by_subject ? count( $reservations_by_subject ) : '0' ) ?> reserva(s) encontrada(s)</h5>
	<table class="table">
		<thead>
			<tr>
				<th scope="col">Sala</th>
				<th scope="col">Início</th>
				<th scope="col">Fim</th>
				<th scope="col">Dia da semana</th>
				<th scope="col">Ações</th>
			</tr>
		</thead>
		<tbody>
			<?php
			if ( count( $reservations_by_subject ) > 0 ) :
				foreach ( $reservations_by_subject as $reservation ) :
					?>
					<tr>
						<td><?= $reservation['data']['place']['data']['number'] ?></td>
						<td><?= $reservation['data']['start_time'] ?></td>
						<td><?= $reservation['data']['end_time'] ?></td>
						<td><?= implode( ', ', array_map( function ($weekday) {
							return parse_weekday_to_name( $weekday ); }, (array) $reservation['data']['weekdays'] ) ) ?>
						</td>
						<td>
							<a class="btn btn-outline-secondary" href="/visualizar-reserva/?id=<?= $reservation['id'] ?>"
								target="blank" title="Detalhes">
								<i class="bi bi-info-lg"></i>
							</a>
						</td>
					</tr>

				<?php
				endforeach;
			endif;
			?>
		</tbody>
	</table>

	<?php
	$user_role = intranet_fafar_get_user_slug_role();
	if (
		$user_role === 'tecnologia_da_informacao_e_suporte' ||
		$user_role === 'administrator'
	) {
		echo '<h5 class="mt-5">Objeto PHP</h5>';
		echo '<pre>';
		print_r( isset( $reservations_by_subject ) ? $reservations_by_subject : '' );
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