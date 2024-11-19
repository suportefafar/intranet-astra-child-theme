<?php
/**
 * astra-intranet Theme functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package astra-intranet
 * @since 1.0.0
 */

/**
 * Define Constants
 */
define( 'CHILD_THEME_ASTRA_INTRANET_VERSION', '1.0.0' );

/**
 * Enqueue styles
 */
function child_enqueue_styles() {

	wp_enqueue_style( 'astra-intranet-theme-css', get_stylesheet_directory_uri() . '/style.css', array('astra-theme-css'), CHILD_THEME_ASTRA_INTRANET_VERSION, 'all' );

}

add_action( 'wp_enqueue_scripts', 'child_enqueue_styles', 15 );

/*
 *	
 *	
 *	
 *	
 *	
 *	
 *	
 *	
 *	
 *	    <<<<<<<<<<<<< START >>>>>>>>>>>
 *		ADDED BY Setor de Suporte e T.I. 
*/

require_once 'class-wp-bootstrap-navwalker.php';

require_once 'import-scripts.php';

require_once 'rrule.php';

require_once 'api.php';

require_once 'shortcodes.php';

//require_once 'logs.php';

/*
 * Isso é uma gambiarra para lidar com um problema no CF7:
 * https://stackoverflow.com/questions/78101215/contact-form-undefined-value-was-submitted-through-this-field
 * 
 * Para mim, o problema no campo IP de um equipamento, no formulário de editar. 
 * Dava erro ao concluir a edição com o mesmo IP que já estava.
 * De tempos em tempos eu venho aqui para ver se já foi corrigido.
 * 
 * Re-visitado em: --/--/----
 */
remove_action( 'wpcf7_swv_create_schema', 'wpcf7_swv_add_select_enum_rules', 20, -1 );

/*
 * Adicionando checagem para criação de reservas.
 */
add_filter( 'fafar_cf7crud_before_create', 'intranet_fafar_api_create_or_update_reservation', 10, 2 );

/*
 * Adicionando checagem para criação de reservas.
 */
add_filter( 'fafar_cf7crud_before_update', 'intranet_fafar_api_create_or_update_reservation', 10, 2 );

/*
 * Adicionando checagem para criação de empréstimos de equipamentos.
 */
add_filter( 'fafar_cf7crud_before_create', 'intranet_fafar_api_create_new_loan', 10, 2 );

/*
 * Adicionando checagem para registrar o retorno de um empréstimo
 */
add_filter( 'fafar_cf7crud_before_update', 'intranet_fafar_api_register_loan_return', 10, 2 );

/*
 * Adicionando checagem para registrar atualização de ordem de serviço
 */
add_filter( 'fafar_cf7crud_before_create', 'intranet_fafar_api_insert_update_on_service_ticket', 10, 2 );

/*
 * Mudando o caminho dos arquivos template 'page-NOME_DA_PAGINA.php'
 */
function custom_page_template_hierarchy( $templates ) {
    $new_templates = array();

    // Add custom directory for page templates
    foreach ( $templates as $template ) {
        $new_templates[] = 'pages/' . $template;
    }

    // Merge with default template hierarchy
    $new_templates = array_merge( $new_templates, $templates );

    return $new_templates;
}
add_filter( 'page_template_hierarchy', 'custom_page_template_hierarchy' );

/*
 * Adicionando o HTML do Bootstrap Alert
 */
function intranet_fafar_add_bootstrap_alert_html() {
    ?>
    <!-- Bootstrap Toast HTML -->
	<div id="intranetFafarLiveAlertPlaceholder"></div>
    <?php
}
add_action('wp_footer', 'intranet_fafar_add_bootstrap_alert_html');

/*
 * Importanto script JS do Bootstrap Alert
 */
add_action( 'wp_enqueue_scripts', function () {

	wp_enqueue_script( 'intranet-fafar-alert', get_stylesheet_directory_uri() . '/assets/js/alert.js', array( 'jquery' ), false, true );

} );

/*
 * Adicionando o HTML do Bootstrap Modal de confirmação
 */
function intranet_fafar_add_bootstrap_confirm_modal_html() {

	?>

		<div id="intranetFafarConfirmModal" class="modal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                  <div class="modal-header">
                      <h5 class="modal-title">Modal title</h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                      <p>Modal body text goes here.</p>
                  </div>
                  <div class="modal-footer">
                      <button type="button" id="btn_deny" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                      <button type="button" id="btn_accept" class="btn btn-primary">Save changes</button>
                  </div>
                </div>
            </div>
        </div>

	<?php

}
add_action('wp_footer', 'intranet_fafar_add_bootstrap_confirm_modal_html');

/*
 * Importanto script JS do Bootstrap Modal de confirmação
 */
add_action( 'wp_enqueue_scripts', function () {

	wp_enqueue_script( 'intranet-fafar-confirm-modal', get_stylesheet_directory_uri() . '/assets/js/confirm-modal.js', array( 'jquery' ), false, true );

} );

/*
 * Adicionando o HTML do Bootstrap Toast
 */
function add_bootstrap_toast_every_page() {
    ?>
    <!-- Bootstrap Toast HTML -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3">
      <div id="globalToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header">
          <img src="..." class="rounded me-2" alt="...">
          <strong class="me-auto">Notification</strong>
          <small class="text-muted">Just now</small>
          <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body">
          This toast appears on every page!
        </div>
      </div>
    </div>
    <?php
}
add_action('wp_footer', 'add_bootstrap_toast_every_page');

/*
 * Importanto script JS do Bootstrap Toast
 */
add_action( 'wp_enqueue_scripts', function () {

	wp_enqueue_script( 'intranet-fafar-toast', get_stylesheet_directory_uri() . '/assets/js/toast.js', array( 'jquery' ), false, true );

} );
