<?php

/**
 * Media taxonomies module
 * @package KC_Essentials
 */


class kcEssentials_mediatax {
	private static $data;


	public static function init( $taxonomies ) {
		self::$data['taxonomies'] = array();
		$media_taxonomies = get_taxonomies_for_attachments();
		foreach ( $taxonomies as $tax ) {
			if ( !taxonomy_exists($tax) )
				continue;

			self::$data['taxonomies'][$tax] = get_taxonomy( $tax );

			# Register the taxonomy for attachment post type
			if ( !in_array($tax, $media_taxonomies) )
				register_taxonomy_for_object_type( $tax, 'attachment' );
		}
	}
}

if ( $taxonomies = kcEssentials::get_data('settings', 'taxonomy_media', 'taxonomies') )
	kcEssentials_mediatax::init( $taxonomies );
