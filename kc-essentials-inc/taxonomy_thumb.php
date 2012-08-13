<?php

/**
 * Term thumbnail module
 *
 * @package KC_Essentials
 * @since 0.2
 */

class kcEssentials_termthumb {
	private static $data = array( 'settings' => array() );

	public static function init() {
		$settings = kcEssentials::get_data( 'settings' );
		if (
			!isset( $settings['components']['taxonomy'] )
			|| !in_array( 'taxonomy_thumb', $settings['components']['taxonomy'] )
			|| !isset( $settings['taxonomy_thumb']['taxonomies'] )
			|| empty( $settings['taxonomy_thumb']['taxonomies'] )
		)
			return;

		self::$data['settings'] = $settings['taxonomy_thumb'];
		add_filter( 'kc_term_settings', array(__CLASS__, '_register_metadata') );

		if (
			!isset($settings['taxonomy_thumb']['misc'])
			|| !in_array('no_display_thumb', $settings['taxonomy_thumb']['misc'])
		) {
			add_action( 'admin_print_styles', array(__CLASS__, '_column_thumb_styles'), 99 );

			foreach ( $settings['taxonomy_thumb']['taxonomies'] as $tax )
				add_filter( "{$tax}_row_actions", array(__CLASS__, '_column_thumb_display'), 10, 2 );
		}
	}


	public static function _register_metadata( $settings ) {
		foreach ( self::$data['settings']['taxonomies'] as $tax ) {
			$settings[] = array(
				$tax => array(
					array(
						'id' => 'kcs-term-thumb',
						'title' => __('Term thumbnail', 'kc-essentials'),
						'fields' => array(
							array(
								'id'    => 'kcs-term-thumb',
								'title' => __('Thumbnail image', 'kc-essentials'),
								'type'  => 'file',
								'mode'  => 'single',
								'size'  => 'thumbnail'
							)
						)
					)
				)
			);
		}
		return $settings;
	}


	public static function _column_thumb_display( $actions, $term ) {
		echo self::get_thumb( $term->term_id, $size = 'thumbnail', $attr = array( 'class'=> 'term-icon') );

		return $actions;
	}


	public static function _column_thumb_styles() {
		$screen = get_current_screen();
		if ( $screen->base == 'edit-tags' && in_array($screen->taxonomy, self::$data['settings']['taxonomies'] ) ) { ?>
<style>.term-icon {float:left;width:40px;height:40px;overflow:hidden;margin-right:10px} .term-icon img {max-width:100%;height:auto}</style>
		<?php }
	}


	public static function get_thumb( $term_id = '', $size = '', $attr = array() ) {
		if ( !$term_id && ( is_tax() || is_category() || is_tag() ) )
			$term_id = get_queried_object_id();
		if ( !$term_id )
			return false;

		$thumb_id = get_metadata( 'term', $term_id, 'kcs-term-thumb', true );
		if ( !$thumb_id && !is_admin() && self::$data['settings']['default'] && self::$data['settings']['default'] )
			$thumb_id = self::$data['settings']['default'];
		if ( !$thumb_id )
			return false;

		if ( !$size ) {
			if ( isset(self::$data['settings']['size']) && self::$data['settings']['size'] )
				$size = self::$data['settings']['size'];
			else
				$size = 'thumbnail';
		}

		return wp_get_attachment_image( $thumb_id, $size, false, $attr );
	}
}


function kc_get_term_thumbnail( $term_id = '', $size = '', $attr = array() ) {
	return kcEssentials_termthumb::get_thumb( $term_id, $size, $attr );
}
