<?php
// Prevent direct access to the file.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'show_user_profile', 'intranet_fafar_extra_user_profile_fields' );
add_action( 'edit_user_profile', 'intranet_fafar_extra_user_profile_fields' );

add_action( 'personal_options_update', 'intranet_fafar_save_extra_user_profile_fields' );
add_action( 'edit_user_profile_update', 'intranet_fafar_save_extra_user_profile_fields' );

add_action( 'admin_enqueue_scripts', 'intranet_fafar_load_admin_scripts' );


function intranet_fafar_extra_user_profile_fields( $user ) { ?>

	<!-- Extras Pessoais -->

	<h2><?php _e( "Extras Pessoais", "intranet-astra-child-theme" ); ?></h2>

	<table class="form-table">
		<tr>
			<th><label for="personal_phone"><?php _e( "Telefone", "intranet-astra-child-theme" ); ?></label></th>
			<td>
				<input type="text" name="personal_phone" id="personal_phone"
					value="<?php echo esc_attr( get_the_author_meta( 'personal_phone', $user->ID ) ); ?>"
					class="phone" /><br />
			</td>
		</tr>
		<tr>
			<th><label for="personal_birthday"><?php _e( "Data de nascimento", "intranet-astra-child-theme" ); ?></label>
			</th>
			<td>
				<input type="date" name="personal_birthday" id="personal_birthday"
					value="<?php echo esc_attr( get_the_author_meta( 'personal_birthday', $user->ID ) ); ?>"
					class="regular-text" /><br />
			</td>
		</tr>
		<tr>
			<th><label for="personal_cpf"><?php _e( "CPF", "intranet-astra-child-theme" ); ?></label></th>
			<td>
				<input type="text" name="personal_cpf" id="personal_cpf"
					value="<?php echo esc_attr( get_the_author_meta( 'personal_cpf', $user->ID ) ); ?>" class="cpf" /><br />
			</td>
		</tr>
		<tr>
			<th><label
					for="personal_ufmg_registration"><?php _e( "Matrícula UFMG", "intranet-astra-child-theme" ); ?></label>
			</th>
			<td>
				<input type="text" name="personal_ufmg_registration" id="personal_ufmg_registration"
					value="<?php echo esc_attr( get_the_author_meta( 'personal_ufmg_registration', $user->ID ) ); ?>"
					class="regular-text" /><br />
			</td>
		</tr>
		<tr>
			<th><label for="personal_siape"><?php _e( "SIAPE", "intranet-astra-child-theme" ); ?></label></th>
			<td>
				<input type="text" name="personal_siape" id="personal_siape"
					value="<?php echo esc_attr( get_the_author_meta( 'personal_siape', $user->ID ) ); ?>"
					class="regular-text siape" /><br />
			</td>
		</tr>
	</table>

	<!-- Endereço -->

	<h2><?php _e( "Endereço", "intranet-astra-child-theme" ); ?></h2>

	<table class="form-table">
		<tr>
			<th><label for="address_cep_code"><?php _e( "CEP", "intranet-astra-child-theme" ); ?></label></th>
			<td>
				<input type="text" name="address_cep_code" id="address_cep_code"
					value="<?php echo esc_attr( get_the_author_meta( 'address_cep_code', $user->ID ) ); ?>"
					class="cep" /><br />
				<span class="description">Insira o CEP para preenchimento automático</span>
				<p id="cep_code_req_status"></p>
			</td>
		</tr>
		<tr>
			<th><label for="address_uf"><?php _e( "UF", "intranet-astra-child-theme" ); ?></label></th>
			<td>
				<input type="text" name="address_uf" id="address_uf"
					value="<?php echo esc_attr( get_the_author_meta( 'address_uf', $user->ID ) ); ?>"
					class="regular-text" /><br />
			</td>
		</tr>
		<tr>
			<th><label for="address_city"><?php _e( "Município", "intranet-astra-child-theme" ); ?></label></th>
			<td>
				<input type="text" name="address_city" id="address_city"
					value="<?php echo esc_attr( get_the_author_meta( 'address_city', $user->ID ) ); ?>"
					class="regular-text" /><br />
			</td>
		</tr>
		<tr>
			<th><label for="address_neighborhood"><?php _e( "Bairro", "intranet-astra-child-theme" ); ?></label></th>
			<td>
				<input type="text" name="address_neighborhood" id="address_neighborhood"
					value="<?php echo esc_attr( get_the_author_meta( 'address_neighborhood', $user->ID ) ); ?>"
					class="regular-text" /><br />
			</td>
		</tr>
		<tr>
			<th><label for="address_public_place"><?php _e( "Logradouro", "intranet-astra-child-theme" ); ?></label></th>
			<td>
				<input type="text" name="address_public_place" id="address_public_place"
					value="<?php echo esc_attr( get_the_author_meta( 'address_public_place', $user->ID ) ); ?>"
					class="regular-text" /><br />
			</td>
		</tr>
		<tr>
			<th><label for="address_number"><?php _e( "Número", "intranet-astra-child-theme" ); ?></label></th>
			<td>
				<input type="text" name="address_number" id="address_number"
					value="<?php echo esc_attr( get_the_author_meta( 'address_number', $user->ID ) ); ?>"
					class="regular-text" /><br />
			</td>
		</tr>
		<tr>
			<th><label for="address_complement"><?php _e( "Complemento", "intranet-astra-child-theme" ); ?></label></th>
			<td>
				<input type="text" name="address_complement" id="address_complement"
					value="<?php echo esc_attr( get_the_author_meta( 'address_complement', $user->ID ) ); ?>"
					class="regular-text" /><br />
			</td>
		</tr>
	</table>

	<!-- Vínculo do Servidor -->

	<h2><?php _e( "Vínculo do Servidor", "intranet-astra-child-theme" ); ?></h2>

	<table class="form-table">
		<tr>
			<th><label for="public_servant_bond_type"><?php _e( "Tipo", "intranet-astra-child-theme" ); ?></label></th>
			<td>
				<?php
				$bond_types = get_option( 'bond_types', [] );

				echo intranet_fafar_utils_render_dropdown_menu(
					array(
						'options' => $bond_types,
						'options_values' => $bond_types,
						'name' => 'public_servant_bond_type',
						'id' => 'public_servant_bond_type',
						'selected' => esc_attr( get_the_author_meta( 'public_servant_bond_type', $user->ID ) ),
					)
				);
				?>
				<br />
			</td>
		</tr>

		<tr>
			<th><label for="public_servant_bond_category"><?php _e( "Categoria", "intranet-astra-child-theme" ); ?></label>
			</th>
			<td>
				<?php
				$bond_categories = get_option( 'bond_categories', [] );

				echo intranet_fafar_utils_render_dropdown_menu(
					array(
						'options' => $bond_categories,
						'options_values' => $bond_categories,
						'name' => 'public_servant_bond_category',
						'id' => 'public_servant_bond_category',
						'selected' => esc_attr( get_the_author_meta( 'public_servant_bond_category', $user->ID ) ),
					)
				);
				?>
				<br />
			</td>
		</tr>

		<tr id="professor_bond_positions_row">
			<th><label for="public_servant_bond_position"><?php _e( "Cargo", "intranet-astra-child-theme" ); ?></label></th>
			<td>
				<?php
				$professor_bond_positions = get_option( 'professor_bond_positions', [] );

				echo intranet_fafar_utils_render_dropdown_menu(
					array(
						'options' => $professor_bond_positions,
						'options_values' => $professor_bond_positions,
						'name' => 'public_servant_bond_position',
						'id' => 'public_servant_bond_position',
						'selected' => esc_attr( get_the_author_meta( 'public_servant_bond_position', $user->ID ) ),
					)
				);
				?>
				<br />
			</td>
		</tr>

		<tr id="tae_bond_positions_row">
			<th><label for="public_servant_bond_position"><?php _e( "Cargo", "intranet-astra-child-theme" ); ?></label></th>
			<td>
				<?php
				$tae_bond_positions = get_option( 'tae_bond_positions', [] );

				echo intranet_fafar_utils_render_dropdown_menu(
					array(
						'options' => $tae_bond_positions,
						'options_values' => $tae_bond_positions,
						'name' => 'public_servant_bond_position',
						'id' => 'public_servant_bond_position',
						'selected' => esc_attr( get_the_author_meta( 'public_servant_bond_position', $user->ID ) ),
					)
				);
				?>
				<br />
			</td>
		</tr>

		<tr id="professor_bond_classes_row">
			<th><label for="public_servant_bond_class"><?php _e( "Classe", "intranet-astra-child-theme" ); ?></label></th>
			<td>
				<?php
				$professor_bond_classes = get_option( 'professor_bond_classes', [] );

				echo intranet_fafar_utils_render_dropdown_menu(
					array(
						'options' => $professor_bond_classes,
						'options_values' => $professor_bond_classes,
						'name' => 'public_servant_bond_class',
						'id' => 'public_servant_bond_class',
						'selected' => esc_attr( get_the_author_meta( 'public_servant_bond_class', $user->ID ) ),
					)
				);
				?>
				<br />
			</td>
		</tr>

		<tr id="professor_bond_class_levels_row">
			<th><label
					for="professor_bond_class_levels"><?php _e( "Nível de classe", "intranet-astra-child-theme" ); ?></label>
			</th>
			<td>
				<?php
				$professor_bond_class_levels = get_option( 'professor_bond_class_levels', [] );

				echo intranet_fafar_utils_render_dropdown_menu(
					array(
						'options' => $professor_bond_class_levels,
						'options_values' => $professor_bond_class_levels,
						'name' => 'public_servant_bond_level',
						'id' => 'public_servant_bond_level',
						'selected' => esc_attr( get_the_author_meta( 'public_servant_bond_level', $user->ID ) ),
						'placeholder' => 'Selecione um nível',
					)
				);
				?>
				<br />
			</td>
		</tr>

		<tr id="tae_bond_classes_row">
			<th><label for="public_servant_bond_class"><?php _e( "Classe", "intranet-astra-child-theme" ); ?></label></th>
			<td>
				<?php
				$tae_bond_classes = get_option( 'tae_bond_classes', [] );

				echo intranet_fafar_utils_render_dropdown_menu(
					array(
						'options' => $tae_bond_classes,
						'options_values' => $tae_bond_classes,
						'name' => 'public_servant_bond_class',
						'id' => 'public_servant_bond_class',
						'selected' => esc_attr( get_the_author_meta( 'public_servant_bond_class', $user->ID ) ),
					)
				);
				?>
				<br />
			</td>
		</tr>

		<tr>
			<th><label for="public_servant_work_sector"><?php _e( "Setor", "intranet-astra-child-theme" ); ?></label></th>
			<td>
				<?php
				$roles = intranet_fafar_utils_get_all_roles();

				$current_user_role = intranet_fafar_utils_get_current_user_role();
				if (
					empty( $current_user_role ) ||
					! in_array( $current_user_role['slug'], [ 'administrator', 'tecnologia_da_informacao_e_suporte' ] )
				) {
					$roles = array_filter( $roles, fn( $role_slug ) => ( $role_slug !== 'administrator' ), ARRAY_FILTER_USE_KEY );
				}

				$roles_display_names = array_map( fn( $role ) => $role['name'], $roles );
				$roles_display_names = array_values( $roles_display_names );

				$roles_slugs = array_map( fn( $slug ) => $slug, array_keys( $roles ) );

				$user_roles = get_userdata( $user->ID )->roles;

				echo intranet_fafar_utils_render_dropdown_menu(
					array(
						'options' => $roles_display_names,
						'options_values' => $roles_slugs,
						'name' => 'public_servant_role',
						'id' => 'public_servant_role',
						'selected' => esc_attr( ! empty( $user_roles[0] ) ? $user_roles[0] : '' ),
						'placeholder' => 'Selecione um setor',
					)
				);
				?>
				<br />
			</td>
		</tr>

		<tr>
			<th><label for="bond_status"><?php _e( "Status", "intranet-astra-child-theme" ); ?></label></th>
			<td>
				<?php
				$bond_status = get_option( 'bond_status', [] );

				echo intranet_fafar_utils_render_dropdown_menu(
					array(
						'options' => $bond_status,
						'options_values' => $bond_status,
						'name' => 'bond_status',
						'id' => 'bond_status',
						'selected' => esc_attr( get_the_author_meta( 'bond_status', $user->ID ) ),
					)
				);
				?>
				<br />
			</td>
		</tr>

	</table>

	<!-- Local de Trabalho -->

	<h2><?php _e( "Local de Trabalho", "intranet-astra-child-theme" ); ?></h2>

	<table class="form-table">
		<tr>
			<th><label
					for="workplace_extension"><?php _e( "Telefone Institucional", "intranet-astra-child-theme" ); ?></label>
			</th>
			<td>
				<input type="text" name="workplace_extension" id="workplace_extension"
					value="<?php echo esc_attr( get_the_author_meta( 'workplace_extension', $user->ID ) ); ?>"
					class="regular-text extension" /><br />
			</td>
		</tr>

		<tr>
			<th><label for="workplace_place"><?php _e( "Sala", "intranet-astra-child-theme" ); ?></label></th>
			<td>
				<?php
				$rooms = intranet_fafar_api_get_not_reservable_places();

				$rooms_numbers = array_map( function ($room) {
					return $room['data']['number'];
				}, $rooms );
				$rooms_ids = array_map( function ($room) {
					return $room['id'];
				}, $rooms );

				echo intranet_fafar_utils_render_dropdown_menu(
					array(
						'options' => $rooms_numbers,
						'options_values' => $rooms_ids,
						'name' => 'workplace_place',
						'id' => 'workplace_place',
						'selected' => esc_attr( get_the_author_meta( 'workplace_place', $user->ID ) ),
						'placeholder' => 'Selecione uma sala',
					)
				);
				?>
				<br />
			</td>
		</tr>

	</table>

<?php }

