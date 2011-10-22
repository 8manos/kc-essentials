<?php

/*
Plugin name: KC Essentials
Plugin URI: http://kucrut.org/
Description: The essentials
Version: 0.1
Author: Dzikri Aziz
Author URI: http://kucrut.org/
License: GPL v2

*/


class kcEssentials {
	public static $paths;
	private static $settings;


	static function get_paths() {
		$paths = array();
		$inc_prefix = "/kc-essentials-inc";
		$fname = basename( __FILE__ );

		if ( file_exists(WPMU_PLUGIN_DIR . "/{$fname}") )
			$file = WPMU_PLUGIN_DIR . "/{$fname}";
		else
			$file = WP_PLUGIN_DIR . "/kc-essentials/{$fname}";

		$paths['file']		= $file;
		$paths['inc']			= dirname( $file ) . $inc_prefix;
		$url							= plugins_url( '', $file );
		$paths['url']			= $url;
		$paths['scripts']	= "{$url}{$inc_prefix}/scripts";
		$paths['styles']	= "{$url}{$inc_prefix}/styles";

		return $paths;
	}


	static function init() {
		self::$paths = self::get_paths();

		require_once self::$paths['inc'] . '/admin.php';
		self::$settings = kc_get_option( 'kc_essentials' );

		//add_action( 'admin_footer', array(__CLASS__, 'dev' ) );
	}


	static function dev() {
		echo '<pre>';

		print_r( self::$settings );

		echo '</pre>';
	}
}


function kc_essentials_init() {
	kcEssentials::init();
}
add_action( 'init', 'kc_essentials_init' );
?>
