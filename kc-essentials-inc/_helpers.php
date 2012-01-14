<?php

/**
 * @package KC_Essentials
 * @version 0.1
 */


/**
 * Get all image sizes
 *
 * @return array Image sizes
 */
function kc_essentials_get_image_sizes( $pair = false ) {
	$sizes = array();
	foreach ( array('thumbnail', 'medium', 'large') as $size ) {
		$sizes[$size] = array(
			'width'		=> get_option( "{$size}_size_w" ),
			'height'	=> get_option( "{$size}_size_h" )
		);
	}

	global $_wp_additional_image_sizes;
	if ( is_array($_wp_additional_image_sizes) )
		$sizes = array_merge( $sizes, $_wp_additional_image_sizes );

	if ( !$pair )
		return $sizes;

	$pairs = array();
	foreach ( $sizes as $name => $dim )
		$pairs[$name] = $dim['width'];
	$pairs = array_unique($pairs);
	asort( $pairs, SORT_NUMERIC );
	return $pairs;
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


function kc_essentials_sanitize_numbers( $value, $glue = ',' ) {
	$_sizes = explode( $glue, $value );
	foreach ( $_sizes as $idx => $_s ) {
		$_w = absint( $_s );
		if ( !$_w )
			unset( $_sizes[$idx] );
		else
			$_sizes[$idx] = $_w;
	}

	return implode( $glue, $_sizes );
}


?>
