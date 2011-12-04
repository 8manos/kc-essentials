<?php

/**
 * @package KC_Essentials
 * @version 0.1
 *
 * @subpackage Custom_Widgets
 *
 */


class kcEssentials_widgets {

	public function init() {
		$options = get_option( 'kc_essentials_settings' );
		if ( !$options ) {
			$options = array(
				'general'		=> array(
					'components'	=> array( 'custom_widget_id_class', 'widgets', 'insert_custom_size' ),
				),
				'widgets'		=> array(
					'widgets'		> array( 'post', 'menu' )
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


	/**
	 * Scripts n styles for the widget configuration forms
	 */
	public static function _sns() {
		wp_enqueue_script( 'kc-widgets-admin', kcEssentials::$data['paths']['scripts'].'/widgets.js', array('jquery'), kcEssentials::$data['version'], true );
		wp_enqueue_style( 'kc-widgets-admin', kcEssentials::$data['paths']['styles'].'/widgets.css', false, kcEssentials::$data['version'] );
	}


	/**
	 * Widget configuration form
	 *
	 * @param object $widget Widget object
	 * @param array $options Widget options
	 * @param array $config Current widget settings
	 *
	 * @return string Configuration form
	 */
  public static function form( $widget, $options, $config, $list_class = 'kcw-control-normal' ) {
		$form = "<ul class='${list_class}'>\n";
		foreach ( $options as $id => $args ) {
			$f_id = $widget->get_field_id($id);
			$f_name = $widget->get_field_name($id);

			$label = "<label for='${f_id}'>${args['label']}</label>";
			if ( isset($args['heading']) ) {
				$label = "<h5>${label}</h5>";
				unset( $args['heading'] );
			}
			unset( $args['label'] );

			$form .= "\t<li>\n";
			$form .= "\t\t${label}\n";

			if ( !isset($args['current']) )
				$args['current'] = $config[$id];
			$args['attr'] = array(
				'id'		=> $f_id,
				'name'	=> $f_name
			);
			$form .= "\t\t".kcForm::field( $args )."\n";
			$form .= "\t</li>\n";
		}
		$form .= "</ul>\n";

		return $form;
  }

}

?>
