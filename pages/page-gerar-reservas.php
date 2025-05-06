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

$reservation_log = array();

function generate_reservations( $class_subjects ) {

	global $reservation_log;

	if ( isset( $class_subjects['error_msg'] ) ) {
		return 'Nenhuma reserva encontrada!';
	}

	// Filtra disciplinas com mais de 80 vagas, disciplinas práticas, etc.
	$filtered_class_subjects = array_filter( $class_subjects, function ($subject) {
		return (
			( (int) $subject['data']['number_vacancies_offered'] ) > 0 &&
			( (int) $subject['data']['number_vacancies_offered'] ) < 80 &&
			isset( $subject['data']['desired_time'] ) &&
			is_string( $subject['data']['desired_time'] ) &&
			count( parse_schedule( $subject['data']['desired_time'] ) ) > 0 &&
			! str_contains( intranet_fafar_utils_escape_and_clean_to_compare( $subject['data']['name_of_subject'] ), 'estagio' ) &&
			! str_contains( intranet_fafar_utils_escape_and_clean_to_compare( $subject['data']['name_of_subject'] ), 'monografia' ) &&
			! str_contains( strtoupper( $subject['data']['group'] ), 'P' ) &&
			isset( $subject['data']['use_on_auto_reservation'][0] ) &&
			strtoupper( $subject['data']['use_on_auto_reservation'][0] ) === 'SIM'
		);
	} );

	if ( empty( $class_subjects ) ) {
		return 'Não há disciplinas cadastradas para reservas.';
	}

	echo '<br />' . count( $class_subjects ) . ' disciplinas para reservas.<br />';

	$pre_reservations_data = get_pre_reservations_data( $filtered_class_subjects );

	// Adicionar pontuação nas disciplinas
	$pre_reservations_pointed = set_points_to_subjects( $pre_reservations_data );

	// Ordenar pela pontuação recebida
	usort( $pre_reservations_pointed, function ($a, $b) {
		return $b['score'] <=> $a['score']; // Sort descending
	} );

	// Ordenar pela quantidade vagas
	$classrooms = array_filter(
		intranet_fafar_api_get_submissions_by_object_name( 'place', [ 'orderby_json' => 'capacity', 'order' => 'ASC' ] ),
		fn( $place ) => $place['data']['object_sub_type'][0] === 'classroom'
	);

	$attempts_counter = 0;
	$fails_counter = 0;
	$successes_counter = 0;

	foreach ( $pre_reservations_pointed as $pre_reservation_pointed ) {

		$attempts_counter++;

		$possible_rooms = array_filter(
			$classrooms,
			fn( $room ) => $room['data']['capacity'] >= $pre_reservation_pointed['class_subject_number_vacancies_offered']
		);

		$made_reservation = false;
		foreach ( $possible_rooms as $room ) {

			$data = $pre_reservation_pointed;

			$desc = $data['class_subject_code'] . ' (' . implode( '/', $data['class_subject_group'] ) . ')';

			$new_pre_reservation = [ 
				'object_name' => 'reservation',
				'permissions' => '774',
				'group_owner' => $data['class_subject_group_owner'],
				'data' => json_encode( [ 
					'class_subject' => [ $data['class_subject_id'] ],
					'place' => [ $room['id'] ],
					'frequency' => [ 'weekly' ],
					'weekdays' => $data['weekdays'],
					'start_time' => $data['start_time'],
					'end_time' => $data['end_time'],
					'date' => convert_date( $data['date'] ),
					'end_date' => convert_date( $data['end_date'] ),
					'applicant' => get_current_user_id(),
					'desc' => $desc,
				] ),
			];

			// echo '<br />';
			// print_r($new_pre_reservation);
			// echo '<br />';

			$new_reservation_formatted = intranet_fafar_api_create_or_update_reservation( $new_pre_reservation );

			if ( ! isset( $new_reservation_formatted['error_msg'] ) ) {

				$new_reservation = intranet_fafar_api_create( $new_reservation_formatted );

				if ( ! isset( $new_reservation['error_msg'] ) ) {

					$successes_counter++;

					$reservation_log[] = array(
						'sub_id' => $data['class_subject_id'],
						'sub_code' => $data['class_subject_code'],
						'vacancies' => $data['class_subject_number_vacancies_offered'],
						'scheduale' => implode( ' ', array( $data['start_time'], $data['end_time'], implode( ' ', $data['weekdays'] ) ) ),
						'status' => 'success',
						'desc' => '',
						'points' => $data['score'],
						'nature' => $data['class_subject_nature_of_subject'],
					);

					$made_reservation = true;

					break;

				}

			}
		}

		if ( ! $made_reservation ) {

			$fails_counter++;

			$reservation_log[] = array(
				'sub_id' => $data['class_subject_id'],
				'sub_code' => $data['class_subject_code'],
				'vacancies' => $data['class_subject_number_vacancies_offered'],
				'scheduale' => implode( ' ', array( $data['start_time'], $data['end_time'], implode( ' ', $data['weekdays'] ) ) ),
				'status' => 'fail',
				'desc' => 'Sem sala disponível',
				'points' => $data['score'],
				'nature' => $data['class_subject_nature_of_subject'],
			);

		}


	}


	echo "<br />" . $attempts_counter . " disciplina/turma<br />" . $successes_counter . " com succeso<br />" . $fails_counter . " falhas<br />";

	return '';
}

