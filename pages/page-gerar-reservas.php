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

function generate_reservation_log( $reservation_data, $status = 'success', $msg = '' ) {

	$start_time = ( ! empty( $reservation_data['start_time'] ) ? $reservation_data['start_time'] : '' );
	$end_time   = ( ! empty( $reservation_data['end_time'] ) ? $reservation_data['end_time'] : '' );
	$weekdays   = ( ! empty( $reservation_data['weekdays'] ) ? $reservation_data['weekdays'] : [] );
	$nature     = ( ! empty( $reservation_data['class_subject_nature_of_subject'] ) ? $reservation_data['class_subject_nature_of_subject'] : [] );
	

	return array(
					'sub_id'    => ( ! empty( $reservation_data['class_subject_id'] ) ? $reservation_data['class_subject_id'] : '' ),
					'sub_code'  => ( ! empty( $reservation_data['class_subject_code'] ) ? $reservation_data['class_subject_code'] : '' ),
					'vacancies' => ( ! empty( $reservation_data['class_subject_number_vacancies_offered'] ) ? $reservation_data['class_subject_number_vacancies_offered'] : '' ),
					'scheduale' => implode( ' ', array( $start_time, $end_time, implode( ' ', $weekdays ) ) ),
					'status'    => $status,
					'desc'      => $msg,
					'points'    => ( ! empty( $reservation_data['score'] ) ? $reservation_data['score'] : '' ),
					'nature'    => implode( ' ', $nature ),
				);
}

