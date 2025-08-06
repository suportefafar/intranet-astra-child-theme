<?php
// Prevent direct access to the file
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class FAFAR_Sidebar_Menu_Walker extends Walker_Nav_Menu {
	function start_lvl( &$output, $depth = 0, $args = null ) {
		if ( $depth === 0 ) {
			$output .= '<ul class="dropdown-menu">';
		} else {
			$output .= '<ul class="navbar-nav">';
		}
	}

	// function end_lvl( &$output, $depth=0, $args=null ) {

	// }

	function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
		// Verifica se é um elemento pai ou filho
		$IS_CHILDREN = false;
		if ( $depth > 0 ) {
			$IS_CHILDREN = true;
		}

		// Gerando as classes do <li>
		$li_classes = [];
		if ( ! $IS_CHILDREN ) {
			$li_classes[] = 'nav-item';
			$li_classes[] = 'mb-2';
		}

		// Verifica tem um link para criar o attr 'href' do <a>
		$attr_href = '';
		if ( $item->url && $item->url != '#' ) {
			$attr_href .= ' href="' . $item->url . '"';
		}

		// Gerando as classes do <a>
		$anchor_classes = [];
		if ( ! $IS_CHILDREN ) {
			$anchor_classes[] = 'nav-link';
		} else {
			$anchor_classes[] = 'dropdown-item';
		}

		// Obtendo URL atual para ativar o item
		global $wp;
		$current_url = home_url( $wp->request );

		// Se o item é o link da página atual
		if (
			$item->url &&
			trailingslashit( $current_url ) === trailingslashit( $item->url )
		) {
			$anchor_classes[] = 'text-primary';
		}

		// Demais atributos para <a>
		$anchor_attrs = [];

		// Se um o elemento tem filho
		if ( $args->walker->has_children ) {
			$li_classes[] = 'dropdown';

			$anchor_classes[] = 'dropdown-toggle';

			$anchor_attrs[] = 'role="button"';
			$anchor_attrs[] = 'data-bs-toggle="dropdown"';
			$anchor_attrs[] = 'aria-expanded="false"';
		}

		$output .= '<li';

		// Insere as classes do <li>
		$output .= ' class="' . implode( ' ', $li_classes ) . '"';

		$output .= '>';

		$output .= '<a';

		// Insere o attr 'href', se houver
		$output .= $attr_href;

		// Insere as classes do <a>
		$output .= ' class="' . implode( ' ', $anchor_classes ) . '"';

		// Insere um title, se for um link
		if ( $attr_href )
			$output .= ' title="Acesse a página ' . $item->title . '"';

		$output .= implode( ' ', $anchor_attrs );

		// Fecha a tag '<a>' de abertura
		$output .= '>';

		// Insere o texto
		$output .= $args->link_before . apply_filters( 'the_title', $item->title, $item->ID ) . $args->link_after;

		// Fecha a tag
		$output .= '</a>';
	}

	function start_el_old( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
		// Verifica se é um elemento pai ou filho
		if ( $depth === 0 ) {
			$output .= '<li class="nav-item">';
		} else {
			$output .= '<li>';
		}

		// Verifica tem um link
		if ( $item->url && $item->url != '#' ) {
			$output .= '<a href="' . $item->url . '"';
		} else {
			$output .= '<a';
		}

		// Obtendo URL atual para ativar o item
		global $wp;
		$current_url = home_url( $wp->request );

		// Iniciando array de classes da tag <a>
		$anchor_classes = [ 'nav-link' ];

		// Se o item é o link da página atual
		if (
			$item->url &&
			trailingslashit( $current_url ) === trailingslashit( $item->url )
		) {
			$anchor_classes[] = 'text-primary';
		}

		// Insere as classes do link do item
		$output .= ' class="' . implode( ' ', $anchor_classes ) . '"';

		// Insere um title
		$output .= ' title="Acesse a página ' . $item->title . '"';

		// Fecha a tag '<a>' de abertura
		$output .= '>';

		// Insere o texto
		$output .= $args->link_before . apply_filters( 'the_title', $item->title, $item->ID ) . $args->link_after;

		// Fecha a tag
		$output .= '</a>';
	}

	// function end_el( &$output, $item, $depth=0, $args=null ) {

	// }
}