function set_points_to_subjects( $pre_reservations ) {


	foreach ( $pre_reservations as &$pre_reservation ) {
		$scheduales = getDurations( $pre_reservation['desired_time'] );
		$scheduales_qtd = count( $scheduales );

		/*
		 * Os seguintes pesos são, por padrão, para disciplinas obrigatória.
		 * (Na dúvida, trata com se fosse obrigatória)
		 */
		$w_v = 1; // Peso para vagas
		$w_t = 65; // Peso para quantidade de horários
		$w_n = 3; // Peso para natureza da disciplina: 'obrigatória' ou 'optativa'
		if (
			isset( $pre_reservation['class_subject_nature_of_subject'][0] ) &&
			intranet_fafar_utils_escape_and_clean_to_compare( $pre_reservation['class_subject_nature_of_subject'][0] ) === 'optativa'
		) {
			$w_v = 0.5;
			$w_t = 25;
			$w_n = 1;
		}

		$pre_reservation['score'] = ( ( (int) $pre_reservation['class_subject_number_vacancies_offered'] * $w_v ) + ( $scheduales_qtd * $w_t ) ) * $w_n;
	}

	return $pre_reservations;

}

function get_pre_reservations_data( $class_subjects ) {

	$pre_reservations_data = array();

	foreach ( $class_subjects as $subject ) {

		$schedules = parse_schedule( $subject['data']['desired_time'] );

		foreach ( $schedules as $schedule ) {

			// Início do semestre
			$date = '10/03/2025';
			if (
				isset( $subject['data']['desired_start_date'] ) && $subject['data']['desired_start_date']
			)
				$date = $subject['data']['desired_start_date'];

			// Fim do semestre
			$end_date = '12/07/2025';
			if (
				isset( $subject['data']['desired_end_date'] ) && $subject['data']['desired_end_date']
			)
				$end_date = $subject['data']['desired_end_date'];

			$new_pre_reservation_data = array(
				'class_subject_id' => $subject['id'],
				'class_subject_group' => [ $subject['data']['group'] ],
				'class_subject_code' => $subject['data']['code'],
				'class_subject_number_vacancies_offered' => $subject['data']['number_vacancies_offered'],
				'class_subject_nature_of_subject' => $subject['data']['nature_of_subject'],
				'start_time' => $schedule['start'],
				'end_time' => $schedule['end'],
				'weekdays' => $schedule['weekday'],
				'date' => $date,
				'end_date' => $end_date,
				'desired_time' => $subject['data']['desired_time'],
				'class_subject_group_owner' => $subject['group_owner'],
			);

			$index = index_of_reservation( $new_pre_reservation_data, $pre_reservations_data );
			if ( $index > -1 ) {
				$pre_reservations_data[ $index ]['class_subject_number_vacancies_offered'] += (int) $new_pre_reservation_data['class_subject_number_vacancies_offered'];
				$pre_reservations_data[ $index ]['class_subject_group'][] = $new_pre_reservation_data['class_subject_group'][0];
			} else {
				$pre_reservations_data[] = $new_pre_reservation_data;
			}

		}
	}

	return $pre_reservations_data;

}

function index_of_reservation( $new_pre_reservation_data, $pre_reservations_data ) {

	foreach ( $pre_reservations_data as $index => $pre_reservation_data ) {

		if (
			$pre_reservation_data['class_subject_id'] !== $new_pre_reservation_data['class_subject_id'] &&
			$pre_reservation_data['class_subject_group'][0] !== $new_pre_reservation_data['class_subject_group'][0] &&
			$pre_reservation_data['class_subject_code'] === $new_pre_reservation_data['class_subject_code'] &&
			$pre_reservation_data['start_time'] === $new_pre_reservation_data['start_time'] &&
			$pre_reservation_data['end_time'] === $new_pre_reservation_data['end_time'] &&
			$pre_reservation_data['weekdays'] === $new_pre_reservation_data['weekdays']
		)
			return $index;

	}

	return -1;

}

