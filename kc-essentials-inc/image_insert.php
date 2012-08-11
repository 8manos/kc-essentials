<?php

/**
 * Custom image size insert module
 * Updated for WP 3.4+
 * @package KC_Essentials
 * @link http://www.wpmayor.com/wordpress-hacks/how-to-add-custom-image-sizes-to-wordpress-uploader/
 */
function kc_essentials_insert_custom_image_sizes( $sizes ) {
	$custom_sizes = kcSettings_options::$image_sizes_custom;
	if ( empty($custom_sizes) )
		return $sizes;

	foreach ( $custom_sizes as $id => $name ) {
		if ( !isset($sizes[$id]) )
			$sizes[$id] = ucfirst( str_replace( '-', ' ', $id ) );
	}

	return $sizes;
}
add_filter( 'image_size_names_choose', 'kc_essentials_insert_custom_image_sizes' );

?>
