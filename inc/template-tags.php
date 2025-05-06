<?php
// Prevent direct access to the file.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * Adicionando o HTML do Bootstrap Alert
 */
add_action( 'wp_footer', 'intranet_fafar_add_bootstrap_alert_html' );
/*
 * Adicionando o HTML do Bootstrap Modal de confirmação
 */
add_action( 'wp_footer', 'intranet_fafar_add_bootstrap_confirm_modal_html' );
/*
 * Adicionando o HTML do Bootstrap Toast
 */
add_action( 'wp_footer', 'add_bootstrap_toast_every_page' );


function intranet_fafar_add_bootstrap_alert_html() {
	?>
	<!-- Bootstrap Toast HTML -->
	<div id="intranetFafarLiveAlertPlaceholder"></div>
	<?php
}

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