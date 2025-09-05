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

function parse_subject_desired_time( $input ) {
	$result = [];
	preg_match_all( '/(\d{1,2}:\d{2})\s+(\d{1,2}:\d{2})\s+\((\w{3})\)/', $input, $matches, PREG_SET_ORDER );

	// Mapeamento dos dias da semana para números (Seg = 1, Ter = 2, ..., Dom = 7)
	$days_map = [ 
		'SEG' => 1, 'TER' => 2, 'QUA' => 3,
		'QUI' => 4, 'SEX' => 5, 'SAB' => 6, 'DOM' => 7
	];

	foreach ( $matches as $match ) {
		$start = $match[1];
		$end = $match[2];
		$weekday = $days_map[ strtoupper( $match[3] ) ] ?? null;

		if ( $weekday ) {
			$result[] = [ 
				'start' => $start,
				'end' => $end,
				'weekday' => [ (int) $weekday ]
			];
		}
	}

	return $result;
}

function get_weekday_label( $weekday_num ) {
	$weekday_num = intval( $weekday_num );

	$weekdays_label = [
		'DOM',
		'SEG',
		'TER',
		'QUA',
		'QUI',
		'SEX',
		'SAB'
	];

	if ( isset( $weekdays_label[ $weekday_num ] ) )
		return $weekdays_label[ $weekday_num ];

	return '';
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
	<div class="d-flex flex-column gap-2">
		<h5 class="m-0"><?= ( $class_subject_title ?? '--' ) ?></h5>
		<div class="d-flex gap-1 justify-content-between align-items-end">
			<span><?= ( $reservations_by_subject ? count( $reservations_by_subject ) : '0' ) ?> reserva(s) encontrada(s)</span>
			
			<button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#intranetFafarSujectClassTimesModal">
				<i class="bi bi-plus-lg"></i>
				Reservar Disciplina
			</button>
		</div>
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
	</div>

	<!-- Modal para selecionar o horário da nova reserva da disciplina -->
	<div class="modal fade" id="intranetFafarSujectClassTimesModal" tabindex="-1"
		aria-labelledby="intranetFafarSujectClassTimesModalLabel" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<h1 class="modal-title fs-5" id="intranetFafarSujectClassTimesModalLabel">Horários</h1>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
					<table class="table border border-start-0 border-end-0">
						<tbody>
							<?php
								$times = parse_subject_desired_time( $class_subject['data']['desired_time'] );
								if ( ! empty( $times ) ):
									foreach( $times as $time ): 
										$capacity      = $class_subject['data']['number_vacancies_offered'] ?? 0;
										$weekday       = $time['weekday'][0];
										$weekday_label = get_weekday_label( $weekday );
										$time_display  = $time['start'] . ' ' . $time['end'] . ' (' . $weekday_label . ')';
							?>
							<tr>
								<td scope="col"><?= $time_display ?></td>
								<td scope="col">
									<a href="/assistente-de-reservas-de-salas/?subject=<?= $class_subject['id'] ?>&capacity=<?= $capacity ?>&start_time=<?= $time['start'] ?>&end_time=<?= $time['end'] ?>&weekdays=<?= $weekday ?>&frequency=weekly" class="btn btn-outline-primary" target="_blank" title="Reservar para <?= $time_display ?>">
										<i class="bi bi-arrow-right"></i>
									</a>
								</td>
							</tr>
							<?php 	
									endforeach;
								else: 
							?>
							<tr>
								<td scope="col">Adicione um horário.</td>
							</tr>
							<?php 
								endif; 
							?>
						</tbody>
					</table>
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