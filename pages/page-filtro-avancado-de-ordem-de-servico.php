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

get_header();

$is_searching = false;
if ( ! empty( $_POST['number'] ) ) {
	$form_data = array_map(
		fn( $field_data ) => is_array( $field_data ) ? array_map(
			fn( $item ) => intranet_fafar_utils_escape_and_clean( $item, 'lower' ),
			$field_data
		) : intranet_fafar_utils_escape_and_clean( $field_data, 'lower' ),
		$_POST
	);

	// Only localize if the script is registered
	if ( wp_script_is( 'intranet-fafar-filtro-avancado-de-ordem-de-servico', 'registered' ) ) {
		wp_localize_script( 'intranet-fafar-filtro-avancado-de-ordem-de-servico', 'FORM_DATA', $form_data );
	} else {
		print_r( 'Script intranet-fafar-filtro-avancado-de-ordem-de-servico not registered' );
	}

	$is_searching = true;
}

$users_apoio_logistico = intranet_fafar_api_get_users( array( 'role' => 'apoio_logistico_e_operacional' ) );

$users_ti = intranet_fafar_api_get_users( array( 'role' => 'tecnologia_da_informacao_e_suporte' ) );

$types_apoio_logistico = [ 
	"Instalações elétricas",
	"Instalações hidráulicas",
	"Máquinas e equipamentos especiais",
	"Perdiais e urbanas",
	"Segurança universitária",
];

$types_ti = [ 
	"Ajuda/Suporte",
	"Desenvolvimento",
	"Instalação",
	"Manutenção",
	"Outros",
];

if ( wp_script_is( 'intranet-fafar-filtro-avancado-de-ordem-de-servico', 'registered' ) ) {
	wp_localize_script( 'intranet-fafar-filtro-avancado-de-ordem-de-servico', 'USERS_APOIO_LOGISTICO', $users_apoio_logistico );
	wp_localize_script( 'intranet-fafar-filtro-avancado-de-ordem-de-servico', 'USERS_TI', $users_ti );
	wp_localize_script( 'intranet-fafar-filtro-avancado-de-ordem-de-servico', 'TYPES_APOIO_LOGISTICO', $types_apoio_logistico );
	wp_localize_script( 'intranet-fafar-filtro-avancado-de-ordem-de-servico', 'TYPES_TI', $types_ti );
} else {
	print_r( 'Script intranet-fafar-filtro-avancado-de-ordem-de-servico not registered' );
}
?>

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

	<!-- FORM -->
	<form id="search-form">
		<div class='d-flex gap-3'>
			<div class="mb-3 flex-grow-1">
				<label for="number" class="form-label">Número da OS:</label>
				<input type="number" id="number" name="number" class="form-control">
			</div>
			<div class="mb-3 flex-grow-1">
				<label for="status" class="form-label">Status:</label>
				<select class="form-select" id="status" name="status" aria-label="Select para status">
					<option value>Todos</option>
					<option value="Novo">Novo</option>
					<option value="Em andamento">Em andamento</option>
					<option value="Aguardando">Aguardando</option>
					<option value="Cancelada">Cancelada</option>
					<option value="Finalizada">Finalizada</option>
				</select>
			</div>
			<div class="mb-3 flex-grow-1">
				<label for="departament_assigned_to" class="form-label">Setor demandado:</label>
				<select class="form-select" id="departament_assigned_to" name="departament_assigned_to"
					aria-label="Select para Setor">
					<option value>Todos</option>
					<option value="apoio_logistico_e_operacional">Apoio Logístico Operacional</option>
					<option value="tecnologia_da_informacao_e_suporte">TI</option>
				</select>
			</div>
			<div class="mb-3 flex-grow-1">
				<label for="type" class="form-label">Tipo de OS:</label>
				<select class="form-select" id="type" name="type" aria-label="Select para Tipo de OS" disabled>
					<option value>Todos</option>
				</select>
			</div>
		</div>
		<div class='d-flex gap-3'>
			<div class="mb-3 flex-grow-1">
				<label for="created_at_from" class="form-label">Período de criação - De:</label>
				<input type="date" id="created_at_from" name="created_at_from" class="form-control">
			</div>
			<div class="mb-3 flex-grow-1">
				<label for="created_at_until" class="form-label">Até:</label>
				<input type="date" id="created_at_until" name="created_at_until" class="form-control">
			</div>
		</div>
		<div class="d-flex gap-2">
			<div class="mb-3 flex-grow-1">
				<label for="place" class="form-label">Local:</label>
				<?php
				$places = intranet_fafar_api_read(
					args: array(
						'filters' => array(
							array(
								'column' => 'object_name',
								'value' => 'place',
								'operator' => '=',
							),
						),
						'order_by' => array(
							'orderby_json' => 'number',
							'order' => 'ASC',
						),
					)
				);

				if ( empty( $places['data'] ) )
					$places = [];

				$places['data'] = array_filter( $places['data'], fn( $place ) => ! empty( $place['data']['number'] ) );
				$places_numbers = array_map( fn( $place ) => $place['data']['number'] . ' ' . ( $place['data']['desc'] ?? '' ), $places['data'] );
				$places_ids = array_map( fn( $place ) => $place['id'], $places['data'] );

				echo intranet_fafar_utils_render_dropdown_menu(
					array(
						'options' => $places_numbers,
						'options_values' => $places_ids,
						'name' => 'place',
						'id' => 'place',
						'placeholder' => 'Todos',
					)
				);
				?>
			</div>
			<div class="mb-3 flex-grow-1">
				<label for="owner" class="form-label">Solicitante:</label>
				<?php
				$users = intranet_fafar_api_get_users();

				$display_names = array_map( fn( $user ) => $user['display_name'], $users );
				$ids = array_map( fn( $user ) => $user['ID'], $users );

				echo intranet_fafar_utils_render_dropdown_menu(
					array(
						'options' => $display_names,
						'options_values' => $ids,
						'name' => 'owner',
						'id' => 'owner',
						'placeholder' => 'Todos',
					)
				);
				?>
			</div>
			<div class="mb-3 flex-grow-1">
				<label for="assigned_to" class="form-label">Prestador:</label>
				<select class="form-select" id="assigned_to" name="assigned_to" aria-label="Select para Prestador"
					disabled>
					<option value>Todos</option>
				</select>
			</div>
		</div>
		<div class="d-flex gap-2">
			<div class="mb-3 flex-grow-1">
				<label for="user_report" class="form-label">Relato do problema (contém):</label>
				<input type="text" id="user_report" name="user_report" class="form-control">
			</div>
		</div>
		<div class="d-flex gap-2">
			<button type="submit" class="btn btn-primary">
				<i class="bi bi-search"></i>
				Buscar
			</button>
		</div>

	</form>

	<hr />

	<!-- TABLES -->


	<div id="table-wrapper" class="d-none"></div>

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