function intranet_fafar_save_extra_user_profile_fields( $user_id ) {
	if ( empty( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'update-user_' . $user_id ) ) {
		return;
	}

	if ( ! current_user_can( 'edit_user', $user_id ) ) {
		return false;
	}

	update_user_meta( $user_id, 'personal_phone', sanitize_text_field( $_POST['personal_phone'] ) );
	update_user_meta( $user_id, 'personal_birthday', sanitize_text_field( $_POST['personal_birthday'] ) );
	update_user_meta( $user_id, 'personal_cpf', sanitize_text_field( $_POST['personal_cpf'] ) );
	update_user_meta( $user_id, 'personal_ufmg_registration', sanitize_text_field( $_POST['personal_ufmg_registration'] ) );
	update_user_meta( $user_id, 'personal_siape', sanitize_text_field( $_POST['personal_siape'] ) );

	update_user_meta( $user_id, 'address_cep_code', sanitize_text_field( $_POST['address_cep_code'] ) );
	update_user_meta( $user_id, 'address_uf', sanitize_text_field( $_POST['address_uf'] ) );
	update_user_meta( $user_id, 'address_city', sanitize_text_field( $_POST['address_city'] ) );
	update_user_meta( $user_id, 'address_neighborhood', sanitize_text_field( $_POST['address_neighborhood'] ) );
	update_user_meta( $user_id, 'address_public_place', sanitize_text_field( $_POST['address_public_place'] ) );
	update_user_meta( $user_id, 'address_number', sanitize_text_field( $_POST['address_number'] ) );
	update_user_meta( $user_id, 'address_complement', sanitize_text_field( $_POST['address_complement'] ) );

	update_user_meta( $user_id, 'public_servant_bond_type', sanitize_text_field( $_POST['public_servant_bond_type'] ) );
	update_user_meta( $user_id, 'public_servant_bond_category', sanitize_text_field( $_POST['public_servant_bond_category'] ) );
	update_user_meta( $user_id, 'public_servant_bond_position', sanitize_text_field( $_POST['public_servant_bond_position'] ) );
	update_user_meta( $user_id, 'public_servant_bond_class', sanitize_text_field( $_POST['public_servant_bond_class'] ) );
	update_user_meta( $user_id, 'professor_bond_class_levels', sanitize_text_field( $_POST['professor_bond_class_levels'] ) );
	update_user_meta( $user_id, 'public_servant_bond_class', sanitize_text_field( $_POST['public_servant_bond_class'] ) );


	if ( isset( $_POST['public_servant_role'] ) ) {
		$user = new WP_User( $user_id );

		// 1. Remove ALL roles (fixes indexing issues)
		foreach ( $user->roles as $role ) {
			$user->remove_role( $role );
		}

		// 2. Remove ALL direct capabilities (if any)
		foreach ( $user->allcaps as $cap => $has_cap ) {
			$user->remove_cap( $cap );
		}

		// 3. Set the new role (clean slate)
		$new_role = sanitize_text_field( $_POST['public_servant_role'] );
		$user->set_role( $new_role ); // Overwrites any remaining caps
	}

	update_user_meta( $user_id, 'bond_status', sanitize_text_field( $_POST['bond_status'] ) );

	update_user_meta( $user_id, 'workplace_extension', sanitize_text_field( $_POST['workplace_extension'] ) );
	update_user_meta( $user_id, 'workplace_place', sanitize_text_field( $_POST['workplace_place'] ) );
}

function intranet_fafar_load_admin_scripts( $hook_suffix ) {
	// Carrega apenas em páginas administrativas ligadas ao usuário
	if ( $hook_suffix === 'user-edit.php' || $hook_suffix === 'user-new.php' || $hook_suffix === 'profile.php' ) {
		// Carrega script CSS
		wp_enqueue_style(
			'my-custom-user-style',
			get_stylesheet_directory_uri() . '/assets/admin/css/user-extra-fields.css',
			[],
			'1.0.0'
		);

		// Enqueue the jQuery Mask Plugin
		wp_enqueue_script(
			'jquery-mask-plugin',
			'https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js', // CDN version
			[ 'jquery' ], // Depends on jQuery
			'1.14.16',
			true
		);

		// Carrega script JS
		wp_enqueue_script(
			'my-custom-user-script',
			get_stylesheet_directory_uri() . '/assets/admin/js/user-extra-fields.js',
			[ 'jquery', 'jquery-mask-plugin' ], // Dependência: jQuery
			'1.0.0',
			true // Carrega o script no footer
		);

	}
}
