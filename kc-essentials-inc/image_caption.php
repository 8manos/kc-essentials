<?php

/**
 * Image caption quicktags module
 * @package KC_Essentials
 */


class kcEssentials_image_caption_quicktags {
	public static $done = false;


	public static function init() {
		require_once kcSettings::get_data('paths', 'inc') . '/attachment.php';

		add_filter( 'attachment_fields_to_edit', array(__CLASS__, 'fields'), 10, 2 );
		add_action( 'admin_head', array(__CLASS__, 'sns') );
	}


	public static function fields( $fields, $post ) {
		$fields['post_content']['input'] = 'html';
		$values = get_object_vars( $post );

		foreach ( array('post_excerpt', 'post_content') as $field_id )
			$fields[$field_id]['html'] = kcSettings_attachment::field_editor( $field_id, $post->ID, $values[$field_id] );

		return $fields;
	}


	public static function sns() {
		if ( !kcSettings_attachment::$sns_done )
			kcSettings_attachment::sns();
	}
}
kcEssentials_image_caption_quicktags::init();