/*
 * Verifica se uma mesma disciplina já foi 
 * reservada no mesmo horário e dia da semana, 
 * mas de turma diferente, apenas. Se sim, 
 * não há necessidade de outra reserva.
 */
function has_reservation_for_another_group( $new_reservation ) {
	$reservations = intranet_fafar_api_get_submissions_by_object_name( 'reservation' );

	if (
		count( $reservations ) === 0 ||
		isset( $reservations['error_msg'] )
	)
		return false;

	$duplicate = array_filter( $reservations, function ($reservation) use ($new_reservation) {

		if (
			! isset( $reservation['data']['class_subject'] ) ||
			! $reservation['data']['class_subject']
		)
			return false;

		$class_subject_a = intranet_fafar_api_get_submission_by_id( $reservation['data']['class_subject'][0] );
		$class_subject_b = intranet_fafar_api_get_submission_by_id( $new_reservation['class_subject'][0] );

		if (
			isset( $class_subject_a['error_msg'] ) ||
			isset( $class_subject_b['error_msg'] )
		)
			return false;

		return (
			$class_subject_a['data']['code'] === $class_subject_b['data']['code'] &&
			$reservation['data']['start_time'] === $new_reservation['start_time'] &&
			$reservation['data']['end_time'] === $new_reservation['end_time'] &&
			$reservation['data']['weekdays'] === $new_reservation['weekdays']
		);
	} );


	return ( count( $duplicate ) > 0 );
}

function parse_schedule( $input ) {
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
		$weekday = $days_map[ $match[3] ] ?? null;

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


function convert_date( $date ) {
	$dt = DateTime::createFromFormat( 'd/m/Y', $date );
	return $dt ? $dt->format( 'Y-m-d' ) : false;
}

function getDurations( $input ) {
	preg_match_all( '/(\d{1,2}):(\d{2})\s+(\d{1,2}):(\d{2})/', $input, $matches, PREG_SET_ORDER );
	$durations = [];

	foreach ( $matches as $match ) {
		$startHour = (int) $match[1];
		$startMinute = (int) $match[2];
		$endHour = (int) $match[3];
		$endMinute = (int) $match[4];

		$startTime = $startHour * 60 + $startMinute;
		$endTime = $endHour * 60 + $endMinute;
		$durations[] = $endTime - $startTime;
	}

	return $durations;
}

function standardDeviation( $numbers ) {
	$n = count( $numbers );
	if ( $n === 0 )
		return 0; // Avoid division by zero

	$mean = array_sum( $numbers ) / $n;
	$sumSquaredDifferences = 0;

	foreach ( $numbers as $num ) {
		$sumSquaredDifferences += pow( $num - $mean, 2 );
	}

	return sqrt( $sumSquaredDifferences / $n );
}

// // Example usage
// $values = [10, 12, 23, 23, 16, 23, 21, 16];
// echo standardDeviation($values); // Output: Standard deviation


$class_subjects = intranet_fafar_api_get_submissions_by_object_name( 'class_subject' );

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

	<h5><?= count( $class_subjects ) ?> disciplinas encontradas </h5>

	<?php if ( isset( $_GET['generate'] ) ) : ?>

		<h5>Gerando reservas....</h5>

		<br />

		<?= generate_reservations( $class_subjects ); ?>

	<?php else : ?>

		<br />

		<a href="/gerar-reservas?generate=true" class="btn btn-primary text-decoration-none" title="Gerar reservas">
			Gerar
		</a>

	<?php endif; ?>

	<br />

	<h5><?= ( isset( $reservation_log[0] ) ? count( $reservation_log ) : '0' ) ?> reservas</h5>

	<table class="table">
		<thead>
			<tr>
				<th scope="col">Código Disciplina</th>
				<th scope="col">Vagas</th>
				<th scope="col">Pontos</th>
				<th scope="col">Natureza</th>
				<th scope="col">Horários</th>
				<th scope="col">Status</th>
				<th scope="col">Desc</th>
			</tr>
		</thead>
		<tbody>
			<?php
			if ( isset( $reservation_log ) && is_array( $reservation_log ) && count( $reservation_log ) > 0 ) {
				foreach ( $reservation_log as $row ) {
					echo '<tr>';
					echo '<td><a href="/visualizar-objeto?id=' . $row['sub_id'] . '" target="blank">' . $row['sub_code'] . '</td>';
					echo '<td>' . $row['vacancies'] . '</td>';
					echo '<td>' . $row['points'] . '</td>';
					echo '<td>' . print_r( $row['nature'], true ) . '</td>';
					echo '<td>' . $row['scheduale'] . '</td>';
					echo '<td>' . $row['status'] . '</td>';
					echo '<td>' . $row['desc'] . '</td>';
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