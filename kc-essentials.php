<?php

/**
 * @package KC_Essentials
 * @version 0.1
 */

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
	public static $data = array(
		'version'	=> '0.1'
	);


	private static function _paths() {
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
		$paths = kcSettings::_paths( __FILE__ );
		if ( !is_array($paths) )
			return false;

		self::$data['paths'] = $paths;

		$settings = kc_get_option( 'kc_essentials' );
		self::$data['settings'] = $settings;

		require_once "${paths['inc']}/_helpers.php";

		# Components
		if ( isset($settings['general']['components']) && !empty($settings['general']['components']) ) {
			foreach ( $settings['general']['components'] as $c ) {
				if ( $c != 'widgets' )
					require_once "${paths['inc']}/{$c}.php";
			}
		}

		# Helpers
		if ( isset($settings['general']['helper']) && !empty($settings['general']['helper']) ) {
			foreach ( $settings['general']['helper'] as $h )
				require_once "${paths['inc']}/helper_{$h}.php";
		}

		# Dev
		//add_action( 'admin_footer', array(__CLASS__, 'dev' ) );
	}


	# Register to KC Settings
	public static function _activate() {
		if ( !class_exists('kcSettings') )
			wp_die( 'Please install and activate <a href="http://wordpress.org/extend/plugins/kc-settings/">KC Settings</a> before activating this plugin.<br /> <a href="'.wp_get_referer().'">&laquo; Go back</a> to plugins page.' );

		$kcs = get_option('kc_settings');
		$kcs['kids']['kc-essentials'] = array(
			'name'	=> 'KC Essentials',
			'file'	=> kc_plugin_file( __FILE__ )
		);
		update_option( 'kc_settings', $kcs );
	}


	# Unregister from KC Settings
	public static function _deactivate() {
		$kcs = get_option('kc_settings');
		unset( $kcs['kids']['kc-essentials'] );
		update_option( 'kc_settings', $kcs );
	}


	static function dev() {
		echo '<pre>';

		print_r( get_option( 'kc_essentials_we' ) );

		echo '</pre>';
	}
}


require_once dirname(__FILE__) . '/kc-essentials-inc/options.php';
add_action( 'init', array('kcEssentials', 'init'), 12 );


# A hack for symlinks
if ( !function_exists('kc_plugin_file') ) {
	function kc_plugin_file( $file ) {
		if ( !file_exists($file) )
			return $file;

		$file_info = pathinfo( $file );
		$parent = basename( $file_info['dirname'] );

		$file = ( $parent == $file_info['filename'] ) ? "{$parent}/{$file_info['basename']}" : $file_info['basename'];

		return $file;
	}
}

$plugin_file = kc_plugin_file( __FILE__ );
register_activation_hook( $plugin_file, array('kcEssentials', '_activate') );
register_deactivation_hook( $plugin_file, array('kcEssentials', '_deactivate') );

require_once dirname(__FILE__) . '/kc-essentials-inc/widgets.php';
add_action( 'widgets_init', array('kcEssentials_widgets', 'init') );

?>
