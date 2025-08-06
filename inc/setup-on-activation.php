<?php
// Prevent direct access to the file.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'after_switch_theme', 'intranet_fafar_initial_setup' );

function intranet_fafar_initial_setup() {

	if ( get_template() === 'astra' && get_stylesheet() === 'intranet-astra-child-theme' ) {
		// Grava os tipos de vínculo possíveis
		$bond_types = [ 
			'EFETIVO',
			'SUBSTITUTO',
			'VOLUNTÁRIO'
		];
		update_option( 'bond_types', $bond_types );

		// Grava as categorias de vínculo possíveis
		$bond_categories = [ 
			'DOCENTE',
			'TAE',
			'TERCEIRIZADO',
		];
		update_option( 'bond_categories', $bond_categories );

		// Grava os cargos de vínculo possíveis para docentes
		$professor_bond_positions = [ 
			'PROFESSOR DO MAGISTÉRIO SUPERIOR'
		];
		update_option( 'professor_bond_positions', $professor_bond_positions );

		// Grava os cargos de vínculo possíveis para TAEs
		$tae_bond_positions = [ 
			'ADMINISTRADOR',
			'ALMOXARIFE',
			'ANALISTA DE SISTEMA',
			'ASSISTENTE ADMINISTRATIVO',
			'ASSISTENTE DE LABORATÓRIO ',
			'AUXILIAR DE ADMINISTRAÇÃO',
			'AUXILIAR DE LABORATÓRIO ',
			'BIBLIOTECÁRIO',
			'BIÓLOGO',
			'BOMBEIRO HIDRÁULICO',
			'CONTADOR',
			'FARMACÊUTICO',
			'GERENTE DE QUALIDADE',
			'MÉDICO VETERINÁRIO',
			'MOTORISTA',
			'REPROGRAFIA',
			'SECRETÁRIO EXECUTIVO',
			'SERVENTE DE OBRAS',
			'TÉCNICO ADMINISTRATIVO',
			'TÉCNICA ASSUNTOS EDUCACIONAIS',
			'TÉCNICO QUIMICO',
			'TÉCNICO DE ALIMENTOS E LATICÍNIOS',
			'TÉCNICO DE FARMÁCIA',
			'TÉCNICO DE LABORATÓRIO/ANÁLISES CLÍNICAS',
			'TÉCNICO DE LABORATÓRIO/BIOLOGIA',
			'TÉCNICO DE LABORATÓRIO/FÍSICA',
			'TÉCNICO DE LABORATÓRIO/INDUSTRIAL',
			'TÉCNICO DE LABORATÓRIO/QUÍMICA',
			'TÉCNICO EM CONTABILIDADE',
			'TÉCNICO EM TECNOLOGIA DA INFORMAÇÃO'
		];
		update_option( 'tae_bond_positions', $tae_bond_positions );

		// Grava as classes de vínculo possíveis para docentes
		$professor_bond_classes = [ 
			'AUXILIAR',
			'ADJUNTO A',
			'ADJUNTO',
			'ASSOCIADO',
			'TITULAR'
		];
		update_option( 'professor_bond_classes', $professor_bond_classes );

		/* 
		 * Grava os níveis de classe de vínculo possíveis para docentes
		 * Guardei com string para não dar algum problema idiota de tipo
		 */
		$professor_bond_class_levels = [ 
			'1',
			'2',
			'3',
			'4'
		];
		update_option( 'professor_bond_class_levels', $professor_bond_class_levels );

		// Grava as classes de vínculo possíveis para TAEs
		$tae_bond_classes = [ 
			'A',
			'B',
			'C',
			'D',
			'E'
		];
		update_option( 'tae_bond_classes', $tae_bond_classes );

		// Grava os status de vínculo possíveis
		$bond_status = [ 
			'ATIVO',
			'APOSENTADO',
			'DESLIGADO',
			'REMOVIDO'
		];
		update_option( 'bond_status', $bond_status );

		/*
		 * Remove todos os 'roles' para caso a lista mude.
		 * Se não remover, ao adicionar, o novo 'role' irá para o 
		 * final da lista. 
		 * Além disso, exclui todos os 'roles' padrões do WP, que não 
		 * são necessários.
		 */
		intranet_fafar_remove_all_roles_except_admin();

		/* 
		 * Grava os setores de trabalho possíveis, como 
		 * 'roles' do WordPress
		 */
		intranet_fafar_add_custom_roles();

		/*
		 * Cria menus padrões
		 */
		intranet_fafar_add_custom_roles_menus();
	}
}