function generate_reservations( $class_subjects = [] ) {


	$class_subjects_raw = get_class_subjects();

	if ( empty( $class_subjects_raw ) ) {
		echo 'Nenhuma reserva encontrada!';
		return false;
	}

	$class_subjects = $class_subjects_raw['data'];

	if ( empty( $class_subjects ) ) {
		echo 'Não há disciplinas cadastradas para reservas.';
		return false;
	}

	$reservation_logs        = [];
	$filtered_class_subjects = [];
	// Filtra disciplinas com mais de 80 vagas, disciplinas práticas, etc.
	foreach ( $class_subjects as $class_subject ) {

		$sd = $class_subject['data'];

		$log_subject = [
			'sub_id'    => $sd['id'],
			'sub_code'  => $sd['code'],
			'vacancies' => $sd['number_vacancies_offered'],
			'scheduale' => $sd['desired_time'],
			'points'    => 0,
			'nature'    => $sd['nature_of_subject'],
		];

		if ( intval( $sd['number_vacancies_offered'] ) <= 0 ) {
			$reservation_logs[] = generate_reservation_log( $log_subject, 'Falha', 'Número de mínimo de vagas não ofertada.' );
			continue;
		}
		if ( intval( $sd['number_vacancies_offered'] ) >= 80 ) {
			$reservation_logs[] = generate_reservation_log( $log_subject, 'Falha', 'Número de máximo de vagas excedido.' );
			continue;
		}
		if ( empty( $sd['desired_time'] ) ) {
			$reservation_logs[] = generate_reservation_log( $log_subject, 'Falha', 'Não informado horário.' );
			continue;
		}
		if ( count( parse_schedule( $sd['desired_time'] ) ) <= 0 ) {
			$reservation_logs[] = generate_reservation_log( $log_subject, 'Falha', 'Formato de horário não aceito.' );
			continue;
		}
		if ( str_contains( intranet_fafar_utils_escape_and_clean_to_compare( $sd['name_of_subject'] ), 'estagio' ) ) {
			$reservation_logs[] = generate_reservation_log( $log_subject, 'Falha', 'Ignorado: disciplina de estágio.' );
			continue;
		}
		if ( str_contains( intranet_fafar_utils_escape_and_clean_to_compare( $sd['name_of_subject'] ), 'monografia' ) ) {
			$reservation_logs[] = generate_reservation_log( $log_subject, 'Falha', 'Ignorado: disciplina de monografia.' );
			continue;
		}
		if ( str_contains( strtoupper( $sd['group'] ), 'P' ) ) {
			$reservation_logs[] = generate_reservation_log( $log_subject, 'Falha', 'Ignorado: disciplina prática.' );
			continue;
		}
		if ( 
			! empty( $sd['use_on_auto_reservation'][0] ) && 
			strtoupper( $sd['use_on_auto_reservation'][0] ) !== 'SIM'
		 ) {
			$reservation_logs[] = generate_reservation_log( $log_subject, 'Falha', 'Uso para geração de reservas desabilitado.' );
			continue;
		}

		$filtered_class_subjects[] = $class_subject;
	}

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

			$new_reservation_formatted = intranet_fafar_api_create_or_update_reservation( $new_pre_reservation );

			

			if ( ! isset( $new_reservation_formatted['error_msg'] ) ) {

				$new_reservation = intranet_fafar_api_create( $new_reservation_formatted );

				if ( ! isset( $new_reservation['error_msg'] ) ) {
					$successes_counter++;

					$reservation_logs[] = generate_reservation_log( $data, 'Sucesso' );

					$made_reservation = true;

					break;
				}

			}
		}

		if ( ! $made_reservation ) {
			$fails_counter++;
			$reservation_logs[] = generate_reservation_log( $data, 'Falha', 'Sem sala disponível.' );
		}

	}

	echo '<br />Sucessos: ' . $successes_counter ;
	echo '<br />Disciplina/Turma: ' . $attempts_counter ;
	echo '<br />Erros: ' . ( $fails_counter + count( array_filter( $reservation_logs, fn( $r ) => $r['status'] === 'Falha' ) ) );

	echo '<div>';
	echo '<span><strong>Log de Reservas</strong> (' . count( $reservation_logs ) . ' logs)</span>';
	$reservation_logs_table_html = '
					<table class="table">
						<thead>
							<tr>
								<th scope="col">#</th>
								<th scope="col">Código</th>
								<th scope="col">Vagas</th>
								<th scope="col">Horário</th>
								<th scope="col">Natureza</th>
								<th scope="col">Pontos</th>
								<th scope="col">Status</th>
								<th scope="col">Desc</th>
							</tr>
						</thead>
						
						</tbody>';

	$count = 1;
	foreach ( $reservation_logs as $log ) {
		$reservation_logs_table_html .= '
						<tr>
							<th scope="row">' . $count++ . '</th>
							<td><a href="/visualizar-objeto/?id=' . $log['sub_id'] . '" blank="_target">' . $log['sub_code'] . '</a></td>
							<td>' . $log['vacancies'] . '</td>
							<td>' . $log['scheduale'] . '</td>
							<td>' . $log['nature'] . '</td>
							<td>' . $log['points'] . '</td>
							<td>' . $log['status'] . '</td>
							<td>' . $log['desc'] . '</td>
						</tr>';
	}

	$reservation_logs_table_html .= '</table>';

	echo $reservation_logs_table_html;
	
	echo '</div>';


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
			$date = '11/08/2025';
			if (
				isset( $subject['data']['desired_start_date'] ) && $subject['data']['desired_start_date']
			)
				$date = $subject['data']['desired_start_date'];

			// Fim do semestre
			$end_date = '13/12/2025';
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

