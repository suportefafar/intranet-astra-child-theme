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

$CURRENT_CLASSROOM          = null;
$POSSIBLES_CLASSROOMS       = [];
$POSSIBLES_CLASSROOMS_INDEX = 0;
$PATHS                      = [];
$ANTI_LOOP                  = 0;
$PATHS_COUNTER              = [ 'LEVEL' => 0, 'SUB_LEVEL' => 0 ];

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

function get_classrooms( $capacity ) {
	$raw_places = intranet_fafar_api_get_submissions_by_object_name(
		'place', 
		[], 
		[
			'check_permissions' => false,
		],
		false
	);

	$classrooms = array_filter( $raw_places['data'], function( $place ) use ( $capacity ) {
		return (
			! empty( $place['data']['capacity'] ) && 
			intval( $place['data']['capacity'] ) >= intval( $capacity ) && 
			$place['data']['object_sub_type'][0] === 'classroom'
		);
	} );

	return $classrooms;
}

function get_overlaps_reservations( $raw_reservation ) {

	if ( ! $raw_reservation )
		return array( 'error_msg' => 'Dados mal formados.' );

	// Verificar se dados necessários foram informados
	if (
		empty( $raw_reservation['date'] ) ||
		empty( $raw_reservation['start_time'] ) ||
		empty( $raw_reservation['end_time'] ) ||
		empty( $raw_reservation['frequency'] ) ||
		empty( $raw_reservation['place'] )
	) {
		return array( 'error_msg' => 'Data, tempo, frequência ou lugar não informado!' );
	}

	if (
		! is_string( $raw_reservation['date'] ) ||
		! is_string( $raw_reservation['start_time'] ) ||
		! is_string( $raw_reservation['end_time'] ) ||
		! is_array( $raw_reservation['frequency'] )
	) {
		return array( 'error_msg' => 'Data, tempo ou frequência do tipo errado!' );
	}

	// Validando formato de data
	$date = DateTime::createFromFormat( 'Y-m-d', $raw_reservation['date'] );
	if ( ! $date || $date->format( 'Y-m-d' ) !== $raw_reservation['date'] ) {
		return array( 'error_msg' => 'Data de início inválida!' );
	}

	// Verificar se *hora* de fim é posterior ao de início
	$s = new DateTime( $raw_reservation['start_time'] );
	$e = new DateTime( $raw_reservation['end_time'] );
	if ( $s >= $e )
		return array( 'error_msg' => 'Horário de início não pode ser depois de fim!' );

	/* 
	 * Verificando se sala/lugar existe
	 */
	/*
	 * No assistente de reservas, o ID do lugar é passado por parâmetro em URL.
	 * Isso causa uma treta.... E ai tem que fazer essas coisas: 
	 */
	if ( is_string( $raw_reservation['place'] ) ) {
		$decoded = json_decode( stripslashes( $raw_reservation['place'] ), true );
		if ( json_last_error() === JSON_ERROR_NONE ) {
			$raw_reservation['place'] = $decoded;
		} else {
			return array( 'error_msg' => 'Erro ao processar local da reserva!' );
		}
	}

	if ( empty( $raw_reservation['place'][0] ) )
		return array( 'error_msg' => 'Local com dados incorretos' );

	$place = intranet_fafar_api_get_submission_by_id( $raw_reservation['place'][0], false );

	if ( empty( $place ) )
		return array( 'error_msg' => 'Lugar desconhecido' );

	/* 
	 * Verificando se o usuário tem permissão de reserva nessa sala 
	 */
	if ( ! intranet_fafar_api_check_write_permission( $place ) ) {
		return array( 'error_msg' => 'Não autorizado!' );
	}

	// Verircar se *data* de fim é posterior ao de início, se houver data de fim
	if ( $raw_reservation['frequency'][0] !== 'once' ) {

		if ( empty( $raw_reservation['end_date'] ) ) {
			return array( 'error_msg' => 'Data de término não informada!' );
		}

		if ( ! is_string( $raw_reservation['end_date'] ) ) {
			return array( 'error_msg' => 'Data de término informada com tipo errado!' );
		}

		// Validando formato de data
		$start_date = DateTime::createFromFormat( 'Y-m-d', $raw_reservation['date'] );
		if ( ! $start_date || $start_date->format( 'Y-m-d' ) !== $raw_reservation['date'] ) {
			return array( 'error_msg' => 'Data de início inválida!' );
		}

		$end_date = DateTime::createFromFormat( 'Y-m-d', $raw_reservation['end_date'] );
		if ( ! $end_date || $end_date->format( 'Y-m-d' ) !== $raw_reservation['end_date'] ) {
			return array( 'error_msg' => 'Data de término inválida!' );
		}

		$s = DateTime::createFromFormat( 'H:i', $raw_reservation['start_time'] );
		$e = DateTime::createFromFormat( 'H:i', $raw_reservation['end_time'] );
		if ( ! $s || ! $e || $s >= $e ) {
			return array( 'error_msg' => 'Horário de início não pode ser depois de fim ou inválido!' );
		}
	}

	$title = 'Reserva ' . time();

	if ( ! empty( $raw_reservation['desc'] ) ) {
		$title = $raw_reservation['desc'];
	} else if ( isset( $raw_reservation['class_subject'][0] ) ) {
		$class_subject = intranet_fafar_api_get_submission_by_id( $raw_reservation['class_subject'][0] );

		if ( ! empty( $class_subject ) ) {
			$title = $class_subject['data']['code'] . ' (' . $class_subject['data']['group'] . ')';
		}
	}

	// Setando a prop 'title'
	$raw_reservation['title'] = $title;

	if ( $raw_reservation['frequency'][0] === 'weekly' ) {

		// Validando weekdays
		if ( empty( $raw_reservation['weekdays'][0] ) ) {
			return array( 'error_msg' => 'Dia(s) de semana não informado(s)!' );
		}

		if ( ! is_array( $raw_reservation['weekdays'] ) ) {
			return array( 'error_msg' => 'Dia(s) de semana do tipo errado!' );
		}

		foreach ( $raw_reservation['weekdays'] as $day ) {
			if ( ! is_numeric( $day ) || $day < 1 || $day > 7 ) {
				return array( 'error_msg' => 'Dia da semana inválido!' );
			}
		}

		// Gerando a prop 'dt_dstart' (Data início + Hora início) só com números
		$date = new DateTime( $raw_reservation['date'] );

		$time = DateTime::createFromFormat( 'H:i', $raw_reservation['start_time'] );

		$dt_start = $date->format( 'Ymd' ) . 'T' . $time->format( 'His' );

		// Gerando a prop 'byday' com o array retornado pelos checkboxes do CF7
		$byday = [];
		$weekday_map = [ 1 => 'MO', 2 => 'TU', 3 => 'WE', 4 => 'TH', 5 => 'FR', 6 => 'SA', 7 => 'SU' ];
		foreach ( $raw_reservation['weekdays'] as $day ) {
			if ( isset( $weekday_map[ $day ] ) ) {
				$byday[] = $weekday_map[ $day ];
			}
		}

		// Gerando a prop 'until' só com números
		$date = new DateTime( $raw_reservation['end_date'] );

		/*
		 * '+1 day' para cobrir o dia de encerramento, todo.
		 * Se informado '24102025T000000' cobre até o primeiro segundo de 24/10/2025
		 * O que eu quero é cobrir 24/10/2025 todo, 
		 * então: '25102025T000000'
		 */
		$date->modify( '+1 day' );
		$until = $date->format( 'Ymd' ) . 'T000000';

		/*
		 * Gerando RRULE string com a: 
		 * 'DTSTART:20240201T113000\nRRULE:FREQ=WEEKLY;INTERVAL=1;UNTIL=20241201;BYDAY=MO,FR'
		 */
		$new_rrule = 'DTSTART:' . $dt_start . '\nRRULE:FREQ=WEEKLY;INTERVAL=1;UNTIL=' . $until . ';BYDAY=' . implode( ',', $byday );

		// Gerando a prop 'duration'
		$start = DateTime::createFromFormat( 'H:i', $raw_reservation['start_time'] );
		$end = DateTime::createFromFormat( 'H:i', $raw_reservation['end_time'] );

		// Calculate the difference between the two times
		$interval = $start->diff( $end );

		// 'duration' é uma prop independente de 'rrule'
		$new_duration = $interval->format( '%H:%I' );

	} else {

		// Gerando a prop 'dt_dstart' (Data início + Hora início) só com números
		$date = new DateTime( $raw_reservation['date'] );
		$time = DateTime::createFromFormat( 'H:i', $raw_reservation['start_time'] );
		$dt_start = $date->format( 'Ymd' ) . 'T' . $time->format( 'His' );

		/*
		 * Gerando RRULE string com a: 
		 * 'DTSTART:20241107T113000\nRRULE:FREQ=DAILY;COUNT=1'
		 */
		$new_rrule = 'DTSTART:' . $dt_start . '\nRRULE:FREQ=DAILY;COUNT=1';

		// Gerando a prop 'duration'
		$start = DateTime::createFromFormat( 'H:i', $raw_reservation['start_time'] );
		$end = DateTime::createFromFormat( 'H:i', $raw_reservation['end_time'] );

		// Calculate the difference between the two times
		$interval = $start->diff( $end );

		// 'duration' é uma prop independente de 'rrule'
		$new_duration = $interval->format( '%H:%I' );

	}


	$skip_check_overlap = false;
	if ( ! empty( $raw_reservation['rrule'] ) && $raw_reservation['rrule'] == $new_rrule ) {

		$skip_check_overlap = true;

	}

	// É, pois é.... Medo....
	$raw_reservation['rrule'] = $new_rrule;

	$raw_reservation['duration'] = $new_duration;

	/*
	 * Sim, eu sei... Isso não é necessário. 
	 * Mas é medo de colocar mais de uma forma de sair com sucesso dessa função...
	 * Vou mudar re-escrever isso aqui quando o sistema de reservas já estiver bem testado.
	 * Na verdade, essa função toda....
	*/
	$overlaps_class_subjects = [];
	if ( ! $skip_check_overlap ) {

		$existing_reservations = intranet_fafar_api_get_reservations_by_place( $raw_reservation['place'][0] );

		/* 
		 * Gerar as datas dos reservas existentes
		 * Array ( [0] => 2024-02-05 00:00:00 [1] => 2024-02-02 00:00:00 [2] => ...
		 */
		$new_reservation_timestamps = intranet_fafar_rrule_get_all_occurrences( $raw_reservation['rrule'] );

		if ( empty( $new_reservation_timestamps ) ) {
			return array( 'error_msg' => 'RRULE inválido ou sem ocorrências. Confira os dados enviados.' );
		}

		// Aqui temos timestamps das reservas à ser registradas
		foreach ( $new_reservation_timestamps as $new_reservation_timestamp ) {

			foreach ( $existing_reservations as $existing_reservation ) {

				// Aqui estamos gerando as timestamps de cada evento registrado
				$existing_reservation_timestamps = intranet_fafar_rrule_get_all_occurrences( $existing_reservation['data']['rrule'] );

				if ( empty( $existing_reservation_timestamps ) ) {
					continue; // Pula RRULEs inválidas
				}

				foreach ( $existing_reservation_timestamps as $existing_reservation_timestamp ) {

					$existing = intranet_fafar_api_get_event_start_and_end( $existing_reservation_timestamp, $existing_reservation['data']['duration'] );
					$new = intranet_fafar_api_get_event_start_and_end( $new_reservation_timestamp, $raw_reservation['duration'] );

					if ( ! intranet_fafar_api_does_reservations_overlaps( $existing, $new ) ) continue;
					if ( empty( $existing_reservation['data']['class_subject'][0] ) ) continue;
					if ( in_array( $existing_reservation['data']['class_subject'][0], $overlaps_class_subjects ) ) continue;
						
					$overlaps_class_subjects[] = $existing_reservation['data']['class_subject'][0];

				}

			}

		}
	}

	return $overlaps_class_subjects;
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

function is_class_subject_multi_day( $class_subject ) {

	$schedules = parse_schedule( $class_subject['data']['desired_time'] );

	return ( ! empty( $schedules ) && count( $schedules ) > 1 );
	
}

function throw_on_path( $arr ) {
	global $PATHS, $PATHS_COUNTER;

	$level     = ( $PATHS_COUNTER['LEVEL']++ );
	//$sub_level = ( $PATHS_COUNTER['SUB_LEVEL']++ );

	//$PATHS[$level][$sub_level] = $arr;
	$PATHS[$level] = $arr;
}

function suggestion_routine( $reservation ) {
	global $ANTI_LOOP;

	$max_sub_levels = 3;
	if ( ! empty( $_POST['max_sub_levels'] ) && is_numeric( $_POST['max_sub_levels'] ) ) {
		$max_sub_levels = $_POST['max_sub_levels'];
	}

	if ( ( $ANTI_LOOP++ ) === $max_sub_levels ) {
		throw_on_path( [ 'Sub-Níveis máximo(' . $max_sub_levels . ') de procura alcançado' ] );
		$ANTI_LOOP = 0;
		return false;
	}

	$capacity = 0;
	if ( ! empty( $reservation['data']['class_subject']['data']['number_vacancies_offered'] ) ) {
		$capacity = $reservation['data']['class_subject']['data']['number_vacancies_offered'];
	} else if ( ! empty( $reservation['capacity'] ) ) {
		$capacity = $reservation['capacity'];
	}

	$possibles_classrooms = get_classrooms( $capacity );

	foreach( $possibles_classrooms as $possible_classroom ) {
		
		$reservation['place'] = [ $possible_classroom['id'] ];

		$overlaps = get_overlaps_reservations( $reservation );
		
		if( count( $overlaps ) === 0 ) {
			// Deu bom!!
			throw_on_path( $reservation ); 
			return true;
		}

		// Mais de uma disciplina está no caminho?
		if( count( $overlaps ) > 1 ) continue;

		// Pega a única disciplina que está no caminho
		$class_subject = intranet_fafar_api_get_submission_by_id( $overlaps[0], false );

		// Não achou? Não existe?
		if ( ! $class_subject ) continue;

		if (
			! empty( $reservation['class_subject']['id'] ) && 
			$reservation['class_subject']['id'] === $class_subject['id'] 
		) {
			continue;
		}

		// Transforma o campo 'Horário' da disciplina
		$schedules = parse_schedule( $class_subject['data']['desired_time'] );

		// Has multiple day on week ?
		if ( ! empty( $schedules ) && count( $schedules ) > 1 ) continue;

		$silly_reservation = [
			'date'          => ( ! empty( $class_subject['data']['desired_start_date'] ) ? $class_subject['data']['desired_start_date'] : '2025-08-18' ), //$_POST['date'] ,
			'end_date'      => ( ! empty( $class_subject['data']['desired_end_date'] ) ? $class_subject['data']['desired_end_date'] : '2025-12-13' ), //$_POST['end_date'] ,
			'start_time'    => $schedules[0]['start'], //$_POST['start_time'] ,
			'end_time'      => $schedules[0]['end'], //$_POST['end_time'] ,
			'frequency'     => [ 'weekly' ],
			'weekdays'      => $schedules[0]['weekday'], //$_POST['weekdays'],
			'capacity'      => $class_subject['data']['number_vacancies_offered'], //$_POST['capacity'],
			'class_subject' => $class_subject, //$_POST['capacity'],
		];

		// Tenta achar alguma sala vaga para essa disciplina que tá no caminho da anterior
		if ( ! suggestion_routine( $silly_reservation ) ) {
			continue;
		} else {
			// Deu bom!!
			throw_on_path( $reservation ); 
			return true;
		}
		
	}

	return false;

}

function lets_get_it_started() {
	global $PATHS;	

	$raw_reservation = [
		'date'       => '2025-08-18', //$_POST['date'] ,
		'end_date'   => '2025-12-13', //$_POST['end_date'] ,
		'start_time' => '13:30', //'09:00',//$_POST['start_time'] ,
		'end_time'   => '17:30', //'11:30',//$_POST['end_time'] ,
		'frequency'  => [ 'weekly' ],
		'weekdays'   => [ '1' ], //[ '1' ],//$_POST['weekdays'],
		'capacity'   => '18', //'50',//$_POST['capacity'],
	];

	if ( wp_get_environment_type() === 'production' ) {
		$raw_reservation = [
			'date'       => $_POST['date'] ,
			'end_date'   => $_POST['end_date'] ,
			'start_time' => $_POST['start_time'] ,
			'end_time'   => $_POST['end_time'] ,
			'frequency'  => [ 'weekly' ],
			'weekdays'   => $_POST['weekdays'],
			'capacity'   => $_POST['capacity'],
		];
	}

	suggestion_routine( $raw_reservation );

	// print_r( '<pre>' );
	// print_r( $PATHS );
	// print_r( '</pre>' );

	$suggestions = array_filter( $PATHS, fn( $item ) => ( empty( $item[0] ) ) );

	echo '<h5>' . count( $suggestions ) . ' passos encontrados.</h5>';

	echo '<ol class="list-group list-group-numbered">';
	foreach ( $suggestions as $suggestion_item ) {
		if ( ! empty( $suggestion_item[0] ) && is_string( $suggestion_item[0] ) ) continue;
		if ( empty( $suggestion_item['class_subject'] ) && empty( $suggestion_item['place'] )  ) continue;

		$place = intranet_fafar_api_get_submission_by_id( $suggestion_item['place'][0], false );

		$place_desc = '----';
		if ( ! empty( $place['data']['number'] ) ) { 
			$place_desc = $place['data']['number'];
		}

		$subject_desc = 'SUA RESERVA';
		if ( ! empty( $suggestion_item['class_subject'] ) ) {
			$code  = $suggestion_item['class_subject']['data']['code'];
			$group = $suggestion_item['class_subject']['data']['group'];

			$subject_desc = $code . '/' . $group;
		}
 
		echo '<li class="list-group-item">Inserir <b>' . $subject_desc . '</b> na <b>' . $place_desc . '</b></li>';
	}
	echo '</ol>';

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

	<br />
	<h5>Sugestão (Beta)</h5>
	<br />
	<form action="/sugestao-de-reservas-de-salas?action=search" method="post" enctype="multipart/form-data" class="mb-5">
		<div class="form-group mb-3">
			<label for="event_date">* Data início </label>
			<input type="date" class="form-control" id="date" name="date" min="2024-09-10"
				aria-required="true" required />
		</div>
		<div class="form-group mb-3">
			<label for="event_date">* Data fim </label>
			<input type="date" class="form-control" id="end_date" name="end_date" min="2024-09-10"
				aria-required="true" required />
		</div>
		<div class="form-group mb-3">
			<label for="start_time">* Hora Início </label>
			<input type="time" class="form-control" id="start_time" name="start_time" aria-required="true" required />
		</div>
		<div class="form-group mb-3">
			<label for="end_time">* Hora Fim </label>
			<input type="time" class="form-control" id="end_time" name="end_time" aria-required="true" required />
		</div>
		<div class="form-group mb-3 btn-group" role="group" aria-label="Basic checkbox toggle button group">
			<input type="checkbox" class="btn-check" id="btncheck1" autocomplete="off" value="1" name="weekdays[]">
			<label class="btn btn-outline-primary" for="btncheck1">Segunda</label>

			<input type="checkbox" class="btn-check" id="btncheck2" autocomplete="off" value="2" name="weekdays[]">
			<label class="btn btn-outline-primary" for="btncheck2">Terça</label>

			<input type="checkbox" class="btn-check" id="btncheck3" autocomplete="off" value="3" name="weekdays[]">
			<label class="btn btn-outline-primary" for="btncheck3">Quarta</label>

			<input type="checkbox" class="btn-check" id="btncheck4" autocomplete="off" value="4" name="weekdays[]">
			<label class="btn btn-outline-primary" for="btncheck4">Quinta</label>

			<input type="checkbox" class="btn-check" id="btncheck5" autocomplete="off" value="5" name="weekdays[]">
			<label class="btn btn-outline-primary" for="btncheck5">Sexta</label>
		</div>
		<div class="form-group mb-3">
			<label for="capacity">* Capacidade </label>
			<input type="number" class="form-control" id="capacity" name="capacity" min="1" max="200" placeholder="20"
				aria-required="true" required />
		</div>
		<div class="form-group mb-3">
			<label for="capacity">* Sub-níveis </label>
			<input type="number" class="form-control" id="capacity" name="max_sub_levels" min="1" max="200" placeholder="10" value="2"
				aria-required="true" required />
		</div>
		<button type="submit" class="btn btn-primary">Buscar Salas</button>
	</form>
	<?php if (
		isset( $_GET['action'] ) &&
		$_GET['action'] === 'search' 
	) : ?>
		<br />
		<?php lets_get_it_started(); ?>
		<br />
		<br />
		<br />
	<?php else : ?>

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