function intranet_fafar_remove_all_roles_except_admin() {
	global $wp_roles;

	// Get all roles
	$roles = $wp_roles->roles;

	// Loop through all roles
	foreach ( $roles as $role => $details ) {
		// Skip the 'administrator' role
		if ( $role === 'administrator' ) {
			continue;
		}

		// Remove the role
		remove_role( $role );
	}
}

function intranet_fafar_add_custom_roles() {
	intranet_add_custom_capabilities();

	/* 
	 * Será criado 'roles' com base nos setores,
	 * pois o setor define a função do usuário
	 */
	$work_sectors = [ 
		array(
			'display_name' => 'ARQUIVO',
			'slug' => 'arquivo',
			'capabilities' => array(
				'read' => true,
			),
		),
		array(
			'display_name' => 'ACT',
			'slug' => 'act',
			'capabilities' => array(
				'read' => true,
			),
		),
		array(
			'display_name' => 'ALM',
			'slug' => 'alm',
			'capabilities' => array(
				'read' => true,
			),
		),
		array(
			'display_name' => 'ALMOXARIFADO',
			'slug' => 'almoxarifado',
			'capabilities' => array(
				'read' => true,
			),
		),
		array(
			'display_name' => 'APOIO LOGÍSTICO E OPERACIONAL',
			'slug' => 'apoio_logistico_e_operacional',
			'capabilities' => array(
				'read' => true,
				'manage_ticket' => true,
			),
		),
		array(
			'display_name' => 'ASSESSORIA DE ASSUNTOS EDUCACIONAIS',
			'slug' => 'assessoria_de_assuntos_educacionais',
			'capabilities' => array(
				'read' => true,
			),
		),
		array(
			'display_name' => 'BIBLIOTECA',
			'slug' => 'biblioteca',
			'capabilities' => array(
				'read' => true,
			),
		),
		array(
			'display_name' => 'BIOTÉRIO',
			'slug' => 'bioterio',
			'capabilities' => array(
				'read' => true,
			),
		),
		array(
			'display_name' => 'CENTRO DE MEMÓRIA',
			'slug' => 'centro_de_memoria',
			'capabilities' => array(
				'read' => true,
			),
		),
		array(
			'display_name' => 'COLEGIADO DE GRADUAÇÃO BIOMEDICINA',
			'slug' => 'colegiado_de_graduacao_biomedicina',
			'capabilities' => array(
				'read' => true,
			),
		),
		array(
			'display_name' => 'COLEGIADO DE GRADUAÇÃO FARMÁCIA',
			'slug' => 'colegiado_de_graduacao_farmacia',
			'capabilities' => array(
				'read' => true,
			),
		),
		array(
			'display_name' => 'CONTABILIDADE',
			'slug' => 'contabilidade',
			'capabilities' => array(
				'read' => true,
			),
		),
		array(
			'display_name' => 'COMPRAS',
			'slug' => 'compras',
			'capabilities' => array(
				'read' => true,
			),
		),
		array(
			'display_name' => 'DIRETORIA',
			'slug' => 'diretoria',
			'capabilities' => array(
				'read' => true,
			),
		),
		array(
			'display_name' => 'FARMÁCIA UNIVERSITÁRIA',
			'slug' => 'farmacia_universitaria',
			'capabilities' => array(
				'read' => true,
			),
		),
		array(
			'display_name' => 'FAS',
			'slug' => 'fas',
			'capabilities' => array(
				'read' => true,
			),
		),
		array(
			'display_name' => 'GERENCIAMENTO AMBIENTAL E BIOSSEGURANÇA',
			'slug' => 'gerenciamento_ambiental_e_biosseguranca',
			'capabilities' => array(
				'read' => true,
			),
		),
		array(
			'display_name' => 'PATRIMÔNIO',
			'slug' => 'patrimonio',
			'capabilities' => array(
				'read' => true,
			),
		),
		array(
			'display_name' => 'PESSOAL',
			'slug' => 'pessoal',
			'capabilities' => array(
				'read' => true,
				'edit_users' => true,
				'list_users' => true,
				'remove_users' => true,
				'add_users' => true,
				'create_users' => true,
				'delete_users' => true,
				'unfiltered_upload' => true,
				'edit_profile' => true,
				'edit_roles' => true,
				'delete_roles' => true,
				'create_roles' => true,
			),
		),
		array(
			'display_name' => 'PFA',
			'slug' => 'pfa',
			'capabilities' => array(
				'read' => true,
			),
		),
		array(
			'display_name' => 'PORTARIA',
			'slug' => 'portaria',
			'capabilities' => array(
				'read' => true,
			),
		),
		array(
			'display_name' => 'PPGCA',
			'slug' => 'ppgca',
			'capabilities' => array(
				'read' => true,
				'manage_form' => true,
			),
		),
		array(
			'display_name' => 'PPGCF',
			'slug' => 'ppgcf',
			'capabilities' => array(
				'read' => true,
				'manage_form' => true,
			),
		),
		array(
			'display_name' => 'PPGACT',
			'slug' => 'ppgact',
			'capabilities' => array(
				'read' => true,
				'manage_form' => true,
			),
		),
		array(
			'display_name' => 'PPGMAF',
			'slug' => 'ppgmaf',
			'capabilities' => array(
				'read' => true,
				'manage_form' => true,
			),
		),
		array(
			'display_name' => 'NAPQ/CENEX',
			'slug' => 'napq_cenex',
			'capabilities' => array(
				'read' => true,
			),
		),
		array(
			'display_name' => 'SECRETARIA GERAL',
			'slug' => 'secretaria_geral',
			'capabilities' => array(
				'read' => true,
			),
		),
		array(
			'display_name' => 'SECRETARIA EXECUTIVA',
			'slug' => 'secretaria_executiva',
			'capabilities' => array(
				'read' => true,
			),
		),
		array(
			'display_name' => 'SUPERINTENDÊNCIA ADMINISTRATIVA',
			'slug' => 'superintendencia_administrativa',
			'capabilities' => array(
				'read' => true,
			),
		),
		array(
			'display_name' => 'TECNOLOGIA DA INFORMAÇÃO E SUPORTE',
			'slug' => 'tecnologia_da_informacao_e_suporte',
			'capabilities' => array(
				'switch_themes' => true,
				'edit_themes' => true,
				'activate_plugins' => true,
				'edit_plugins' => true,
				'edit_users' => true,
				'edit_files' => true,
				'manage_options' => true,
				'moderate_comments' => true,
				'manage_categories' => true,
				'manage_links' => true,
				'upload_files' => true,
				'edit_posts' => true,
				'edit_others_posts' => true,
				'edit_published_posts' => true,
				'publish_posts' => true,
				'edit_private_posts' => true,
				'read_private_posts' => true,
				'edit_pages' => true,
				'edit_others_pages' => true,
				'edit_published_pages' => true,
				'edit_private_pages' => true,
				'publish_pages' => true,
				'read_private_pages' => true,
				'read' => true,
				'edit_dashboard' => true,
				'update_plugins' => true,
				'install_plugins' => true,
				'update_themes' => true,
				'install_themes' => true,
				'update_core' => true,
				'list_users' => true,
				'remove_users' => true,
				'add_users' => true,
				'create_users' => true,
				'unfiltered_upload' => true,
				'edit_profile' => true,
				'edit_roles' => true,
				'create_roles' => true,
				'customize' => true,
				'manage_ticket' => true,
			),
		),
	];


	foreach ( $work_sectors as $work_sector ) {

		add_role(
			$work_sector['slug'],
			$work_sector['display_name'],
			$work_sector['capabilities'],
		);

		error_log( $work_sector['slug'] );

	}

	return $work_sectors;
}

function intranet_fafar_add_custom_roles_menus() {

	$menus = array( 'ADMINISTRATOR', 'DEFAULT', 'HEADER' );

	foreach ( $menus as $menu ) {
		$menu_exists = wp_get_nav_menu_object( $menu );

		if ( ! $menu_exists ) {
			wp_create_nav_menu( $menu );
		}
	}
}

function intranet_add_custom_capabilities() {

	$role = get_role( 'administrator' );

	if ( $role ) {
		// Permite receber Ordens de Serviço
		$role->add_cap( 'manage_ticket' );
		// Permite gerenciar formulários e submissões
		$role->add_cap( 'manage_form' );
	}

}