function parse_schedule_new( $input ) {

	$result = [];

	// The new regex pattern to match the day, start time, end time, and ignore the dates.
	// It captures the day abbreviation (\w{3}), the start time (\d{2}:\d{2}), and the end time (\d{2}:\d{2}).
	preg_match_all( '/(\w{3})\s+(\d{2}:\d{2})\s+-\s+(\d{2}:\d{2})/', $input, $matches, PREG_SET_ORDER );

	// Mapeamento dos dias da semana para números (Seg = 1, Ter = 2, ..., Dom = 7)
	$days_map = [
		'Seg' => 1, 'Ter' => 2, 'Qua' => 3,
		'Qui' => 4, 'Sex' => 5, 'Sab' => 6, 'Dom' => 7
	];

	foreach ( $matches as $match ) {
		// The captured groups are now at different indices.
		$weekday_abbr = $match[1];
		$start = $match[2];
		$end = $match[3];

		// Use the mapping to get the weekday number.
		$weekday = $days_map[ $weekday_abbr ] ?? null;

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

// --------------------------------------------------------------------------

function get_class_subjects() {
	return intranet_fafar_api_get_submissions_by_object_name(
		'class_subject', 
		[], 
		[ 'check_permissions' => false ],
		false
	);
}

function get_reservations() {
	return intranet_fafar_api_get_submissions_by_object_name(
		'reservation', 
		[], 
		[ 'check_permissions' => false ],
		false
	);
}

function generate_checkpoint() {

	$class_subjects = get_class_subjects();

	$reservations = get_reservations();

	if ( empty( $class_subjects ) && empty( $reservations ) ) {
		echo 'Sem reservas e disciplinas para salvar!';
		return false;
	}

	if ( empty( $class_subjects ) ) {
		$class_subjects = [];
	}

	if ( empty( $reservations ) ) {
		$reservations = [];
	}

	$checkpoint = [
		'class_subjects' => json_encode( $class_subjects['data'] ), 
		'reservations'  => json_encode( $reservations['data'] ), 
	];

    $upload_dir_info = wp_upload_dir();
    $upload_dir_path = $upload_dir_info['basedir'];

    $filename = 'last-checkpoint.json';
    $file_path = trailingslashit( $upload_dir_path ) . $filename;

    $file_content = json_encode( $checkpoint );

    global $wp_filesystem;
    if ( empty( $wp_filesystem ) ) {
        require_once( ABSPATH . '/wp-admin/includes/file.php' );
        WP_Filesystem();
    }

    if ( $wp_filesystem ) {

        $success = $wp_filesystem->put_contents( $file_path, $file_content, FS_CHMOD_FILE );

        if ( $success ) {
			if ( ! empty( $class_subjects['data'] ) ) {
				echo "<br />Disciplinas: " . count( $class_subjects['data'] );
			}
			if ( ! empty( $reservations['data'] ) ) {
				echo "<br />Reservas: " . count( $reservations['data'] );
			}
            echo "<br />Salvo novo checkpoint em: " . esc_html($file_path);
        } else {
            echo "Falha ao escrever no arquivo.";
        }
    } else {
        echo "WP_Filesystem could not be initialized.";
    }

}

function delete_all_class_subjects() {

	$class_subjects = get_class_subjects();

	if ( empty( $class_subjects ) ) return false;
	
	foreach ( $class_subjects['data'] as $class_subject ) {

		intranet_fafar_api_delete(
			$class_subject, 
			false, 
			false
		);

	}

	return true;

}

function delete_all_reservations() {

	$reservations = get_reservations();

	if ( empty( $reservations ) ) return false;
	
	foreach ( $reservations['data'] as $reservation ) {

		intranet_fafar_api_delete(
			$reservation, 
			false, 
			false
		);

	}

	return true;

}

function create_submissions( $submissions ) {

	foreach ( $submissions as $submission ) {
		intranet_fafar_api_create( $submission, false );
	}

}

function use_last_checkpoint() {
    $upload_dir_info = wp_upload_dir();
    $upload_dir_path = $upload_dir_info['basedir'];

    $filename = 'last-checkpoint.json';
    $file_path = trailingslashit( $upload_dir_path ) . $filename;

    global $wp_filesystem;
    if ( empty( $wp_filesystem ) ) {
        require_once( ABSPATH . '/wp-admin/includes/file.php' );
        WP_Filesystem();
    }

    if ( $wp_filesystem && $wp_filesystem->exists( $file_path ) ) {

        $file_content = $wp_filesystem->get_contents( $file_path );

        if ( $file_content !== false ) {
    
			delete_all_class_subjects();
			delete_all_reservations();

			$checkpoint = json_decode( $file_content, true );

			if ( empty( $checkpoint['class_subjects'] ) && empty( $checkpoint['reservations'] ) ) {
				echo 'Sem reservas e disciplinas para restaurar!';
				return false;
			}

			if ( ! empty( $checkpoint['class_subjects'] ) ) {
				$class_subjects = json_decode( $checkpoint['class_subjects'], true );
				create_submissions( $class_subjects );
				echo '<br />Disciplinas restauradas: ' . count( $class_subjects ); 
			}

			if ( ! empty( $checkpoint['reservations'] ) ) {
				$reservations = json_decode( $checkpoint['reservations'], true );
				create_submissions( $reservations );
				echo '<br />Reservas restauradas: ' . count( $reservations ); 
			}

            
        } else {
            echo "Failed to read the contents of the file.";
        }
    } else {
        echo "The file " . esc_html($file_path) . " does not exist or WP_Filesystem could not be initialized.";
    }
}

function reformat_schedule( $input ) {
	// A regex busca o dia, a hora de início e a hora de fim em cada linha.
	// O padrão: `(\w{3})` captura 3 letras (o dia).
	// `(\d{2}:\d{2})\s+-\s+(\d{2}:\d{2})` captura o intervalo de tempo.
	$pattern = '/^(\w{3})\s+(\d{2}:\d{2})\s+-\s+(\d{2}:\d{2}).*$/m';

	// O `preg_replace` substitui a string encontrada pelo novo formato.
	// `$1` é o primeiro grupo capturado (o dia).
	// `$2` e `$3` são o segundo e terceiro grupos (as horas).
	// A flag `m` (multiline) permite que o ^ e $ funcionem em cada linha da string.
	$output = preg_replace( $pattern, '$2 $3 ($1)', $input );
	
	// Retorna a string reformatada.
	return $output;
}

function is_the_same( $class_subject_a, $class_subject_b ) {

    /*
     * Se já não existe por CÓDIGO, NOME e TURMA 
     * Obs.: No caso do NOME, aplicar low case, retirar acentos e espaços
     */
    if( 
        ! isset( $class_subject_a['code'] ) || 
        ! isset( $class_subject_a['name_of_subject'] ) || 
        ! isset( $class_subject_a['group'] )
      ) return false;

    if( 
        ! isset( $class_subject_a['code'] ) || 
        ! isset( $class_subject_a['name_of_subject'] ) || 
        ! isset( $class_subject_a['group'] )
      ) return false;

    // Códigos
    $code_a = intranet_fafar_utils_escape_and_clean_to_compare( $class_subject_a['code'] );
    $code_b = intranet_fafar_utils_escape_and_clean_to_compare( $class_subject_b['code'] );

    // Nomes
    // $name_a = intranet_fafar_utils_escape_and_clean_to_compare( $class_subject_a['name_of_subject'] );
    // $name_b = intranet_fafar_utils_escape_and_clean_to_compare( $class_subject_b['name_of_subject'] );

    // Turmas
    $group_a = intranet_fafar_utils_escape_and_clean_to_compare( $class_subject_a['group'] );
    $group_b = intranet_fafar_utils_escape_and_clean_to_compare( $class_subject_b['group'] );

    if( 
        intranet_fafar_utils_escape_and_clean_to_compare( $code_a ) === intranet_fafar_utils_escape_and_clean_to_compare( $code_b ) && 
        intranet_fafar_utils_escape_and_clean_to_compare( $name_a ) === intranet_fafar_utils_escape_and_clean_to_compare( $name_b ) && 
        intranet_fafar_utils_escape_and_clean_to_compare( $group_a ) === intranet_fafar_utils_escape_and_clean_to_compare( $group_b )  
      ) return true;

    return false;

}

function import_class_subjects( $data, $group_owner = '' ) {
    $error_count         = 0;
	$uptaded_count       = 0;
	$added_count       = 0;
	$not_a_fafar_subject = 0;
	$success_count       = 0;
    $total_count         = count( $data );

	$class_subjects_raw = get_class_subjects();
	$class_subjects     = $class_subjects_raw['data'];
	
	$class_subjects_added   = [];
	$class_subjects_updated = [];

    foreach( $data as $item ) {
        /* 
         * Aplica o filtro de código de disciplina se o 
         * curso informado não for da Pós.
         * Filtro: 
         * Verifica se tem código e se ele tem 
         * 'ACT', 'ALM', 'FAF', 'FAS', 'PFA' ou 'NUT' 
         */
        if( 
            ! str_contains( $item['curso'], 'PPG' ) && 
            preg_match( '/ACT|ALM|FAF|FAS|PFA|NUT/', $item['codigo'] ) !== 1
          ) {
            $not_a_fafar_subject++;
            continue;
        }

        // Decidimos que é melhor errar com uma optativa que com uma obrigatória
        $nature_of_subject = 'Obrigatória';
        if( 
            isset( $item['natureza'] ) && 
            intranet_fafar_utils_escape_and_clean_to_compare( $item['natureza'] ) === 'optativa'
          ) $nature_of_subject = 'Optativa';
        
        /*
         * Essa condição maluca se dá pelo fato do relatório 
         * do SIGA - conseguido pelo colegiado -, trazer essa informação
         * como 'Téo.' ou 'Prá.'. E para não forçar a todos que usem essa 
         * abreviação, então se faz necessário abracar todas as possibidades 
         */
        $type = ( isset( $item['tipo'] ) ? $item['tipo'] : '' );
        if( 
            str_contains( 
                intranet_fafar_utils_escape_and_clean_to_compare( $type ), 
                'teo' 
            ) 
        ) $type = 'Teórica';
        else if(
            str_contains( 
                intranet_fafar_utils_escape_and_clean_to_compare( $type ), 
                'pra' 
            )
        ) $type = 'Prática';
        else if(
            str_contains( 
                intranet_fafar_utils_escape_and_clean_to_compare( $type ), 
                'amb' 
            )
        ) $type = 'Ambas';

        // Verificar todos as colunas, exceto 'Código', 'Curso', 'Turma', 'Horario', 'Vagas'
        $inicio                = ( isset( $item['inicio'] ) ? $item['inicio'] : '' );
        $fim                   = ( isset( $item['fim'] ) ? $item['fim'] : '' );
        $carga_horaria         = ( isset( $item['carga horaria'] ) ? $item['carga horaria'] : 0 );
        $credits               = ( ( (float) $carga_horaria ) / 15 );
        $curso                 = ( isset( $item['curso'] ) ? $item['curso'] : '' );
        $nivel                 = ( isset( $item['nivel'] ) ? $item['nivel'] : '' );
        $departamento          = ( isset( $item['departamento'] ) ? $item['departamento'] : '' );
        $ajuste                = ( isset( $item['ajuste'] ) ? $item['ajuste'] : 0 );
        $professores           = ( isset( $item['professores'] ) ? $item['professores'] : '' );
        $matrizes_curriculares = ( isset( $item['matrizes curriculares'] ) ? $item['matrizes curriculares'] : '' );

        $new_class_subject = array(
            'code'                         => intranet_fafar_utils_escape_and_clean( $item['codigo'] ),
            'name_of_subject'              => intranet_fafar_utils_escape_and_clean( $item['nome'] ),
            'group'                        => intranet_fafar_utils_escape_and_clean( $item['turma'] ),
            'nature_of_subject'            => array( $nature_of_subject ),
            'number_vacancies_offered'     => intranet_fafar_utils_escape_and_clean( $item['vagas'] ),
            'desired_time'                 => intranet_fafar_utils_escape_and_clean( $item['horario'] ),
            'desired_start_date'           => intranet_fafar_utils_escape_and_clean( $inicio ),
            'desired_end_date'             => intranet_fafar_utils_escape_and_clean( $fim ),
            'course_load'                  => intranet_fafar_utils_escape_and_clean( $carga_horaria ),
            'credits_of_subject'           => $credits,
            'course'                       => array( intranet_fafar_utils_escape_and_clean( $curso ) ),
            'level'                        => array( intranet_fafar_utils_escape_and_clean( $nivel, 'capitalized' ) ),
            'departament'                  => array( intranet_fafar_utils_escape_and_clean( $departamento ) ),
            'type'                         => array( intranet_fafar_utils_escape_and_clean( $type, 'capitalized' ) ),
            'adjustment'                   => intranet_fafar_utils_escape_and_clean( $ajuste ),
            'professors'                   => intranet_fafar_utils_escape_and_clean( $professores ),
            'version_of_curriculum_matrix' => intranet_fafar_utils_escape_and_clean( $matrizes_curriculares ),
			'use_on_auto_reservation'      => [ 'Sim' ],
        );

		if ( $_POST['convert_schedule_format'] ) {
			$new_class_subject['desired_time'] = reformat_schedule( $new_class_subject['desired_time'] );
		}

        $has_class_subject = false;
		$class_subject_to_be_updated = [];
        foreach( $class_subjects as $class_subject ) {

            if( is_the_same( $class_subject['data'], $new_class_subject ) ) {
				$has_class_subject = true;
				$class_subject_to_be_updated = $class_subject;
				break;
			}

        }

		// Cria ou Atualiza
        if( $has_class_subject ) {
			
			$class_subject_to_be_updated['data'] = $new_class_subject;
            
			$result = intranet_fafar_api_update(
				$class_subject_to_be_updated['id'],
				$class_subject_to_be_updated,
				false
			);
			
			if( isset( $result['error_msg'] ) ) {
				print_r( $result['error_msg'] );
				$error_count++;
			}
			
			$uptaded_count++;
			$class_subjects_updated[] = $class_subject_to_be_updated;
			
        } else {
			
			$new_class_subject_full = array( 
				'object_name' => 'class_subject',
				'owner'       => '',
				'group_owner' => $group_owner,
				'permissions' => '777',
				'data'        => $new_class_subject, 
			);
			
			$result = intranet_fafar_api_create( $new_class_subject_full );
			
			if( isset( $result['error_msg'] ) ) {
				print_r( $result['error_msg'] );
				$error_count++;
			}
			
			$added_count++;
			
			$class_subjects_added[] = $new_class_subject_full;

		}

    }

    $success_count = $total_count - $error_count - $not_a_fafar_subject;

	echo '<br /><span>Total: ' . $total_count . '</span>';
	echo '<br /><span>Sucesso: ' . $success_count . '</span>';
	echo '<br /><span>Adicionadas: ' . $added_count . '</span>';
	echo '<br /><span>Atualizadas: ' . $uptaded_count . '</span>';
	echo '<br /><span>Erros: ' . $error_count . '</span>';
	echo '<br /><span>Disciplinas Não FAFAR: ' . $not_a_fafar_subject . '</span>';

	render_imported_subjects_table( $class_subjects_added, $class_subjects_updated );
}

function render_imported_subjects_table( $class_subjects_added, $class_subjects_updated ) {

	echo '<div>';
	echo '<span><strong>Disciplinas Adicionadas</strong> (' . count( $class_subjects_added ) . ' disciplinas)</span>';
	$class_subjects_added_table_html = '
					<table class="table">
						<thead>
							<tr>
								<th scope="col">#</th>
								<th scope="col">Código</th>
								<th scope="col">Nome</th>
								<th scope="col">Turma</th>
								<th scope="col">CH</th>
								<th scope="col">Vagas</th>
								<th scope="col">Horário</th>
							</tr>
						</thead>
						
						</tbody>';

	$count = 1;
	foreach ( $class_subjects_added as $added ) {
		$class_subjects_added_table_html .= '
						<tr>
							<th scope="row">' . $count++ . '</th>
							<td>' . $added['data']['code'] . '</td>
							<td>' . $added['data']['name_of_subject'] . '</td>
							<td>' . $added['data']['group'] . '</td>
							<td>' . $added['data']['course_load'] . '</td>
							<td>' . $added['data']['number_vacancies_offered'] . '</td>
							<td>' . $added['data']['desired_time'] . '</td>
						</tr>';
	}

	$class_subjects_added_table_html .= '</table>';

	echo $class_subjects_added_table_html;
	
	echo '</div>';

	echo '<div>';
	echo '<span><strong>Disciplinas Atualizadas</strong> (' . count( $class_subjects_updated ) . ' disciplinas)</span>';
	$class_subjects_updated_table_html = '
					<table class="table">
						<thead>
							<tr>
								<th scope="col">#</th>
								<th scope="col">Código</th>
								<th scope="col">Nome</th>
								<th scope="col">Turma</th>
								<th scope="col">CH</th>
								<th scope="col">Vagas</th>
								<th scope="col">Horário</th>
							</tr>
						</thead>
						
						</tbody>';

	$count = 1;
	foreach ( $class_subjects_updated as $updated ) {
		$class_subjects_updated_table_html .= '
						<tr>
							<th scope="row">' . $count++ . '</th>
							<td>' . $updated['data']['code'] . '</td>
							<td>' . $updated['data']['name_of_subject'] . '</td>
							<td>' . $updated['data']['group'] . '</td>
							<td>' . $updated['data']['course_load'] . '</td>
							<td>' . $updated['data']['number_vacancies_offered'] . '</td>
							<td>' . $updated['data']['desired_time'] . '</td>
						</tr>';
	}

	$class_subjects_updated_table_html .= '</table>';

	echo $class_subjects_updated_table_html;
	
	echo '</div>';

}

function read_csv_class_subjects() {

	if ( empty( $_FILES['class_subjects_csv'] ) ) {
		echo 'Nenhum arquivo importado.';
		return false;
	}

	$upload_overrides = [ 'test_form' => false ];
	$uploaded_file = wp_handle_upload( $_FILES['class_subjects_csv'], $upload_overrides );

	if ( ! isset( $uploaded_file['file'] ) ) {
		echo 'Falha ao importar o arquivo.';
		return false;
	}

	$file_path = $uploaded_file['file'];
	$csv_data_array = [];

	if ( ( $handle = fopen( $file_path, 'r' ) ) === false ) {
		echo 'Falha ao abrir o arquivo.';
		return false;
	}

	$encoding = 'Windows-1252';

	rewind( $handle );

	$header_raw = fgetcsv( $handle, 500, ';', '"', '\\' );
	// Limpando e tratando cada celula do header
	$header     = array_map(
		function ( $col ) use ( $encoding ) {
			$col = mb_convert_encoding( $col, 'UTF-8', $encoding );
			$col = intranet_fafar_utils_remove_accents( $col );
			$col = strtolower( $col );
			return trim( $col );
		}, 
		$header_raw
	);

	if (
		! in_array( 'codigo', $header ) || 
		! in_array( 'curso', $header ) || 
		! in_array( 'turma', $header ) || 
		! in_array( 'horario', $header ) || 
		! in_array( 'vagas', $header )
	) {
		echo 'Faltando uma das colunas obrigatórias(Código, Curso, Horário, Turma e/ou Vagas).';
        return false;
	}

	while ( ( $row_raw = fgetcsv( $handle, 500, ';', '"', '\\' ) ) !== false ) {
		// Limpando e tratando cada celula de cada linha
		$row = array_map(
			function ( $col ) use ( $encoding ) {
				$col = mb_convert_encoding( $col, 'UTF-8', $encoding );
				$col = intranet_fafar_utils_remove_accents( $col );
				$col = strtoupper( $col );
				return trim( $col );
			},
			$row_raw
		);

		// Se a linha a ser analisada tem o mesmo tamanho que o header
		if( count( $header ) === count( $row ) )
			$data[] = array_combine( $header, $row );
	}

	fclose( $handle );

	return $data;

}

function get_group_owner() {

	if ( isset( $_POST['group_owner'] ) && is_string( $_POST['group_owner'] ) ) {
		return sanitize_text_field( wp_unslash( $_POST['group_owner'] ) );
	}

	return wp_get_current_user()->roles[0];

}

function import_class_subjects_routine() {
	
	$data = read_csv_class_subjects();

	if ( ! $data ) return false;

	$group_owner = get_group_owner();

	import_class_subjects( $data, $group_owner );

}

$class_subjects = get_class_subjects();
$count_class_subjects = 0;
if ( ! empty( $class_subjects['data'] ) ) {
	$count_class_subjects = count( $class_subjects['data'] );
}

$reservations = get_reservations();
$count_reservations = 0;
if ( ! empty( $reservations['data'] ) ) {
	$count_reservations = count( $reservations['data'] );
}

get_header(); ?>

<?php if ( astra_page_layout() == 'left-sidebar' ) : ?>

	<?php get_sidebar(); ?>

<?php endif ?>

<div id="primary" <?php astra_primary_class(); ?>>

	<?php astra_primary_content_top(); ?>

	<?php astra_content_page_loop(); ?>

	<div class="d-flex flex-column gap-3">
		<h6>Disciplinas cadastradas: <?= $count_class_subjects; ?></h6>
		<h6>Reservas realizadas: <?= $count_reservations; ?></h6>

		<div class="btn-group" role="group" aria-label="Basic mixed styles example">
			<a href="#" 
				class="btn btn-danger text-decoration-none" 
				title="Excluir todas as disciplinas" 
				onclick="confirmAlert('Tem certeza que deseja EXCLUIR todas as DISCIPLINAS?', 'gerar-reservas?action=delete-class-subjects')">
				<i class="bi bi-trash3"></i>
				Excluir Disciplinas
			</a>
			<a href="#" 
				class="btn btn-danger text-decoration-none" 
				title="Excluir todas as reservas" 
				onclick="confirmAlert('Tem certeza que deseja EXCLUIR todas as RESERVAS?', 'gerar-reservas?action=delete-reservations')">
				<i class="bi bi-trash3"></i>
				Excluir Reservas
			</a>
			<a href="/gerar-reservas?action=generate-checkpoint" 
				class="btn btn-primary text-decoration-none" 
				title="Salvar todas as DISCIPLINAS e RESERVAS em banco de dados separado">
				<i class="bi bi-node-plus"></i>
				Gerar Novo Checkpoint
			</a>
			<a href="#" 
				class="btn btn-primary text-decoration-none" 
				title="Restaura o último checkpoint criado. APAGA todas as DISCIPLINAS e RESERVAS atuais." 
				onclick="confirmAlert('Tem certeza que deseja continuar? Isso APAGA todas as DISCIPLINAS e RESERVAS atuais', 'gerar-reservas?action=use-last-checkpoint')">
				<i class="bi bi-clock-history"></i>
				Usar Último Checkpoint
			</a>
			<a href="/gerar-reservas?action=import-subjects-form" 
				class="btn btn-primary text-decoration-none" 
				title="Importar disciplinas por .csv">
				<i class="bi bi-cloud-upload"></i>
				Importar Disciplinas
			</a>
			<a href="#" 
				class="btn btn-primary text-decoration-none" 
				title="Gerar reservas"
				onclick="confirmAlert('Tem certeza que deseja continuar? Pode fazer uma baguncinha. Que tal um checkpoint antes?', 'gerar-reservas?action=generate-reservation')">
				<i class="bi bi-gear-wide-connected"></i>
				Gerar Reservas
			</a>
		</div>

		<hr />

		<div>
		<?php
			if ( isset( $_GET['action'] ) && $_GET['action'] === 'generate-checkpoint' ) {
				generate_checkpoint();
			} else if ( isset( $_GET['action'] ) && $_GET['action'] === 'use-last-checkpoint' ) {
				use_last_checkpoint();
			} else if ( isset( $_GET['action'] ) && $_GET['action'] === 'import-subjects-form' ) {
				?>
					<form action="/gerar-reservas?action=import-subjects" method="post" enctype="multipart/form-data">
						<div class="mb-3">
							<label for="formFile" class="form-label">Disciplinas</label>
							<input class="form-control" type="file" name="class_subjects_csv">
						</div>
						<div class="mb-3">
							<label for="group_owner" class="form-label">Grupo Dono</label>
							<input class="form-control" type="text" id="group_owner" name="group_owner" />
							<div id="group_owner" class="form-text">Se você for o dono das disciplinas, não preencha</div>
						</div>
						<div class="mb-3 form-check">
							<input class="form-check-input" type="checkbox" value="true" id="checkChecked" name="convert_schedule_format" checked>
							<label class="form-check-label" for="checkChecked">
								Converter formato do Horário
							</label>
						</div>
						<button type="submit" class="btn btn-primary">Importar</button>
					</form>
				<?php
			} else if ( isset( $_GET['action'] ) && $_GET['action'] === 'import-subjects' ) {
				import_class_subjects_routine();
			} else if ( isset( $_GET['action'] ) && $_GET['action'] === 'generate-reservation' ) {
				generate_reservations();
			} else if ( isset( $_GET['action'] ) && $_GET['action'] === 'delete-class-subjects' ) {
				delete_all_class_subjects();
				echo "Excluído todas as disciplinas!";
			} else if ( isset( $_GET['action'] ) && $_GET['action'] === 'delete-reservations' ) {
				delete_all_reservations();
				echo "Excluído todas as reservas!";
			}
		?>
		</div>
	</div>

	<script>
		function confirmAlert(msg, href) {
			if (window.confirm(msg)) {
				window.location.href = "/" + href;
			}
		}
	</script>

	<?php astra_primary_content_bottom(); ?>

</div><!-- #primary -->

<?php if ( astra_page_layout() == 'right-sidebar' ) : ?>

	<?php get_sidebar(); ?>

<?php endif ?>

<?php get_footer(); ?>