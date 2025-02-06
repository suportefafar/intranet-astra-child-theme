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
 * wp_enqueue_script( 'intranet-fafar-usuarios-script', get_stylesheet_directory_uri() . '/assets/js/usuarios.js', array( 'jquery' ), false, false );
 */
wp_enqueue_script_module( 'intranet-fafar-usuarios-script', get_stylesheet_directory_uri() . '/assets/js/usuarios.js', array( 'jquery' ), false, false );

/*
 * Obtendo o setor do usuário para limitar ou permitir algumas funcionalidades
 */
$user        = wp_get_current_user();
$sector_slug = $user->roles[0];


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
        <!-- HEADER BUTTONS -->

        <div class="d-flex justify-content-end gap-2 mb-4">
            <?php
                if( in_array( $sector_slug, array( 'pessoal', 'tecnologia_da_informacao_e_suporte', 'administrator' ) ) ):
            ?>

            <a href="#" id="btn_export_users" class="btn btn-outline-dark text-decoration-none w-lg-button">
                <i class="bi bi-arrow-bar-down"></i>
                Exportar CSV
            </a>
            
            <?php endif; ?>
            
            <a href="#" id="btn_copy_emails" class="btn btn-light text-decoration-none w-lg-button" >
                <i class="bi bi-clipboard"></i>
                Copiar e-mails
            </a>
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
                $roles = intranet_fafar_get_all_roles();
                    
                $roles_display_names = array_map( function ( $role ) {
                    return $role['name'];
                }, $roles );
                $roles_display_names = array_values( $roles_display_names );

                $roles_slugs = array_map( function ( $slug ) {
                    return $slug;
                }, array_keys( $roles ) );

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
