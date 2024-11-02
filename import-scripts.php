<?php

add_action( 'wp_head', 'add_header_custom_scripts' );
add_action( 'wp_footer', 'add_footer_custom_scripts' );
add_action( 'wp_footer', 'add_footer_custom_scripts_by_page' );


function add_header_custom_scripts() {

	?>
		<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous" />
		<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" />
        <link href="https://unpkg.com/gridjs/dist/theme/mermaid.min.css" rel="stylesheet" />
		<script src='https://cdn.jsdelivr.net/npm/rrule@2.6.4/dist/es5/rrule.min.js'></script>
		<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js'></script>
		<script src="https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.15/locales-all.global.min.js"></script>
		<script src='https://cdn.jsdelivr.net/npm/@fullcalendar/rrule@6.1.15/index.global.min.js'></script>
		<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.14.4/dist/sweetalert2.min.css" rel="stylesheet" />
	<?php

}


function add_footer_custom_scripts() {

	?>
		<script src="https://unpkg.com/axios/dist/axios.min.js"></script>
		<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script src="https://unpkg.com/gridjs/dist/gridjs.umd.js"></script>
		<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.14.4/dist/sweetalert2.all.min.js"></script>
	<?php

}



/** 
  * Carregando conteúdo extra com base na página
**/
function add_footer_custom_scripts_by_page() {

	if( is_page( "logs" ) ){
		echo '<script type="module" src="' . get_stylesheet_directory_uri() . '/assets/js/logs.js"></script>';
	}

	if( false && is_page( "disciplinas" ) ){
		echo '<script type="module" src="' . get_stylesheet_directory_uri() . '/assets/js/disciplinas.js"></script>';
	}
	
	if( is_page( "salas" ) ){
		echo '<script type="module" src="' . get_stylesheet_directory_uri() . '/assets/js/salas.js"></script>';
	}

	if( is_page( "reservas-por-sala" ) ) {
		
		echo '<script type="module" src="' . get_stylesheet_directory_uri() . '/assets/js/reservas-por-sala.js"></script>';
	}

	if( is_page( "reservas" ) ) {
		
		echo '<script type="module" src="' . get_stylesheet_directory_uri() . '/assets/js/reservas.js"></script>';
	}

	if( is_page( "reservas-por-disciplina" ) ) {
		
		echo '<script type="module" src="' . get_stylesheet_directory_uri() . '/assets/js/reservas-por-disciplina.js"></script>';
	}

	if( is_page( "assistente-de-reservas-de-salas" ) ) {
		echo '<script type="module" src="' . get_stylesheet_directory_uri() . '/assets/js/assistente-de-reservas-de-salas.js"></script>';
	}

}
