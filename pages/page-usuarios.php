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
 * Obtendo o setor do usuário para limitar ou permitir algumas funcionalidades
 */
$user        = wp_get_current_user();
$sector_slug = $user->roles[0];


/*
 * Importanto script JS customizado
 * wp_enqueue_script( 'intranet-fafar-usuarios-script', get_stylesheet_directory_uri() . '/assets/js/usuarios.js', array( 'jquery' ), false, false );
 */
wp_enqueue_script_module( 'intranet-fafar-usuarios-script', get_stylesheet_directory_uri() . '/assets/js/usuarios.js', array( 'jquery' ), false, true );

$user_logged_params = array(
    'displayName' => $user->display_name,
    'userLogin'   => $user->user_login
);

get_header(); ?>

<script>
    const userLogged = <?php echo wp_json_encode( $user_logged_params, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT ); ?>;
</script>


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
            <input type="text" id="input_user_name" class="form-control" placeholder="Pesquise pelo nome" aria-label="Nome" aria-describedby="Campo para busca pelo nome">
            <?php
                $bond_status = get_option('bond_status', []);
            
                echo intranet_fafar_utils_render_dropdown_menu( 
                    array( 
                        'options'        => $bond_status,
                        'options_values' => $bond_status,
                        'name'           => 'select_bond_status',
                        'id'             => 'select_bond_status',
                        'placeholder'    => 'Selecione um status'
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
                        'placeholder'    => 'Selecione uma categoria'
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
                        'placeholder'    => 'Selecione um setor',
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
