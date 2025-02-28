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


/*
 * Importanto script JS customizado
 * wp_enqueue_script( 'intranet-fafar-salas-script', get_stylesheet_directory_uri() . '/assets/js/salas.js', array( 'jquery' ), false, false );
 */

$reservation_log = array();

function generate_reservations($class_subjects) {

    global $reservation_log;

    if (isset($class_subjects['error_msg'])) {
        return 'Nenhuma reserva encontrada!';
    }

    // Filtra disciplinas com mais de 80 vagas, disciplinas práticas e estágio
    $class_subjects = array_filter($class_subjects, function ($subject) {
        return (
            ( (int) $subject['data']['number_vacancies_offered'] ) > 0 && 
            ( (int) $subject['data']['number_vacancies_offered'] ) < 80 &&  
            isset( $subject['data']['desired_time'] ) &&
            is_string( $subject['data']['desired_time'] ) &&  
            ! str_contains( intranet_fafar_utils_escape_and_clean_to_compare( $subject['data']['name_of_subject'] ), 'estagio' ) &&  
            ! str_contains( intranet_fafar_utils_escape_and_clean_to_compare( $subject['data']['name_of_subject'] ), 'monografia' ) &&  
            ! str_contains( strtoupper( $subject['data']['group']), 'P' )
        );
    });

    if (empty($class_subjects)) {
        return 'Não há disciplinas cadastradas para reservas.';
    }

    echo '<br />Tentando reservar ' . count($class_subjects) . ' disciplinas.<br />';

    $vacancies = array_column(array_column($class_subjects, 'data'), 'number_vacancies_offered');
    $durations = array_merge(...array_map(fn($subject) => getDurations($subject['data']['desired_time']), $class_subjects));

    $vacancies_average = array_sum($vacancies) / count($vacancies);
    $duration_average = array_sum($durations) / count($durations);
    
    $vacancies_stdev = standardDeviation($vacancies);
    $durations_stdev = standardDeviation($durations);

    echo '<br />';
    echo 'Vagas: ' . round( $vacancies_average, 2 ) . ' (D.P.:' . round( $vacancies_stdev, 2 ) . ')';
    echo '<br />';
    echo 'Duração: ' . round( $duration_average, 2 ) . ' (D.P.:' . round( $durations_stdev, 2 ) . ')';

    // $groups = array_fill(1, 4, []);

    // foreach ($class_subjects as $subject) {
    //     $subject_duration = array_sum(getDurations($subject['data']['desired_time']));
    //     $vacancies = $subject['data']['number_vacancies_offered'];

    //     $group_index = (int)(($vacancies >= $vacancies_average) << 1 | ($subject_duration >= $duration_average)) + 1;
    //     $groups[$group_index][] = $subject;


    // }

    // foreach ($groups as $index => $group) {
    //     echo "Grupo $index: " . count($group) . '<br />';
    // }

    foreach ( $class_subjects as &$subject ) {
        $scheduales     = getDurations( $subject['data']['desired_time'] );
        $scheduales_qtd = count( $scheduales );

        /*
         * Os seguintes pesos são, por padrão, para disciplinas obrigatória.
         * (Na dúvida, trata com se fosse obrigatória)
         */
        $w_v = 1; // Peso para vagas
        $w_t = 65; // Peso para quantidade de horários
        $w_n = 3; // Peso para natureza da disciplina: 'obrigatória' ou 'optativa'
        if(
            isset( $subject['data']['nature_of_subject'][0] ) && 
            intranet_fafar_utils_escape_and_clean_to_compare( $subject['data']['nature_of_subject'][0] ) === 'optativa' 
        ) {
            $w_v = 0.5;
            $w_t = 25;
            $w_n = 1;
        }

        $subject['score'] = ( ( (int) $subject['data']['number_vacancies_offered'] * $w_v ) + ( $scheduales_qtd * $w_t ) ) * $w_n;
    }

    usort( $class_subjects, function( $a, $b ) {
        return $b['score'] <=> $a['score']; // Sort descending
    } );

    echo '<br />';

    $classrooms = array_filter(
        intranet_fafar_api_get_submissions_by_object_name( 'place', ['orderby_json' => 'capacity', 'order' => 'ASC'] ),
        fn( $place ) => $place['data']['object_sub_type'][0] === 'classroom'
    );

    $failed_reservations = [];
    $no_schedules_failed = [];
    $attempts            = 0;
    $failures            = 0;

    foreach ( $class_subjects as $subject ) {

        if( 
            ! isset( $subject['data']['use_on_auto_reservation'][0] ) ||  
            ! $subject['data']['use_on_auto_reservation'][0] ||  
            $subject['data']['use_on_auto_reservation'][0] !== 'Sim' ){
                $reservation_log[] = array(
                    'sub_id'    => $subject['id'],
                    'sub_code'  => $subject['data']['code'],
                    'scheduale' => '',
                    'status'    => 'fail',
                    'desc'      => 'Can not be used',
                );

                continue;
            }

        $possible_rooms = array_filter($classrooms, fn($room) => $room['data']['capacity'] >= $subject['data']['number_vacancies_offered']);
        $schedules = parse_schedule( $subject['data']['desired_time']);

        if(count($schedules) === 0) {
            $reservation_log[] = array(
                'sub_id'    => $subject['id'],
                'sub_code'  => $subject['data']['code'],
                'scheduale' => '',
                'status'    => 'fail',
                'desc'      => 'Sem scheduale: ' . print_r( $subject['data']['desired_time'], true ),
            );
            continue;
        } 

        foreach ($schedules as $schedule) {
            $attempts++;

            $reservation_obj = array(
                'class_subject' => [$subject['id']],
                'start_time'    => $schedule['start'],
                'end_time'      => $schedule['end'],
                'weekdays'      => $schedule['weekday'],
            );

            if (
                has_reservation_for_another_group( $reservation_obj )
            ) {
                $reservation_log[] = array(
                    'sub_id'    => $subject['id'],
                    'sub_code'  => $subject['data']['code'],
                    'scheduale' => print_r( $schedule, true ),
                    'status'    => 'fail',
                    'desc'      => 'Existe reserva com mesma disciplina e turma',
                );
                continue;
            } 

            $new_reservation = null;

            foreach ($possible_rooms as $room) {

                $date = '10/03/2025';
                if(
                    isset($subject['data']['desired_start_date'] ) && $subject['data']['desired_start_date']
                ) $date = $subject['data']['desired_start_date'];

                $end_date = '12/07/2025';
                if(
                    isset($subject['data']['desired_end_date'] ) && $subject['data']['desired_end_date']
                ) $end_date = $subject['data']['desired_end_date'];

                $reservation = [
                    'object_name' => 'reservation',
                    'permissions' => '777',
                    'group_owner' => $subject['group_owner'],
                    'data' => json_encode([
                        'class_subject' => [$subject['id']],
                        'place'         => [$room['id']],
                        'frequency'     => ['weekly'],
                        'weekdays'      => $schedule['weekday'],
                        'start_time'    => $schedule['start'],
                        'end_time'      => $schedule['end'],
                        'date'          => convert_date( $date ),
                        'end_date'      => convert_date( $end_date ),
                        'applicant'     => get_current_user_id()
                    ])
                ];

                // echo '<br />';
                // print_r($reservation);
                // echo '<br />';
                
                $new_reservation = intranet_fafar_api_create_or_update_reservation( $reservation );
                
                if ( ! isset( $new_reservation['error_msg'] ) ) {
                    $new_reservation = intranet_fafar_api_create( $new_reservation );
                    break;
                }
            }

            if ( isset( $new_reservation['error_msg'] ) ) {
                $failures++;

                $reservation_log[] = array(
                    'sub_id'    => $subject['id'],
                    'sub_code'  => $subject['data']['code'],
                    'scheduale' => print_r( $schedule, true ),
                    'status'    => 'fail',
                    'desc'      => $new_reservation['error_msg'],
                );


            } else {
                $reservation_log[] = array(
                    'sub_id'    => $subject['id'],
                    'sub_code'  => $subject['data']['code'],
                    'scheduale' => print_r( $schedule, true ),
                    'status'    => 'success',
                    'desc'      => '',
                );
            }
        }
    }

    echo "$attempts tentativas<br />$failures falhas<br /><br />Disciplinas com falhas:<br /><pre>";
    print_r($failed_reservations);
    echo "</pre><br />" . ($attempts - $failures) . " com sucesso<br />";
    
    return '';
}

