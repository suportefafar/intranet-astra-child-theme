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
add_action('bp_actions', 'fafar_intranet_actions_restrict_bp_profile_editing');

// Verificar se o usuário está editando o próprio perfil
add_action('bp_actions', 'fafar_intranet_actions_allow_only_own_profile_editing');

// Remover o botão "Editar Perfil" para outros usuários
add_action('bp_member_header_actions', 'fafar_intranet_actions_hide_bp_edit_profile_button', 1);

// Bloquear acesso à Perfil, Notificações e Configurações
add_action('bp_actions', 'fafar_intranet_actions_block_for_not_profile_owners');

// Bloquear todo mundo de qualquer coisa
// add_action('template_redirect', 'fafar_intranet_actions_redirect_all_users');

// Add data to profile user
add_action( 'bp_after_member_header', 'fafar_intranet_actions_add_custom_profile_info' );

function fafar_intranet_actions_restrict_bp_profile_editing() {
    if (!current_user_can('manage_options') && bp_is_user_profile_edit()) {
        wp_redirect(home_url());
        exit;
    }
}

function fafar_intranet_actions_allow_only_own_profile_editing() {
    if (bp_is_user_profile_edit() && bp_displayed_user_id() !== get_current_user_id()) {
        wp_redirect(bp_core_get_user_domain(get_current_user_id()));
        exit;
    }
}

function fafar_intranet_actions_hide_bp_edit_profile_button() {
    if (bp_displayed_user_id() !== get_current_user_id()) {
        remove_action('bp_member_header_actions', 'bp_member_header_edit_profile_button', 10);
    }
}

function fafar_intranet_actions_block_for_not_profile_owners() {
    if (bp_is_user() && !bp_is_my_profile()) {
        $componentes_bloqueados = array('profile', 'settings', 'notifications');

        // Se estiver acessando uma página bloqueada, redireciona
        if (in_array(bp_current_component(), $componentes_bloqueados)) {
            // bp_core_add_message(__('Você não tem permissão para editar este perfil.'), 'error');
            bp_core_redirect(bp_loggedin_user_domain());
        }
    }
}

function fafar_intranet_actions_redirect_all_users() {
    // Check if user is logged in
    if ( is_user_logged_in() ) {
        // Get current user ID
        $current_user_id = get_current_user_id();
        
        // If user is not ID 23, redirect them
        if ( ! in_array( $current_user_id, [1, 2078, 2041] ) ) {
            wp_redirect('https://intranet.farmacia.ufmg.br/wp-login.php?redirect_to=https%3A%2F%2Fintranet.farmacia.ufmg.br%2F'); // Change to your desired redirect URL
            exit;
        }
    }
}

function fafar_intranet_actions_add_custom_profile_info() {
    $user_id = bp_displayed_user_id();
    $user    = (array) get_userdata( intval( $user_id ) );
          
    $workplace_place     = intranet_fafar_api_get_submission_by_id( esc_attr( get_the_author_meta( 'workplace_place', $user_id ) ) );
    $workplace_extension = esc_attr( get_the_author_meta( 'workplace_extension', $user_id ) );

    $role_slug  = ( isset( $user['roles'][0] ) ? $user['roles'][0] : '' );

    $role_display_name = '--';
    if ( isset( wp_roles()->roles[ $role_slug ] ) ) {
        $role_display_name = wp_roles()->roles[ $role_slug ]['name'];
    }

    ?>
        <div class="d-flex gap-4 flex-column flex-sm-row">
            <div>
                <i class="bi bi-bookmark"></i>
                <?php if ( ! empty( $role_display_name ) ): ?>
                <span class="fw-medium"><?php echo ( $role_display_name ) ?></span>
                <?php endif; ?>
            </div>
            <div>
                <i class="bi bi-envelope"></i>
                <span class="fw-medium">
                    <?php if ( ! empty( $user['data']->user_email ) ): ?>
                    <a href="mailto:<?= $user['data']->user_email ?>" target="_blank" title="Enviar para <?= $user['data']->user_email ?>">
                        <?= $user['data']->user_email ?>
                    </a>
                    <?php endif; ?>
                </span>
            </div>
            <div>
                <i class="bi bi-geo-alt"></i>
                <span class="fw-medium">
                    <?php if ( ! empty( $workplace_place ) && ! isset( $workplace_place['error_msg'] ) ): ?>
                    <a href="/visualizar-sala?id=<?= $workplace_place['id'] ?>" target="_blank" title="Perfil de <?= $workplace_place['data']['number'] ?>">
                        <?= $workplace_place['data']['number'] ?>
                    </a>
                    <?php endif; ?>
                </span>
            </div>
            <div>
                <i class="bi bi-telephone fs-6 fw-light"></i>
                <?php if ( ! empty( $workplace_extension ) ): ?>
                <span class="fw-medium"><?php echo ( $workplace_extension ) ?></span>
                <?php endif; ?>
            </div>
        </div>
    <?php 
}