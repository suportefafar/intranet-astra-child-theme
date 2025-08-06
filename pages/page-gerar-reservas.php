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

function generate_reservations( $class_subjects = [] ) {

	$a = get_class_subjects();
	$class_subjects = $a['data'];

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

	$class_subjects = intranet_fafar_api_get_submissions_by_object_name(
		'class_subject', 
		[], 
		[ 'check_permissions' => false ],
		false
	);

	$reservations = intranet_fafar_api_get_submissions_by_object_name(
		'reservation', 
		[], 
		[ 'check_permissions' => false ],
		false
	);

	if ( empty( $class_subjects ) || empty( $reservations ) ) {
		echo 'Sem reservas e/ou disciplinas para salvar!';
		return false;
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
            echo "<br />Disciplinas: " . count( $class_subjects['data'] );
            echo "<br />Reservas: " . count( $reservations['data'] );
            echo "<br />Salvo novo checkpoint em: " . esc_html($file_path);
        } else {
            echo "Failed to write to the file.";
        }
    } else {
        echo "WP_Filesystem could not be initialized.";
    }

}

function delete_all_class_subjects() {

	$class_subjects = intranet_fafar_api_get_submissions_by_object_name(
		'class_subject', 
		[], 
		[ 'check_permissions' => false ],
		false
	);

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

	$reservations = intranet_fafar_api_get_submissions_by_object_name(
		'reservation', 
		[], 
		[ 'check_permissions' => false ],
		false
	);

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
    
			$a = delete_all_class_subjects();
			$b = delete_all_reservations();
			
			if ( ! $a || ! $b ) {
				echo 'Sem reservas e/ou disciplinas para restaurar!';
				return false;
			}

			echo "Feito!";

			$checkpoint = json_decode( $file_content, true );

			if ( empty( $checkpoint['class_subjects'] ) || empty( $checkpoint['reservations'] ) ) {
				echo 'Sem reservas e/ou disciplinas para restaurar!';
				return false;
			}

			create_submissions( $checkpoint['class_subjects'] );
			create_submissions( $checkpoint['reservations'] );
            
        } else {
            echo "Failed to read the contents of the file.";
        }
    } else {
        echo "The file " . esc_html($file_path) . " does not exist or WP_Filesystem could not be initialized.";
    }
}

function import_subjects() {

	// Check if the form was submitted.
    if ( ! empty( $_FILES['subjects_file'] ) ) {
        
        // Define an array of overrides for wp_handle_upload().
        $upload_overrides = array( 'test_form' => false );
        
        // Use wp_handle_upload() to securely handle the upload.
        $uploaded_file = wp_handle_upload( $_FILES['subjects_file'], $upload_overrides );

        // Check if the file was uploaded successfully.
        if ( isset( $uploaded_file['file'] ) ) {
            $file_path = $uploaded_file['file'];
            $csv_data_array = array();

            // Open the uploaded CSV file for reading.
            if ( ( $handle = fopen( $file_path, "r" ) ) !== false ) {
                while ( ( $data = fgetcsv( $handle, 1000, ";" ) ) !== false ) {
                    // Push each row into our array.
                    $csv_data_array[] = $data;
                }
                fclose( $handle );
            }

			// It's good practice to unlink (delete) the temporary file.
            unlink( $file_path );
            
            // At this point, $csv_data_array contains the data from the CSV file.
            // You can now use this array to process the data as needed.
            
            // Example: print the array for verification.
            // echo '<pre>';
            // print_r( $csv_data_array );
            // echo '</pre>';

			$class_subjects_added   = [];
			$class_subjects_updated = [];
			foreach ( $csv_data_array as $class_subject ) {
				if ( $class_subject[0] === 'Semestre' ) continue; // Pula o header

				$submission = [
					'object_name' => 'class_subject',
					'permissions' => '777',
					'data' => [
						'code' => $class_subject[2],
						'name_of_subject' => $class_subject[2],
						'group' => $class_subject[7],
						'course_load' => $class_subject[4],
						'credits_of_subject' => intval( $class_subject[4] ) / 15,
						'nature_of_subject' => 'Obrigatória',
						'course' => $class_subject[2],
						'level' => '',
						'departament' => $class_subject[6],
						'type' => '',
						'adjustment' => '',
						'number_vacancies_offered' => $class_subject[9],
						'version_of_curriculum_matrix' => '',
						'professors' => $class_subject[14],
						'desired_time' => $class_subject[12],
						'use_on_auto_reservation' => 'true',

					]
				];

				intranet_fafar_api_create( $submission, false );

				$class_subjects_added[] = $submission;

			}

        } else {
            // Handle upload error.
            echo 'File upload failed: ' . $uploaded_file['error'];
        }
    }

	render_imported_subjects_table( $class_subjects_added, $class_subjects_updated );

	return true;

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
							<input class="form-control" type="file" name="subjects_file">
						</div>
						<button type="submit" class="btn btn-primary">Importar</button>
					</form>
				<?php
			} else if ( isset( $_GET['action'] ) && $_GET['action'] === 'import-subjects' ) {
				import_subjects();
			} else if ( isset( $_GET['action'] ) && $_GET['action'] === 'generate-reservation' ) {
				generate_reservations();
			} else if ( isset( $_GET['action'] ) && $_GET['action'] === 'delete-class-subjects' ) {
				delete_all_class_subjects();
				echo "Excluído todas as disciplinas!";
			} else if ( isset( $_GET['action'] ) && $_GET['action'] === 'delete-reservation' ) {
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