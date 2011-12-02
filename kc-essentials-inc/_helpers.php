<?php

/**
 * Get additional image sizes registered with add_image_size()
 *
 * @return array Addition image sizes
 */
function kc_essentials_get_aditional_image_sizes() {
	$image_sizes = array();
	global $_wp_additional_image_sizes;
	if ( isset($_wp_additional_image_sizes) && count($_wp_additional_image_sizes) )
		$image_sizes = apply_filters( 'intermediate_image_sizes', $_wp_additional_image_sizes );

	return $image_sizes;
}


/**
 * Remove unwanted characters from custom classes
 *
 * @param string $input Classes string to process
 * @return string Sanitized html classes
 */
function kc_essentials_sanitize_html_classes( $input ) {
	if ( !is_array($input) ) {
		if ( strpos($input, ' ') )
			$input = explode( ' ', $input );
		else
			$input = array( $input );
	}

	$output = array();
	foreach ( $input as $c )
		$output[] = sanitize_html_class( $c );

	return join( ' ', $output );
}


if ( !class_exists('kcForm') ) {
	/*
	 * Form elements helper
	 */
	class kcForm {

		public static function field( $args = array() ) {
			$defaults = array(
				'type'    => 'text',
				'attr'    => '',
				'current' => ''
			);
			$args = wp_parse_args( $args, $defaults );

			if ( in_array($args['type'], array('', 'text', 'date', 'color')) ) {
				$type = 'input';
			}
			else {
				$type = $args['type'];
			}


			if ( !method_exists(__CLASS__, $type) )
				return false;

			if ( in_array($type, array('select', 'radio', 'checkbox'))
						&& (!isset($args['options']) || !is_array($args['options'])) )
				return false;

			return call_user_func( array(__CLASS__, $type), $args );
		}


		public static function input( $args ) {
			if ( !isset($args['type']) || in_array($args['type'], array('', 'input') ) )
				$args['type'] = 'text';

			$output  = "<input type='{$args['type']}'";
			$output .= self::_build_attr( $args['attr'] );
			$output .= "value='".esc_attr($args['current'])."' ";
			$output .= " />";

			return $output;
		}


		public static function textarea( $args ) {
			$output  = "<textarea";
			$output .= self::_build_attr( $args['attr'] );
			$output .= ">";
			$output .= esc_textarea( $args['current'] );
			$output .= "</textarea>";

			return $output;
		}


		public static function radio( $args ) {
			$args['type'] = 'radio';
			return self::checkbox( $args );
		}


		public static function checkbox( $args ) {
			if ( !isset($args['type']) || !$args['type'] )
				$args['type'] = 'checkbox';

			if ( !is_array($args['current']) )
				$args['current'] = array($args['current']);
			if ( !isset($args['check_sep']) || !is_array($args['check_sep']) || count($args['check_sep']) < 2 )
				$args['check_sep'] = array('', '<br />');
			$attr = self::_build_attr( $args['attr'] );

			$output  = '';
			foreach ( $args['options'] as $o ) {
				$output .= "{$args['check_sep'][0]}<label class='kcs-check kcs-{$args['type']}'><input type='{$args['type']}' value='{$o['value']}'{$attr}";
				if ( in_array($o['value'], $args['current']) || ( isset($args['current'][$o['value']]) && $args['current'][$o['value']]) )
					$output .= " checked='true'";
				$output .= " /> {$o['label']}</label>{$args['check_sep'][1]}\n";
			}

			return $output;
		}


		public static function select( $args ) {
			if ( !isset($args['none']) || $args['none'] !== false ) {
				$args['none'] = array(
					'value'   => '',
					'label'   => '&mdash;&nbsp;'.__('Select', 'kc-settings').'&nbsp;&mdash;'
				);
				$args['options'] = array_merge( array($args['none']), $args['options'] );
			}

			if ( !is_array($args['current']) )
				$args['current'] = array($args['current']);

			$output  = "<select";
			$output .= self::_build_attr( $args['attr'] );
			$output .= ">\n";
			foreach ( $args['options'] as $o ) {
				$output .= "\t<option value='".esc_attr($o['value'])."'";
				if ( in_array($o['value'], $args['current']) )
					$output .= " selected='true'\n";
				$output .= ">{$o['label']}</option>\n";
			}
			$output .= "</select>";

			return $output;
		}


		private static function _build_attr( $attr ) {
			if ( !is_array($attr) || empty($attr) )
				return;

			foreach ( array('type', 'value', 'checked', 'selected') as $x )
				unset( $attr[$x] );

			$output = '';
			foreach ( $attr as $k => $v )
				$output .= " {$k}='".esc_attr($v)."'";

			return $output;
		}

	}
}


?>
