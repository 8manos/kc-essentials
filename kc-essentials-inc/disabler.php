<?php

/**
 * Disabler module
 *
 * @package KC_Essentials
 * @since 0.2.1
 */

class kcEssentials_disabler {
	private static $data = array();


	public static function init() {
		$items = kcEssentials::get_data( 'settings', 'components', 'disabler' );
		if ( empty($items) )
			return;

		self::$data['items'] = $items;
		foreach ( $items as $item )
			call_user_func( array(__CLASS__, $item) );
	}


	private static function ms_hide_wc_screen() {
		add_action( 'load-index.php', array(__CLASS__, 'ms_hide_wc_screen_action') );
	}


	public static function ms_hide_wc_screen_action() {
		if ( !is_multisite() )
			return;

		if ( 2 === (int) get_user_meta( get_current_user_id(), 'show_welcome_panel', true ) )
			update_user_meta( get_current_user_id(), 'show_welcome_panel', 0 );
	}
}

kcEssentials_disabler::init();

?>