/*
 * Verifica se uma mesma disciplina já foi 
 * reservada no mesmo horário e dia da semana, 
 * mas de turma diferente, apenas. Se sim, 
 * não há necessidade de outra reserva.
 */
function has_reservation_for_another_group( $new_reservation ) {
    $reservations = intranet_fafar_api_get_submissions_by_object_name( 'reservation' );

    if(
        count( $reservations ) === 0 || 
        isset( $reservations['error_msg'] )
    ) return false;

    $duplicate = array_filter( $reservations, function ( $reservation ) use ( $new_reservation ) {

        if(
            ! isset( $reservation['data']['class_subject'] ) || 
            ! $reservation['data']['class_subject']
        ) return false;

        $class_subject_a = intranet_fafar_api_get_submission_by_id( $reservation['data']['class_subject'][0] );
        $class_subject_b = intranet_fafar_api_get_submission_by_id( $new_reservation['class_subject'][0] );

        if(
            isset( $class_subject_a['error_msg'] ) || 
            isset( $class_subject_b['error_msg'] )
        ) return false;

        return (
            $class_subject_a['data']['code'] === $class_subject_b['data']['code'] && 
            $reservation['data']['start_time'] === $new_reservation['start_time'] && 
            $reservation['data']['end_time'] === $new_reservation['end_time'] && 
            $reservation['data']['weekdays'] === $new_reservation['weekdays']
        );
    } );

    return ( count( $duplicate ) > 0 );
}

