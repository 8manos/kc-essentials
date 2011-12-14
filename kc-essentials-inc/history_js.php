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

		$excludes = array('#comment-popup-link', '.no-ajaxy');
		if ( isset($vars['el_excludes']) )
			$excludes = array_merge( $excludes, explode(',', $vars['el_excludes']) );
		$vars['el_excludes'] = implode(',', $excludes);

		$currents = array('current-menu-item');
		if ( isset($vars['class_active']) )
			$currents = array_merge( $currents, explode(',', $vars['class_active']) );
		$vars['class_active'] = implode(',', $currents);

		$vars['el_active_wp'] = '.current-menu-item, .current_page_item';
		$vars['class_active_wp'] = 'current-menu-item current_page_item';

		wp_localize_script( 'kc-ajaxify', 'kcAjaxify', $vars );
	}
}

add_action( 'wp_head', array('kcEssentials_history_js', 'init') );