<?php

class kcEssentials_widgets {

  public function init() {
    $options = get_option( 'kc_essentials_settings' );
    if ( !$options ) {
      $options = array(
        'general'		=> array(
          'components'	=> array( 'custom_widget_id_class', 'widgets', 'insert_custom_size' ),
        ),
				'widgets'		=> array(
					'widgets'		> array( 'post' )
				)
      );

      $options = apply_filters( 'kc_essentials_setting', $options );
    }

    if ( !isset($options['general']['components'])
					|| !in_array( 'widgets', $options['general']['components'] )
					|| !isset($options['widgets']['widgets']) )
      return false;

    foreach ( $options['widgets']['widgets'] as $widget ) {
			$file = dirname(__FILE__) . "/widget-{$widget}.php";
			if ( !file_exists($file) || !is_readable($file) )
				continue;

      require_once $file;
      register_widget( "kc_widget_{$widget}" );
    }

    add_action( 'load-widgets.php', array(__CLASS__, '_sns') );
  }


  public static function _sns() {
    wp_enqueue_script( 'kc-widgets-admin', kcEssentials::$data['paths']['scripts'].'/widgets.js', array('jquery'), kcEssentials::$data['version'], true );
    wp_enqueue_style( 'kc-widgets-admin', kcEssentials::$data['paths']['styles'].'/widgets.css', false, kcEssentials::$data['version'] );
  }

}

?>
