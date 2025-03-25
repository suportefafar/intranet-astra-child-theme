<?php
// Prevent direct access to the file.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/*
 * Impedir que usuários editem perfis de outros, no BuddyPress
 * O BuddyPress usa a capacidade bp_moderate, 
 * que muitas vezes é concedida a administradores e, 
 * em alguns casos, a usuários comuns, dependendo da configuração.
 */

// Remover a permissão globalmente
add_action('bp_actions', 'restrict_bp_profile_editing');

// Verificar se o usuário está editando o próprio perfil
add_action('bp_actions', 'allow_only_own_profile_editing');

// Remover o botão "Editar Perfil" para outros usuários
add_action('bp_member_header_actions', 'hide_bp_edit_profile_button', 1);

// Bloquear acesso à Perfil, Notificações e Configurações
add_action('bp_actions', 'intraner_fafar_block_for_not_profile_owners');

// Bloquear todo mundo de qualquer coisa
// add_action('template_redirect', 'redirect_all_users');

function restrict_bp_profile_editing() {
    if (!current_user_can('manage_options') && bp_is_user_profile_edit()) {
        wp_redirect(home_url());
        exit;
    }
}

function allow_only_own_profile_editing() {
    if (bp_is_user_profile_edit() && bp_displayed_user_id() !== get_current_user_id()) {
        wp_redirect(bp_core_get_user_domain(get_current_user_id()));
        exit;
    }
}

function hide_bp_edit_profile_button() {
    if (bp_displayed_user_id() !== get_current_user_id()) {
        remove_action('bp_member_header_actions', 'bp_member_header_edit_profile_button', 10);
    }
}

function intraner_fafar_block_for_not_profile_owners() {
    if (bp_is_user() && !bp_is_my_profile()) {
        $componentes_bloqueados = array('profile', 'settings', 'notifications');

        // Se estiver acessando uma página bloqueada, redireciona
        if (in_array(bp_current_component(), $componentes_bloqueados)) {
            // bp_core_add_message(__('Você não tem permissão para editar este perfil.'), 'error');
            bp_core_redirect(bp_loggedin_user_domain());
        }
    }
}

function redirect_all_users() {
    // Check if user is logged in
    if ( is_user_logged_in() ) {
        // Get current user ID
        $current_user_id = get_current_user_id();
        
        // If user is not ID 23, redirect them
        if ( in_array( $current_user_id, [1, 2078, 2041] ) ) {
            wp_redirect('https://intranet.farmacia.ufmg.br/wp-login.php?redirect_to=https%3A%2F%2Fintranet.farmacia.ufmg.br%2F'); // Change to your desired redirect URL
            exit;
        }
    }
}
