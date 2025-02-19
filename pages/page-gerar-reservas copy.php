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
// wp_enqueue_script( 'intranet-fafar-salas-script', get_stylesheet_directory_uri() . '/assets/js/salas.js', array( 'jquery' ), false, false );

function generate_reservations($class_subjects) {

    if( isset( $class_subjects['error_msg'] ) ) {

        return 'Nenhuma reserva encontrada!';

    }

    $vacancies = array();
    $vacancies_average = 0;

    $durations = array();
    $duration_average = 0;

    // Filtra por disciplinas com mais de 80 vagas e disciplinas práticas
    $class_subjects = array_filter( $class_subjects, function ( $class_subject ) {
        return ( $class_subject['data']['number_vacancies_offered'] < 80 &&
                ! str_starts_with( strtoupper( $class_subject['data']['group'] ), 'P' ) );
    } );

    if( count( $class_subjects ) === 0 ) 
        return 'Não há disciplinas cadastrada para reservas.';

    echo '<br />Tentando reservar ' . count( $class_subjects ) . ' disciplinas.<br />';

    foreach( $class_subjects as $class_subject ) {

        foreach( getDurations( $class_subject['data']['desired_time'] ) as $duration ) {
            $durations[] = $duration;
        }

        $vacancies[] = $class_subject['data']['number_vacancies_offered'];

    }

    $vacancies_average = array_sum($vacancies) / count($vacancies);
    $duration_average = array_sum($durations) / count($durations);

    $vacancie_stdes = standardDeviation($vacancies);
    $duration_stdes = standardDeviation($durations);

    $group_1 = array();
    $group_2 = array();
    $group_3 = array();
    $group_4 = array();
    $group_5 = array();
    $group_6 = array();
    $group_7 = array();
    $group_8 = array();
    $group_9 = array();

    foreach ( $class_subjects as $class_subject ) {

        $class_subject_duration = 0;
        foreach( getDurations( $class_subject['data']['desired_time'] ) as $duration ) {
            $class_subject_duration += $duration;
        }
        
        if (
            $class_subject['data']['number_vacancies_offered'] >= ( $vacancies_average + $vacancie_stdes ) && 
            $class_subject_duration >= ( $duration_average + $duration_stdes )
        ) $group_1[] = $class_subject;
        else if(
            $class_subject['data']['number_vacancies_offered'] >= ( $vacancies_average + $vacancie_stdes ) && 
            $class_subject_duration >= ( $duration_average - $duration_stdes ) && 
            $class_subject_duration < ( $duration_average + $duration_stdes )
        ) $group_2[] = $class_subject;
        else if(
            $class_subject['data']['number_vacancies_offered'] >= ( $vacancies_average + $vacancie_stdes ) && 
            $class_subject_duration < ( $duration_average - $duration_stdes )
        ) $group_3[] = $class_subject;
        else if (
            $class_subject['data']['number_vacancies_offered'] >= ( $vacancies_average - $vacancie_stdes ) && 
            $class_subject['data']['number_vacancies_offered'] < ( $vacancies_average + $vacancie_stdes ) && 
            $class_subject_duration >= ( $duration_average + $duration_stdes )
        ) $group_4[] = $class_subject;
        else if(
            $class_subject['data']['number_vacancies_offered'] >= ( $vacancies_average - $vacancie_stdes ) && 
            $class_subject['data']['number_vacancies_offered'] < ( $vacancies_average + $vacancie_stdes ) && 
            $class_subject_duration >= ( $duration_average - $duration_stdes ) && 
            $class_subject_duration < ( $duration_average + $duration_stdes )
        ) $group_5[] = $class_subject;
        else if(
            $class_subject['data']['number_vacancies_offered'] >= ( $vacancies_average - $vacancie_stdes ) && 
            $class_subject['data']['number_vacancies_offered'] < ( $vacancies_average + $vacancie_stdes ) && 
            $class_subject_duration < ( $duration_average - $duration_stdes )
        ) $group_6[] = $class_subject;
        else if (
            $class_subject['data']['number_vacancies_offered'] < ( $vacancies_average - $vacancie_stdes ) && 
            $class_subject_duration >= ( $duration_average + $duration_stdes )
        ) $group_7[] = $class_subject;
        else if(
            $class_subject['data']['number_vacancies_offered'] < ( $vacancies_average - $vacancie_stdes ) && 
            $class_subject_duration >= ( $duration_average - $duration_stdes ) && 
            $class_subject_duration < ( $duration_average + $duration_stdes )
        ) $group_8[] = $class_subject;
        else if(
            $class_subject['data']['number_vacancies_offered'] < ( $vacancies_average - $vacancie_stdes ) && 
            $class_subject_duration < ( $duration_average - $duration_stdes )
        ) $group_9[] = $class_subject;
        
    }
    
    echo '<br />Vagas: ' . $vacancies_average . ' (D.V.:' . standardDeviation($vacancies) . ')<br />
    Duração: ' . $duration_average . ' (D.V.:' . standardDeviation($durations) . ')<br/ >
    Grupo 1: ' . count($group_1) . '<br />
    Grupo 2: ' . count($group_2) . '<br />
    Grupo 3: ' . count($group_3) . '<br />
    Grupo 4: ' . count($group_4) . '<br />
    Grupo 5: ' . count($group_5) . '<br />
    Grupo 6: ' . count($group_6) . '<br />
    Grupo 7: ' . count($group_7) . '<br />
    Grupo 8: ' . count($group_8) . '<br />
    Grupo 9: ' . count($group_9) . '<br />
    Total: ' . count($class_subjects) . '<br />';

    $places = intranet_fafar_api_get_submissions_by_object_name( 'place', array( 'orderby_json' => 'capacity', 'order' => 'ASC' ) );

    $classrooms = array_filter( $places, function ( $place ) { 
        return ( isset( $place['data']['object_sub_type'][0] ) && $place['data']['object_sub_type'][0] === 'classroom' ); 
    } );

    $count_reserve_fails = 0;
    $reservation_to_make_counter = 0;
    $class_subject_with_error = array();
    
    $all_groups = array_merge(
        $group_1,
        $group_2,
        $group_3,
        $group_4,
        $group_5,
        $group_6,
        $group_7,
        $group_8,
        $group_9,
    );

    // print_r( $classrooms );
    // echo '<br/>';
    // echo '<br/>';
    // print_r( $all_groups );

    // Reserva de grupos
    foreach ( $all_groups as $class_subject ) {

        $possible_classrooms = array_filter( $classrooms, 
            function ( $classroom ) use ( $class_subject ) {
                return ( 
                    $classroom['data']['capacity'] >= $class_subject['data']['number_vacancies_offered'] 
                );
            } 
        );

        $schedules = parse_schedule( $class_subject['data']['desired_time'] );

        // print_r( $class_subject );
        // echo "<br/>";
        // print_r( $schedules );
        // echo "<br/>";
        // echo "<br/>";

        foreach( $schedules as $schedule ) {

            $reservation_to_make_counter++;

            $made_reservation = false;

            foreach ( $possible_classrooms as $classroom ) {

                // Tenho uma sala($classroom) para a $class_subject da vez
                $reservation['object_name'] = 'reservation';

                $reservation['data'] = json_encode( array(
                    'discipline' => array( $class_subject['id'] ),
                    'place' => array( $classroom['id'] ),
                    'frequency' => array( 'weekly' ),
                    'weekdays' => $schedule['weekday'],
                    'start_time' => $schedule['start'],
                    'end_time' => $schedule['end'],
                    'date' => ( isset($class_subject['desired_start_date']) && $class_subject['desired_start_date'] ? convert_date( $class_subject['desired_start_date'] ) : '2025-03-03' ),
                    'end_date' => ( isset($class_subject['desired_end_date']) && $class_subject['desired_end_date'] ? convert_date( $class_subject['desired_end_date'] ) : '2025-07-03' ),
                ) );

                $new_reservation = intranet_fafar_api_create_or_update_reservation( $reservation );

                if( isset( $new_reservation['error_msg'] ) ) {
                    // echo $new_reservation['error_msg'];
                    continue;
                } 
                    
                intranet_fafar_api_create( $new_reservation );

                $made_reservation = true;
                
                break;

            }

            if( ! $made_reservation ) {
                $count_reserve_fails++;
                $class_subject_with_error[] = array( 'class_subject' => $class_subject['data']['code'], 'schedule' => $schedule, 'nature_of_subject' => $class_subject['data']['nature_of_subject'] );
            }

        }
    }

    echo '<br />';
    echo '<br />';
    echo $reservation_to_make_counter . ' tentativas';
    echo '<br />';
    echo $count_reserve_fails . ' falhas';
    echo '<br />';
    echo '<br />Disciplinas com falhas:';
    echo '<pre>';
    print_r($class_subject_with_error);
    echo '</pre>';
    echo '<br />';
    echo '<br />';
    echo ( $reservation_to_make_counter - $count_reserve_fails ) . ' com sucesso';
    echo '<br />';
    return '';

}

function parse_schedule( $input) {
    $result = [];
    preg_match_all('/(\d{2}:\d{2})\s+(\d{2}:\d{2})\s+\((\w{3})\)/', $input, $matches, PREG_SET_ORDER);
    
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
                'weekday' => [ (int) $weekday ]
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
