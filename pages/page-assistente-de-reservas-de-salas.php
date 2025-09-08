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


get_header(); ?>

<?php if ( astra_page_layout() == 'left-sidebar' ) : ?>

	<?php get_sidebar(); ?>

<?php endif; ?>

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

	<?php if ( isset( $_GET['step'] ) && $_GET['step'] === '3' ) : ?>
		<h5>3/3 - Confirmar dados</h5>
		<br />
		<table class="table border border-start-0 border-end-0">
			<tbody>
				<tr>
					<td>Descrição</td>
      				<th scope="row"><?= esc_html( $_POST['desc'] ) ?></th>
				</tr>
				<tr>
					<td>Disciplina</th>
					<?php
						$class_subject = intranet_fafar_api_get_submission_by_id( sanitize_text_field( $_POST['class_subject'] ) );

						$class_subject_display = '';
						if ( ! empty( $class_subject ) ) {
							$class_subject_display = $class_subject['data']['code'] . ' (' . $class_subject['data']['group'] . ')';
						}
					?>
      				<th scope="row"><?= $class_subject_display ?></td>
				</tr>
				<tr>
					<?php
						$date_display = fafar_intranet_format_date_local( esc_html( $_POST['date'] ) );
					?>
					<td><?= ( sanitize_text_field( $_POST['frequency'] ) === 'once' ? 'Data' : 'Data Inicial' ) ?></td>
      				<th scope="row"><?= $date_display ?></th>
				</tr>
				<?php
					$end_date_display = null;
					if ( $_POST['end_date'] ) {
						$end_date_display = fafar_intranet_format_date_local( esc_html( $_POST['end_date'] ) );
					}
				?>
				<tr>
					<td>Data Final</td>
      				<th scope="row"><?= ( $end_date_display ?? '' ) ?></th>
				</tr>
				<tr>
					<td>Hora Início</td>
      				<th scope="row"><?= esc_html( $_POST['start_time'] ) ?></th>
				</tr>
				<tr>
					<td>Hora Fim</td>
      				<th scope="row"><?= esc_html( $_POST['end_time'] ) ?></th>
				</tr>
				<tr>
					<td>Frequência</td>
					<?php
						$frequency_sanitized = sanitize_text_field( $_POST['frequency'] );
						$frequency_display   = fafar_intranet_get_frequency_display_text( $frequency_sanitized );
					?>
					<th scope="row"><?= $frequency_display ?></th>
				</tr>
				<tr>
					<td>Dias da Semana</td>
					<?php
						$weekdays_sanitized = sanitize_text_field( $_POST['weekdays'] );
						$weekdays_arr       = explode( ',', $weekdays_sanitized );
						$weekdays_display   = fafar_intranet_get_weekdays( $weekdays_arr );
					?>
      				<th scope="row"><?= $weekdays_display ?></th>
				</tr>
				<tr>
					<td>Local</td>
					<?php
						$place = intranet_fafar_api_get_submission_by_id( sanitize_text_field( $_POST['place'] ) );

						$place_display = '';
						if ( ! empty( $place ) ) {
							$place_display = $place['data']['number'];
						}
					?>
      				<th scope="row"><?= $place_display ?></th>
				</tr>
				<tr>
					<td>Solicitante</td>
					<?php
						$applicant_sanitized = sanitize_text_field( $_POST['applicant'] );
						$applicant           = get_userdata( intval( $applicant_sanitized ) );

						$applicant_display = '';
						if ( ! empty( $applicant ) ) {
							$applicant_display = $applicant->data->display_name;
						}
					?>
      				<th scope="row"><?= $applicant_display ?></th>
				</tr>
			</tbody>
		</table>
		<?= do_shortcode( '[contact-form-7 id="c567fc2" title="Adicionar Reserva Pelo Assistente"]' ) ?>
	<?php elseif ( isset( $_GET['step'] ) && $_GET['step'] === '2' ) : ?>

		<h5>2/3 - Informações adicionais</h5>
		
		<form action="/assistente-de-reservas-de-salas?step=3" method="post" class="mb-5">

			<input type="hidden" name="date" value="<?= esc_html( $_POST['date'] ) ?>" />
			<input type="hidden" name="start_time" value="<?= esc_html( $_POST['start_time'] ) ?>" />
			<input type="hidden" name="end_time" value="<?= esc_html( $_POST['end_time'] ) ?>" />
			<input type="hidden" name="place" value="<?= esc_html( $_POST['place'] ) ?>" />
			<input type="hidden" name="frequency" value="<?= esc_html( $_POST['frequency'] ) ?>" />
			<?php 
				$weekdays_as_str = '';
				if ( isset( $_POST['weekdays'] ) ) {
					$sanitized_weekdays_array = array_map( 'sanitize_text_field', $_POST['weekdays'] );
					$weekdays_as_str = implode( ',', $sanitized_weekdays_array ); 
				}
			?>
			<input type="hidden" name="weekdays" value="<?= $weekdays_as_str ?>" />
			<input type="hidden" name="end_date" value="<?= esc_html( $_POST['end_date'] ) ?>" />
			
			<div class="input-group mb-3">
				<label for="desc">Descrição</label>
				<input type="text" name="desc" id="desc" />
			</div>
			
			<?php 
				if ( isset( $_POST['class_subject'] ) ) : 
			?>
				<input type="hidden" name="class_subject" value="<?= esc_html( $_POST['class_subject'] ) ?>" />
			<?php
				else:
			?>
				<!-- Disciplina
				[select class_subject include_blank far_crud_shortcode:intranet_fafar_get_subjects_as_select_options] -->
				<div class="form-group mb-3">
					<label for="class-subject">Disciplina</label>
					<select class="form-select" aria-label="Class subject select" id="class-subject" name="class_subject">
						<?php
							$class_subjects = intranet_fafar_get_subjects_as_select_options( false );

							if ( ! empty( $class_subjects ) ) :
								foreach( $class_subjects as $key => $value ) :
						?>
									<option value="<?= $key ?>"><?= $value ?></option>
						<?php
								endforeach;
							endif;
						?>
					</select>
				</div>
			<?php
				endif;
			?>

			<!-- Solicitante
			[select applicant include_blank far_crud_shortcode:intranet_fafar_get_users_as_select_options] -->
			<div class="form-group mb-3">
				<label for="applicant">Solicitante</label>
				<select class="form-select" aria-label="Class applicant" id="applicant" name="applicant">
					<?php
						$applicants = intranet_fafar_get_users_as_select_options( false );
						if ( ! empty( $applicants ) ) :
							foreach( $applicants as $key => $value ) :
					?>
								<option value="<?= $key ?>"><?= $value ?></option>
					<?php
							endforeach;
						endif;
					?>
				</select>
			</div>
			
			<button type="submit" class="btn btn-secondary">Continuar</button>
		</form>

	<?php else : ?>
		<a href="/sugestao-de-reservas-de-salas" class="btn btn-secondary mb-3">
			<i class="bi bi-bookmark-plus"></i>
			Sugestões
		</a>
		
		<h5>1/3 - Informações básicas</h5>
		
		<form action="/assistente-de-reservas-de-salas?step=2" method="post" class="mb-5" id="form-step-1">
			<input type="hidden" name="place" value="" />
			<input type="hidden" name="class_subject" value="" />
			<div class="form-group mb-3">
				<label for="event_date">* Data </label>
				<input type="date" class="form-control" id="date" name="date" min="2024-09-10" 
					aria-required="true" required />
			</div>
			<div class="form-group mb-3">
				<label for="start_time">* Início </label>
				<input type="time" class="form-control" id="start_time" name="start_time" aria-required="true" required />
			</div>
			<div class="form-group mb-3">
				<label for="end_time">* Fim </label>
				<input type="time" class="form-control" id="end_time" name="end_time" aria-required="true" required />
			</div>
			<div class="form-group mb-3">
				<label>Frequência</label>
				<div class="d-flex gap-3">
					<div class="form-check">
						<input class="form-check-input" type="radio" name="frequency" value="once" id="radioFrequency1" checked>
						<label class="form-check-label" for="radioFrequency1">
							Uma vez
						</label>
					</div>
					<div class="form-check">
						<input class="form-check-input" type="radio" name="frequency" value="weekly" id="radioFrequency2">
						<label class="form-check-label" for="radioFrequency2">
							Semanal
						</label>
					</div>
				</div>
			</div>
			<div class="form-group mb-3" id="container-weekdays">
				<label for="container-weekdays">Dia da semana</label>
				<div class="d-flex gap-3">
					<div class="form-check">
						<input class="form-check-input" type="checkbox" value="1" name="weekdays[]"  id="checkWeekdays1">
						<label class="form-check-label" for="checkWeekdays1">
							Segunda
						</label>
					</div>
					<div class="form-check">
						<input class="form-check-input" type="checkbox" value="2" name="weekdays[]" id="checkWeekdays2">
						<label class="form-check-label" for="checkWeekdays2">
							Terça
						</label>
					</div>
					<div class="form-check">
						<input class="form-check-input" type="checkbox" value="3" name="weekdays[]" id="checkWeekdays3">
						<label class="form-check-label" for="checkWeekdays3">
							Quarta
						</label>
					</div>
					<div class="form-check">
						<input class="form-check-input" type="checkbox" value="4" name="weekdays[]" id="checkWeekdays4">
						<label class="form-check-label" for="checkWeekdays4">
							Quinta
						</label>
					</div>
					<div class="form-check">
						<input class="form-check-input" type="checkbox" value="5" name="weekdays[]" id="checkWeekdays5">
						<label class="form-check-label" for="checkWeekdays5">
							Sexta
						</label>
					</div>
					<div class="form-check">
						<input class="form-check-input" type="checkbox" value="6" name="weekdays[]" id="checkWeekdays6">
						<label class="form-check-label" for="checkWeekdays6">
							Sábado
						</label>
					</div>
				</div>
			</div>
			<div class="mb-3" id="container-end-date">
				<label for="end_date" class="form-label">Data de término</label>
				<input type="date" class="form-control" name="end_date" id="end_date" /> 
			</div>
			<div class="form-group mb-3">
				<label for="capacity">* Capacidade </label>
				<input type="number" class="form-control" id="capacity" name="capacity" min="1" max="200" placeholder="20"
					aria-required="true" required />
			</div>
		</form>

		<button class="btn btn-primary" id="btn-search-places">
			<i class="bi bi-binoculars"></i>
			Buscar Salas
		</button>

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