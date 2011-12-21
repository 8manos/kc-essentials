<?php

/**
 * @package KC_Essentials
 * @version 0.1
 */


class kcEssentials_history_js {
	public static function init() {
		wp_register_script( 'jquery-scrollto', kcEssentials::$data['paths']['scripts'].'/jquery.scrollto.min.js', false, '1.0.1', true );
		wp_register_script( 'jquery-history', kcEssentials::$data['paths']['scripts'].'/jquery.history.js', false, '1.7.1', true );
		wp_enqueue_script( 'kc-ajaxify', kcEssentials::$data['paths']['scripts'].'/ajaxify.js', array('jquery', 'jquery-scrollto', 'jquery-history'), '0.1', true );

		$vars = wp_parse_args( kcs_array_remove_empty(kc_get_option('kc_essentials', 'history_js')), kc_get_default('plugin', 'kc_essentials', 'history_js') );

		$must = array(
			'el_excludes'		=> array('#comment-popup-link', '.no-ajaxy'),
			'url_excludes'	=> array('/wp-admin/', '/feed/')
		);
		foreach ( $must as $key => $values ) {
			$glue = ( $key == 'class_active_menu' ) ? ' ' : ',';
			if ( !isset($vars[$key]) )
				$vars[$key] = array();
			else
				$vars[$key] = explode( $glue, $vars[$key] );

			$vars[$key] = implode( $glue, array_unique( array_merge($vars[$key], $values) ) );
		}

		wp_localize_script( 'kc-ajaxify', 'kcAjaxify', $vars );
	}
}

add_action( 'wp_head', array('kcEssentials_history_js', 'init') );