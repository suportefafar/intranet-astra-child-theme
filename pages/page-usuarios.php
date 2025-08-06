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

/*
 * Obtendo o setor do usuário para limitar ou permitir algumas funcionalidades
 */
$user = wp_get_current_user();

// Safely get the first role (if exists)
$sector_slug = !empty($user->roles) ? $user->roles[0] : '';

// Only expose necessary information
$user_logged_params = array(
    'displayName' => $user->display_name,
    // Consider if user_login is actually needed
    'userLogin' => $user->user_login,
    'sectorSlug' => $sector_slug
);

// Only localize if the script is registered
if ( wp_script_is( 'intranet-fafar-usuarios', 'registered' ) ) {
    wp_localize_script( 'intranet-fafar-usuarios', 'userLogged', $user_logged_params );
} else {
    error_log( 'Script intranet-fafar-usuarios not registered' );
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
        <!-- HEADER BUTTONS -->

        <div class="d-flex justify-content-between align-items-end mb-4">
            <h6 class="mb-0">
                <span id="users_counter">0</span> usuário(s)
            </h6>
            <div class="d-flex gap-2">
                <?php
                    if( in_array( $sector_slug, array( 'pessoal', 'administrator' ) ) ):
                ?>

                <a href="/wp-admin/user-new.php" class="btn btn-outline-success text-decoration-none w-lg-button" target="blank" title="Adicionar um novo usuário">
                    <i class="bi bi-plus-lg"></i>
                    Adicionar
                </a>

                <button type="button" id="btn_export_users" class="btn btn-outline-dark text-decoration-none w-lg-button">
                    <i class="bi bi-arrow-bar-down"></i>
                    Exportar CSV
                </button>
                
                <?php endif; ?>
                
                <button type="button" id="btn_copy_emails" class="btn btn-light text-decoration-none w-lg-button" >
                    <i class="bi bi-clipboard"></i>
                    Copiar e-mails
                </button>
            </div>
        </div>

        <div id="filters_container" class="d-flex gap-3 mb-2">
            <input type="text" id="input_keyword" class="form-control" placeholder="Pesquise por palavra-chave..." aria-label="Nome" aria-describedby="Campo para busca por palavra-chave">
            <input type="text" id="input_place" class="form-control" placeholder="Pesquise por sala...." aria-label="Sala" aria-describedby="Campo para busca por sala">
            <?php
                $bond_status = get_option('bond_status', []);
            
                echo intranet_fafar_utils_render_dropdown_menu( 
                    array( 
                        'options'        => $bond_status,
                        'options_values' => $bond_status,
                        'name'           => 'select_bond_status',
                        'id'             => 'select_bond_status',
                        'placeholder'    => 'Todos os status',
                        'selected'       => 'ATIVO',
                    ) 
                );
            ?>
            <?php
                $bond_categories = get_option('bond_categories', []);
            
                echo intranet_fafar_utils_render_dropdown_menu( 
                    array( 
                        'options'        => $bond_categories,
                        'options_values' => $bond_categories,
                        'name'           => 'select_bond_categories',
                        'id'             => 'select_bond_categories',
                        'placeholder'    => 'Todos as categorias'
                    ) 
                );
            ?>
            <?php
                $roles = intranet_fafar_utils_get_all_roles();

                $roles_blacklist = array( 'Administrator', 'Editor', 'Author', 'Contributor', 'Subscriber' );
                $not_wp_roles    = array_filter( $roles, function( $role ) use ( $roles_blacklist ) {
                    return ! in_array( $role['name'], $roles_blacklist );
                } );
                    
                $roles_display_names = array_map( function ( $role ) {
                    return $role['name'];
                }, $not_wp_roles );
                $roles_display_names = array_values( $roles_display_names );

                $roles_slugs = array_map( function ( $slug ) {
                    return $slug;
                }, array_keys( $not_wp_roles ) );

                echo intranet_fafar_utils_render_dropdown_menu( 
                    array( 
                        'options'        => $roles_display_names,
                        'options_values' => $roles_slugs,
                        'name'           => 'select_public_servant_role',
                        'id'             => 'select_public_servant_role',
                        'placeholder'    => 'Todos os setores',
                    ) 
                );
            ?>
            <button type="button" class="d-flex gap-1 btn btn-primary" id="search_button">
                <i class="bi bi-search"></i>
                <span>
                    Pesquisar
                </span>
            </button>
        </div>

        <div id="table-wrapper"></div>

        <!-- MODAL -->
        
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
