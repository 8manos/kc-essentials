<?php

/**
 * Widgets / widgets area module
 *
 * @package KC_Essentials
 */


class kcEssentials_widgets {
	public static $sidebars;

	public function init() {
		$settings = kcEssentials::get_data('settings');
		if (
			!$settings
			|| !isset($settings['components']['widget'])
			|| empty($settings['components']['widget'])
		)
			return false;

		# Scripts & styles for widget config forms
		add_action( 'load-widgets.php', array(__CLASS__, '_actions') );

		# Register sidebars
		if (
			in_array('widget_areas', $settings['components']['widget'])
			&& isset($settings['widget_areas'])
			&& !empty($settings['widget_areas'])
		) {
			self::$sidebars = $settings['widget_areas'];
			add_action( 'widgets_init', array(__CLASS__, 'register_sidebars'), 99 );
		}


		# Register widgets
		if (
			in_array('widget_widgets', $settings['components']['widget'])
			&& isset($settings['widget_widgets']['widgets'])
			&& !empty($settings['widget_widgets']['widgets'])
		) {
			$dir = dirname( __FILE__ );
			foreach ( $settings['widget_widgets']['widgets'] as $widget ) {
				$file = "{$dir}/widgets/{$widget}.php";
				if ( !file_exists($file) || !is_readable($file) )
					continue;

				require_once $file;
				register_widget( "kc_widget_{$widget}" );
			}
		}
	}


	/**
	 * Register custom sidebars
	 *
	 * This has a low priority to make sidebars from the active
	 * theme got registered first
	 */
	public static function register_sidebars() {
		foreach ( self::$sidebars as $sb )
			register_sidebar( $sb );
	}


	/**
	 * Actions for the widgets admin page
	 */
	public static function _actions() {
		# Scripts n styles for the widget configuration forms
		wp_enqueue_style( 'kc-essentials-widgets-admin' );

		# Add the post finder box
		add_action( 'admin_footer', 'find_posts_div', 99 );
		add_action( 'admin_enqueue_scripts', array(__CLASS__, 'scripts_enq'), 99 );
		add_action( 'admin_print_footer_scripts', array('kcSettings', '_sns_vars'), 9 );
		add_action( 'admin_print_footer_scripts', array(__CLASS__, 'scripts_print'), 99 );
	}


	public static function scripts_enq() {
		wp_enqueue_script( 'kc-settings-base' );
		wp_enqueue_script( 'media' );
		wp_enqueue_script( 'wp-ajax-response' );
	}


	public static function scripts_print() { ?>
<script>
(function($) {
	// Form deps
	var $widgets = $('.widgets-sortables');
	$('.hasdep', $widgets).kcFormDep();
	$widgets.ajaxSuccess(function() { $('.hasdep', this).kcFormDep(); });
	// Tax/Meta query row cloner
	$.kcRowCloner();
	// Post IDs finder
	$.kcPostFinder();
	// Chosen
	$('.kcwe select.chosen').kcChosen();
})(jQuery);
</script>
	<?php }


	/**
	 * Widget configuration form
	 *
	 * @param object $widget Widget object
	 * @param array $options Widget options
	 * @param array $config Current widget settings
	 *
	 * @return string Configuration form
	 */
  public static function form( $widget, $options, $config, $attr = array() ) {
		$form = "<ul ".kcForm::_build_attr( wp_parse_args( $attr, array( 'class' => 'kcw-control-normal' ) ) ).">\n";
		foreach ( $options as $id => $args ) {
			$f_id = $widget->get_field_id( $id );
			$f_name = $widget->get_field_name( $id );
			if ( isset($args['name_sfx']) ) {
				$f_name .= $args['name_sfx'];
				unset( $args['name_sfx'] );
			}

			$form .= "\t<li";
			if ( isset($args['wrap_attr']) && !empty($args['wrap_attr']) )
				$form .= is_array( $args['wrap_attr'] ) ? kcForm::_build_attr( $args['wrap_attr'], "'" ) : " {$args['wrap_attr']}";
			$form .= ">\n";

			if ( isset($args['label']) && !empty($args['label']) ) {
				$label = "<label for='{$f_id}'>{$args['label']}</label>";
				if ( isset($args['heading']) ) {
					$label = "<h5>{$label}</h5>";
					unset( $args['heading'] );
				}
				unset( $args['label'] );

				$form .= "\t\t{$label}\n";
			}

			if ( !isset($args['current']) )
				$args['current'] = isset($config[$id]) ? $config[$id] : '';
			if ( !isset($args['attr']) )
				$args['attr'] = array();
			$args['attr']['id']   = $f_id;
			$args['attr']['name'] = $f_name;

			$form .= "\t\t".kcForm::field( $args )."\n";
			$form .= "\t</li>\n";
		}
		$form .= "</ul>\n";

		return $form;
  }


	/**
	 * Get widget settings
	 */
	public static function get_setting( $widget_id ) {
		$setting = get_option( 'kc_essentials_we' );

		if ( !$setting || !isset($setting[$widget_id]) )
			return array();
		else
			return $setting[$widget_id];
	}


	/**
	 * Save widget settings
	 */
	public static function save_setting( $widget_id, $value ) {
		$settings = get_option( 'kc_essentials_we' );
		if ( !$settings )
			$settings = array();

		if ( empty($value) )
			unset( $settings[$widget_id] );
		else
			$settings[$widget_id] = $value;

		update_option( 'kc_essentials_we', $settings );

	}
}
