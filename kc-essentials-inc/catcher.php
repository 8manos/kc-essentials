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
		if (
			isset( self::$data['url'] )
			&& !empty( self::$data['url'] )
			&& self::$data['url'] != self::$data['current']
		) {
			$url = ( get_option('permalink_structure') ) ? trailingslashit( self::$data['url'] ) : self::$data['url'];
			wp_redirect( $url );
			exit;
		}
	}
}
add_action( 'init', array('kcEssentials_Catcher', '_catch') );

?>
