<?php
// Prevent direct access to the file.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_shortcode( 'intranet_fafar_sidebar_profile', 'intranet_fafar_sidebar_profile' );

add_shortcode( 'intranet_fafar_sidebar_menu', 'intranet_fafar_sidebar_menu' );

function intranet_fafar_sidebar_profile() {

    $user       = wp_get_current_user();
    $avatar_url = get_avatar_url( $user->get( 'ID' ) );
    $role_slug  = ( isset( $user->roles[0] ) ? $user->roles[0] : '' );

    $role_display_name = '--';
    if ( isset( wp_roles()->roles[ $role_slug ] ) ) {
        $role_display_name = wp_roles()->roles[ $role_slug ]['name'];
    }

    echo '
        <div class="d-flex gap-4 mb-5">
            <a href="https://intranet.farmacia.ufmg.br/membros/' . $user->get( 'user_login' ) . '/profile/change-avatar/">
                <img src="' . $avatar_url . '" width="80" alt="User profile avatar" />
            </a>

            <div class="d-flex flex-column justify-content-center gap-1">
                <h6 class="p-0 m-0">
                    <a href="https://intranet.farmacia.ufmg.br/membros/' . $user->get( 'user_login' ) . '/" class="text-decoration-none">' . 
                    $user->get( 'display_name' ) . 
                    '</a>
                </h6>
                <small class="p-0 m-0 text-muted lh-base font-monospace">' . $role_display_name . '</small>
            </div>
        </div>
        ';
}

function intranet_fafar_sidebar_menu() {
    $user              = wp_get_current_user();
    $role_slug         = ( isset( $user->roles[0] ) ? $user->roles[0] : '' );
    $role_display_name = '';
    $menu_name         = 'DEFAULT';

    if ( isset( wp_roles()->roles[ $role_slug ] ) ) {
        $role_display_name = strtoupper( wp_roles()->roles[ $role_slug ]['name'] );
        $menu              = wp_get_nav_menu_object( $role_display_name );

        if ( $menu ) {
            $menu_name = $role_display_name;
        }
    }

    echo '<div style="min-height:16em">';
        echo wp_nav_menu(array(
            'menu' => $menu_name,
            'container' => false,
            'menu_class' => '',
            'fallback_cb' => '__return_false',
            'items_wrap' => '<ul id="%1$s" class="navbar-nav me-auto mb-2 mb-md-0 %2$s">%3$s</ul>',
            'depth' => 2,
            'walker' => new bootstrap_5_wp_nav_menu_walker()
        ));
    echo '</div>';

    if ( defined( 'WP_DEV_ENV' ) && WP_DEV_ENV === true ) {
        echo '
            <div class="badge text-bg-primary">
                Development Environment
            </div>
        ';
    }
}