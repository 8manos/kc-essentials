<?php

/**
 * Configurable menu widget module
 * @package KC_Essentials
 */


Class kc_widget_menu extends WP_Widget {

	function __construct() {
		$widget_ops = array( 'classname' => 'kcw_menu', 'description' => __('Configurable custom menu widget', 'kc-essentials') );
		$control_ops = array( 'width' => 200, 'height' => 350 );
		parent::__construct( 'kcw_menu', 'KC Custom Menu', $widget_ops, $control_ops );
	}


	function widget( $args, $instance ) {
		# Get menu
		$menu = ! empty( $instance['menu'] ) ? wp_get_nav_menu_object( $instance['menu'] ) : false;
		if ( !$menu )
			return;

		if ( !empty($instance['walker']) ) {
			if ( class_exists($instance['walker']) )
				$instance['walker'] = new $instance['walker'];
			else
				return;
		}

		$instance['echo'] = false;
		$instance['fallback_cb'] = '';
		if ( isset($instance['container']) && !$instance['container'] )
			$instance['container'] = false;

		if ( isset($instance['mode']) && $instance['mode'] == 'select' ) {
			require_once kcEssentials::get_data('paths', 'inc') . '/menu_dropdown.php';
			$instance['echo'] = false;
			$menu = kc_dropdown_menu( $menu->term_id, $instance );
		}
		else {
			$menu = wp_nav_menu( $instance );
		}
		if ( !$menu )
			return;

		$instance['title'] = apply_filters( 'widget_title', empty( $instance['title'] ) ? '' : $instance['title'], $instance, $this->id_base );
		if ( $instance['action_id'] ) {
			$args['before_widget'] = apply_filters( "kc_widget-{$instance['action_id']}", $args['before_widget'], 'before_widget', 'widget_menu' );
			$args['after_widget'] = apply_filters( "kc_widget-{$instance['action_id']}", $args['after_widget'], 'after_widget', 'widget_menu' );
		}
		unset( $instance['action_id'] );

		echo $args['before_widget'];

		if ( !empty($instance['title']) )
			echo $args['before_title'] . $instance['title'] . $args['after_title'];
		unset( $instance['title'] );

		echo $menu;
		echo $args['after_widget'];
	}


	function update( $new, $old ) {
		$new['title']           = strip_tags( stripslashes($new['title']) );
		$new['menu']            = (int) $new['menu'];
		$new['depth']           = absint($new['depth']);
		$new['container_class'] = kc_essentials_sanitize_html_classes( $new['container_class'] );
		$new['container_id']    = sanitize_html_class( $new['container_id'] );
		$new['menu_class']      = kc_essentials_sanitize_html_classes( $new['menu_class'] );
		$new['menu_id']         = sanitize_html_class( $new['menu_id'] );
		$new['walker']          = sanitize_html_class( $new['walker'] );
		$new['action_id']       = sanitize_html_class( $new['action_id'] );

		return $new;
	}


	function form( $instance ) {
		# Get menus
		$menus = get_terms( 'nav_menu', array( 'hide_empty' => false ) );

		# If no menus exists, direct the user to go and create some.
		if ( !$menus ) {
			echo '<p>'. sprintf( __('No menus have been created yet. <a href="%s">Create some</a>.', 'kc-essentials'), admin_url('nav-menus.php') ) .'</p>';
			return;
		}

		$nav_menus = array();
		foreach ( $menus as $m )
			$nav_menus[] = array( 'value' => $m->term_id, 'label' => $m->name );

		$options_main = array(
			'title' => array(
				'label'   => __('Title:'),
				'type'    => 'text'
			),
			'menu' => array(
				'label'   => __('Menu', 'kc-essentials'),
				'type'    => 'select',
				'options' => $nav_menus,
				'none'    => false
			),
			'depth' => array(
				'label'   => __('Depth', 'kc-essentials'),
				'type'    => 'text',
				'current' => isset( $instance['depth'] ) ? $instance['depth'] : '0'
			),
			'menu_class' => array(
				'label'   => __('Menu class', 'kc-essentials'),
				'type'    => 'text',
				'current' => isset( $instance['menu_class'] ) ? $instance['menu_class'] : 'menu'
			),
			'menu_id' => array(
				'label'   => __('Menu ID', 'kc-essentials'),
				'type'    => 'text'
			),
			'mode' => array(
				'label'   => __('Mode', 'kc-essentials'),
				'type'    => 'select',
				'options' => array(
					'list'   => __('List', 'kc-essentials'),
					'select' => __('Dropdown select', 'kc-essentials')
				),
				'none' => false,
				'current' => isset( $instance['mode'] ) ? $instance['mode'] : 'list',
				'attr'    => array(
					'class' => 'hasdep',
					'data-child' => '.' .$this->get_field_id('mode') .'-child'
				)
			)
		);

		$options_list = array(
			'container' => array(
				'label'   => __('Container tag', 'kc-essentials'),
				'type'    => 'select',
				'options' => array(
					'div' => '&lt;div /&gt;',
					'nav' => '&lt;nav /&gt;',
					'0'   => __('None', 'kc-essentials')
				),
				'none'    => false,
				'attr'    => array(
					'class' => 'hasdep',
					'data-child' => '.' .$this->get_field_id('container'). '-child'
				)
			),
			'container_class' => array(
				'label'   => __('Container class', 'kc-essentials'),
				'type'    => 'text',
				'wrap_attr' => array(
					'class' => $this->get_field_id('container') . '-child',
					'data-dep' => json_encode( array('div', 'nav') )
				)
			),
			'container_id' => array(
				'label'   => __('Container ID', 'kc-essentials'),
				'type'    => 'text',
				'wrap_attr' => array(
					'class' => $this->get_field_id('container') . '-child',
					'data-dep' => json_encode( array('div', 'nav') )
				)
			),
			'before' => array(
				'label'   => __('Before link text', 'kc-essentials'),
				'type'    => 'text'
			),
			'after' => array(
				'label'   => __('After link text', 'kc-essentials'),
				'type'    => 'text'
			),
			'link_before' => array(
				'label'   => __('Before link', 'kc-essentials'),
				'type'    => 'text'
			),
			'link_after' => array(
				'label'   => __('After link', 'kc-essentials'),
				'type'    => 'text'
			),
			'items_wrap' => array(
				'label'   => __('Items wrap', 'kc-essentials'),
				'type'    => 'text',
				'current' => isset( $instance['items_wrap'] ) ? $instance['items_wrap'] : '<ul id="%1$s" class="%2$s">%3$s</ul>'
			),
			'walker' => array(
				'label'   => __('Walker function', 'kc-essentials'),
				'type'    => 'text'
			),
			'action_id' => array(
				'label'   => __('Action ID', 'kc-essentials'),
				'type'    => 'text'
			)
		);

		$options_select = array(
			'submit_text' => array(
				'label' => __('Submit text', 'kc-essentials'),
				'type'  => 'text',
				'current' => ( isset( $instance['submit_text'] ) && $instance['submit_text'] ) ? $instance['submit_text'] : __('Go', 'kc-essentials')
			),
			'select_text' => array(
				'label' => __('Select text', 'kc-essentials'),
				'type'  => 'text',
				'current' => isset( $instance['select_text'] ) ? $instance['select_text'] : ''
			),
			'pad' => array(
				'label' => __('Pad', 'kc-essentials'),
				'type'  => 'text',
				'current' => isset( $instance['pad'] ) ? $instance['pad'] : '&mdash;'
			)
		);

		echo kcEssentials_widgets::form( $this, $options_main, $instance );
		echo kcEssentials_widgets::form( $this, $options_list, $instance, array(
			'class' => $this->get_field_id('mode') .'-child kcw-control-normal',
			'data-dep' => 'list'
		) );
		echo kcEssentials_widgets::form( $this, $options_select, $instance, array(
			'class' => $this->get_field_id('mode') .'-child kcw-control-normal',
			'data-dep' => 'select'
		) );
	}


	public static function kcml_fields( $widgets ) {
		$widgets['widget_kcw_menu'] = array(
			array(
				'id'    => 'title',
				'type'  => 'text',
				'label' => __('Title')
			),
			array(
				'id'    => 'before',
				'type'  => 'text',
				'label' => __('Before link text', 'kc-essentials')
			),
			array(
				'id'    => 'after',
				'type'  => 'text',
				'label' => __('After link text', 'kc-essentials')
			),
			array(
				'id'    => 'link_before',
				'type'  => 'text',
				'label' => __('Before link', 'kc-essentials')
			),
			array(
				'id'    => 'link_after',
				'type'  => 'text',
				'label' => __('After link', 'kc-essentials')
			)
		);

		return $widgets;
	}
}
add_filter( 'kcml_widget_fields', array('kc_widget_menu', 'kcml_fields') );

?>