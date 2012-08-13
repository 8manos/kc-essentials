<?php

/**
 * Widget attributes module
 * @package KC_Essentials
 */


class kcEssentials_widget_attr {

	public static function init() {
		# Custom widget ID & classes
		# 0. Add fields on widget configuration form
		add_filter( 'in_widget_form', array(__CLASS__, '_fields'), 9, 3 );

		# 1. Update widget options
		add_filter( 'widget_update_callback', array(__CLASS__, '_save'), 10, 4 );

		# 2. Modify widget markup
		add_filter( 'dynamic_sidebar_params', array(__CLASS__, '_set') );
	}


	public static function _sns() {
		wp_enqueue_style( 'kc-essentials-widgets-admin' );
	}


	/**
	 * Add custom ID/classes fields to widget configuration form
	 *
	 */
	public static function _fields( $widget, $return, $instance ) {
		$data = kcEssentials::get_data('settings', 'widget_attr');
		$setting = kcEssentials_widgets::get_setting( $widget->id );
		$customs = array(
			'id'    => __('ID:', 'kc-essentials'),
			'class' => __('Classes:', 'kc-essentials'),
		); ?>
<details>
	<summary><?php _e('Widget attributes', 'kc-essentials') ?></summary>
	<ul class="kcw-control-block">
	<?php
		foreach ( $customs as $attr => $label ) {
			$f_current = isset($setting["custom_{$attr}"]) ? $setting["custom_{$attr}"] : '';
			$f_name    = "widget-{$widget->id_base}[{$widget->number}][custom_{$attr}]";
			$f_id      = "widget-{$widget->id_base}-{$widget->number}-custom_{$attr}"; ?>
		<li>
			<?php if ( !isset($data[$attr]) || empty($data[$attr]) ) { ?>
			<label for="<?php echo $f_id ?>"><?php echo $label; if ( $attr == 'class' ) echo ' <small class="impo" title="'.__('Separate with spaces', 'kc-essentials').'">(?)</small>' ?></label>
			<input type="text" name="<?php echo $f_name ?>" id="<?php echo $f_id ?>" value="<?php esc_attr_e($f_current) ?>" />
			<?php
				}
				else {
					$f_opt = array();
					foreach ( explode( ' ', $data[$attr]) as $o )
						$f_opt[] = array('value' => $o, 'label' => $o);

					if ( $attr == 'id' ) {
						$f_attr = array(
							'id'       => $f_id,
							'name'     => $f_name
						);
					}
					else {
						$f_attr = array(
							'id'       => $f_id,
							'name'     => "{$f_name}[]",
							'multiple' => 'multiple'
						);
						$f_current = explode( ' ', $f_current );
					}
			?>
			<label for="<?php echo $f_id ?>"><?php echo $label ?></label>
			<?php
				echo kcForm::field( array(
					'type'    => 'select',
					'attr'    => $f_attr,
					'options' => $f_opt,
					'current' => $f_current
				));
				}
			} ?>
		</li>
	</ul>
</details>
	<?php $return = null;
	}


	/**
	 * Sanitize and save widget's custom ID and/or classes
	 *
	 */
	public static function _save( $instance, $new, $old, $widget ) {
		$setting = kcEssentials_widgets::get_setting( $widget->id );
		foreach ( array('id', 'class') as $attr ) {
			# 0. Add/Update
			if ( isset($new["custom_{$attr}"]) && !empty($new["custom_{$attr}"]) ) {
				if ( $attr == 'id' )
					$setting["custom_{$attr}"] = trim( sanitize_html_class($new["custom_{$attr}"]) );
				else
					$setting["custom_{$attr}"] = trim( kc_essentials_sanitize_html_classes($new["custom_{$attr}"]) );
			}

			# 1. Delete
			else {
				unset( $setting["custom_{$attr}"] );
			}
		}

		kcEssentials_widgets::save_setting( $widget->id, $setting );
		return $instance;
	}


	/**
	 * Add custom ID/classes to widget's markup
	 *
	 */
	public static function _set( $params ) {
		$setting = kcEssentials_widgets::get_setting( $params[0]['widget_id'] );

		# 0. Custom ID
		if ( isset($setting['custom_id']) )
			$params[0]['before_widget'] = preg_replace( '/id=".*?"/', "id=\"{$setting['custom_id']}\"", $params[0]['before_widget'], 1 );

		# 1. Custom Classes
		if ( isset($setting['custom_class']) ) {
			$classes = trim( $setting['custom_class'] );
			if ( !empty($classes) )
				$params[0]['before_widget'] = preg_replace( '/class="/', "class=\"{$setting['custom_class']} ", $params[0]['before_widget'], 1 );
		}

		return $params;
	}
}
kcEssentials_widget_attr::init();
