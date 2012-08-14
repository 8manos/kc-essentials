<?php

/**
 * Form catcher
 *
 * @package KC_Essentials
 * @since 0.3
 */

class kcEssentials_Catcher {
	private static $data;


	public static function _catch() {
		if (
			!isset( $_POST['kcform'] )
			|| !isset( $_POST['kcform']['action'] )
			|| !method_exists( __CLASS__, $_POST['kcform']['action'] )
		)
			return;

		self::$data = $_POST['kcform'];
		call_user_func( array(__CLASS__, $_POST['kcform']['action']) );
	}


	private static function menu() {
		if ( isset( self::$data['menu-id'] ) && self::$data['menu-id'] ) {
			$m = wp_setup_nav_menu_item( get_post( self::$data['menu-id'] ) );
			wp_redirect( $m->url );
			exit;
		}
	}
}
add_action( 'init', array('kcEssentials_Catcher', '_catch') );
