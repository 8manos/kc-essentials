<?php

/**
 * Dropdown menu module
 * @package KC_Essentials
 * @since 0.3
 */

function kc_dropdown_menu( $menu_id, $args = array() ) {
	$menu = wp_get_nav_menu_object( $menu_id );
	if ( !$menu )
		return;

	$items = wp_get_nav_menu_items( $menu_id );
	if ( !$items || is_wp_error($items) )
		return;

	$args = wp_parse_args( $args, array(
		'depth'       => 0,
		'pad'         => '&mdash;',
		'echo'        => true,
		'submit_text' => __('Go', 'kc-essentials'),
		'select_text' => '',
		'js'          => false,
		'menu_class'  => '',
		'menu_id'     => ''
	) );

	$walk = new kcWalker_Menu;
	$walk->pad = $args['pad'];
	$_menu_items = $walk->walk( $items, $args['depth'], $args );
	$menu_items = array();
	foreach ( $_menu_items as $url => $title )
		$menu_items[ rtrim($url, '/') ] = $title;

	$current_url = rtrim( kc_get_current_url(), '/' );

	$f_args = array(
		'type' => 'select',
		'attr' => array( 'name' => 'kcform[url]' ),
		'options' => $menu_items
	);
	if ( !empty($args['select_text']) ) {
		$f_args['none'] = $args['select_text'];
	}
	else {
		$f_args['current'] = $current_url;
		$f_args['none'] = false;
	}

	$class = 'kcss';
	if ( $args['menu_class'] )
		$class .= " {$args['menu_class']}";

	$f_attr = 'class="'.$class.'" method="post" action="'.$current_url.'"';
	if ( $args['menu_id'] )
		$f_attr .= ' id="'.$args['menu_id'].'"';

	$out = '
<form '.$f_attr.'>
	'.kcForm::field( $f_args ).'
	<input type="hidden" name="kcform[current]" value="'.$current_url.'" />
	<button type="submit" name="kcform[action]" value="menu">'.$args['submit_text'].'</button>
</form>
	';

	if ( $args['echo'] )
		echo $out;
	else
		return $out;
}