function parse_schedule($input) {
    $result = [];
    preg_match_all('/(\d{1,2}:\d{2})\s+(\d{1,2}:\d{2})\s+\((\w{3})\)/', $input, $matches, PREG_SET_ORDER);
    
    // Mapeamento dos dias da semana para números (Seg = 1, Ter = 2, ..., Dom = 7)
    $days_map = [
        'SEG' => 1, 'TER' => 2, 'QUA' => 3,
        'QUI' => 4, 'SEX' => 5, 'SAB' => 6, 'DOM' => 7
    ];

    foreach ($matches as $match) {
        $start = $match[1];
        $end = $match[2];
        $weekday = $days_map[$match[3]] ?? null;

        if ($weekday) {
            $result[] = [
                'start' => $start,
                'end' => $end,
                'weekday' => [(int) $weekday]
            ];
        }
    }

    return $result;
}


function convert_date($date) {
    $dt = DateTime::createFromFormat('d/m/Y', $date);
    return $dt ? $dt->format('Y-m-d') : false;
}

function getDurations($input) {
    preg_match_all('/(\d{2}):(\d{2})\s+(\d{2}):(\d{2})/', $input, $matches, PREG_SET_ORDER);
    $durations = [];

    foreach ($matches as $match) {
        $startHour = (int)$match[1];
        $startMinute = (int)$match[2];
        $endHour = (int)$match[3];
        $endMinute = (int)$match[4];

        $startTime = $startHour * 60 + $startMinute;
        $endTime = $endHour * 60 + $endMinute;
        $durations[] = $endTime - $startTime;
    }

    return $durations;
}

function standardDeviation($numbers) {
    $n = count($numbers);
    if ($n === 0) return 0; // Avoid division by zero

    $mean = array_sum($numbers) / $n;
    $sumSquaredDifferences = 0;

    foreach ($numbers as $num) {
        $sumSquaredDifferences += pow($num - $mean, 2);
    }

    return sqrt($sumSquaredDifferences / $n);
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

<?php if ( isset( $_GET['generate'] ) ): ?>

    <h5>Gerando reservas....</h5>

    <br />
    
    <?= generate_reservations($class_subjects); ?>

<?php else: ?>

    <br />

    <a 
        href="/gerar-reservas?generate=true" 
        class="btn btn-primary text-decoration-none" 
        title="Gerar reservas">
            Gerar
    </a>

<?php endif; ?>

    <br />

    <h5><?= ( isset( $reservation_log[0] ) ? count( $reservation_log ) : '0' ) ?> reservas</h5>

    <table class="table">
        <thead>
            <tr>
                <th scope="col">Código Disciplina</th>
                <th scope="col">Horários</th>
                <th scope="col">Status</th>
                <th scope="col">Desc</th>
            </tr>
        </thead>
        <tbody>
            <?php
                if( isset( $reservation_log ) && is_array( $reservation_log ) && count( $reservation_log ) > 0 ){
                    foreach ( $reservation_log as $row ) {
                        echo '<tr>';
                            echo '<td><a href="/visualizar-objeto?id=' . $row['sub_id'] . '" target="blank">' . $row['sub_code'] . '</td>';
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
