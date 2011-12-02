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


?>
