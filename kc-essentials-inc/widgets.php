<?php

class kcEssentials_widgets {

  public function init() {
    $options = get_option( 'kc_essentials_settings' );
    if ( !$options ) {
      $options = array(
        'general'	=> array(
          'components'	=> array( 'uniquetax', 'custom_widget_id_class', 'widgets' ),
          'uniquetax'		=> array(),
          'widgets'			=> array( 'post' )
        )
      );

      $options = apply_filters( 'kc_essentials_setting', $options );
    }

    if ( !isset($options['general']['widgets']) || empty($options['general']['widgets']) )
      return false;

    foreach ( $options['general']['widgets'] as $widget ) {
      require_once dirname(__FILE__) . "/widget-{$widget}.php";
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
