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


	private static function _options() {
		$options = array(
			'general'		=> array(
				'components'	=> array( 'custom_widget_id_class', 'widgets', 'insert_custom_size' ),
			),
			'widgets'		=> array(
				'widgets'		> array( 'post' )
			)
		);

		return apply_filters( 'kc_essentials_setting', $options );
	}


	static function init() {
		$settings = ( class_exists('kcSettings') ) ? kc_get_option( 'kc_essentials' ) : self::_options();
		if ( empty($settings) || !isset($settings['general']['components']) || empty($settings['general']['components']) )
			return false;

		self::$data['settings'] = $settings;
		self::$data['paths'] = self::_paths();
		require_once self::$data['paths']['inc'] . '/_helpers.php';

		foreach ( $settings['general']['components'] as $c ) {
			if ( $c == 'widgets' )
				continue;

			require_once self::$data['paths']['inc'] . "/{$c}.php";
			add_action( 'init', array("kcEssentials_{$c}", 'init'), 99 );
		}

		# Dev
		//add_action( 'admin_footer', array(__CLASS__, 'dev' ) );
	}


	static function dev() {
		echo '<pre>';

		print_r( self::$settings );

		echo '</pre>';
	}
}

require_once dirname(__FILE__) . '/kc-essentials-inc/options.php';
add_action( 'init', array('kcEssentials', 'init'), 12 );


require_once dirname(__FILE__) . '/kc-essentials-inc/widgets.php';
add_action( 'widgets_init', array('kcEssentials_widgets', 'init') );

?>
