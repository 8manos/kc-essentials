<?php

/**
 * Widget logic module
 * @package KC_Essentials
 */


class kcEssentials_widget_logic {
	private static $data = array();

	public static function init() {
		# Custom widget ID & classes
		# 0. Add fields on widget configuration form
		add_filter( 'in_widget_form', array(__CLASS__, '_fields'), 10, 3 );

		# 1. Update widget options
		add_filter( 'widget_update_callback', array(__CLASS__, '_save'), 10, 4 );

		# 2. Remove widgets from sidebars as needed
		add_filter( 'sidebars_widgets', array(__CLASS__, '_filter_widgets') );
	}



	/**
	 * Add logic fields to widget configuration form
	 *
	 */
	public static function _fields( $widget, $return, $instance ) {
		$f_id			= $widget->get_field_id('kc-logic');
		$f_name		= $widget->get_field_name('kc-logic');
		$setting	= kcEssentials_widgets::get_setting( $widget->id ); ?>
<div class="kcwe">
	<p>
		<label for="<?php echo $widget->get_field_id('kc-logic-enable') ?>"><?php _e('Logic status:', 'kc-essentials') ?></label>
		<?php echo kcForm::field( array(
			'type'    => 'select',
			'attr'    => array(
				'id'         => $widget->get_field_id('kc-logic-enable'),
				'name'       => $widget->get_field_name('kc-logic-enable'),
				'class'      => 'hasdep kc-logic-enable',
				'data-child' => "#{$f_id}-logics, #{$f_id}-modes"
			),
			'options' => array(
				'0' => __('Disable', 'kc-essentials'),
				'1' => __('Enable', 'kc-essentials')
			),
			'none'    => false,
			'current' => ( isset($setting['kc-logic-enable']) && $setting['kc-logic-enable'] ) ? true : false
		) );
		?>
	</p>
	<p id="<?php echo $f_id ?>-modes" data-dep="1">
		<label for="<?php echo $widget->get_field_id('kc-logic-mode') ?>"><?php _e('Logic mode:', 'kc-essentials') ?></label>
		<?php echo kcForm::field( array(
			'type'    => 'select',
			'attr'    => array(
				'id'   => $widget->get_field_id('kc-logic-mode'),
				'name' => $widget->get_field_name('kc-logic-mode')
			),
			'options' => array(
				'show' => __('ONLY show in&hellip;', 'kc-essentials'),
				'hide' => __('DO NOT show in&hellip;', 'kc-essentials')
			),
			'none'    => false,
			'current' => isset($setting['kc-logic-mode']) ? $setting['kc-logic-mode'] : 'show'
		) );
		?>
	</p>
	<p id="<?php echo $f_id ?>-logics" data-dep="1">
		<label for="<?php echo $f_id ?>"><?php _e('Logic locations:', 'kc-essentials') ?></label>
		<?php echo kcForm::field( array(
			'type'    => 'select',
			'attr'    => array(
				'id'         => $f_id,
				'name'       => "{$f_name}[]",
				'class'      => 'hasdep',
				'multiple'   => true,
				'data-child' => ".{$f_id}-args"
			),
			'options' => array(
				'is_home'              => __('Homepage', 'kc-essentials'),
				'is_front_page'        => __('Static front page', 'kc-essentials'),
				'is_singular'          => __('Singular', 'kc-essentials'),
				'is_page'              => __('Page', 'kc-essentials'),
				'is_page_template'     => __('Custom page template', 'kc-essentials'),
				'is_single'            => __('Single post', 'kc-essentials'),
				'is_attachment'        => __('Attachment', 'kc-essentials'),
				'is_archive'           => __('Archive', 'kc-essentials'),
				'is_post_type_archive' => __('Post type archive', 'kc-essentials'),
				'is_category'          => __('Category', 'kc-essentials'),
				'is_tag'               => __('Tag', 'kc-essentials'),
				'is_tax'               => __('Taxonomy term', 'kc-essentials'),
				'is_author'            => __('Author', 'kc-essentials'),
				'is_404'               => __('404', 'kc-essentials'),
				'is_search'            => __('Search page', 'kc-essentials'),
				'is_paged'             => __('Paged archive', 'kc-essentials'),
				'is_year'              => __('Year archive', 'kc-essentials'),
				'is_month'             => __('Month archive', 'kc-essentials'),
				'is_date'              => __('Date archive', 'kc-essentials'),
				'is_day'               => __('Day archive', 'kc-essentials'),
				'is_new_day'           => __('New day', 'kc-essentials'),
				'is_time'              => __('Time archive', 'kc-essentials'),
				'is_preview'           => __('Preview page', 'kc-essentials'),
				'is_user_logged_in'    => __('Logged in user', 'kc-essentials'),
			),
			'none'    => false,
			'current' => isset($setting['kc-logic']) ? $setting['kc-logic'] : array()
		) );
		?>
	</p>
	<?php
		$args_id   = $widget->get_field_id('kc-logic-args');
		$args_name = $widget->get_field_name('kc-logic-args');
		foreach ( array(
			'is_page', 'is_single', 'is_singular', 'is_attachment'
			,'is_category', 'is_tag', 'is_tax', 'is_author', 'is_page_template'
			,'is_post_type_archive'
		) as $cond ) {
			$args_val = isset($setting['kc-logic-args'][$cond]) ? $setting['kc-logic-args'][$cond] : '';
	?>
	<p class="<?php echo $f_id ?>-args" data-dep="<?php echo $cond ?>">
		<label for="<?php echo "{$args_id}-{$cond}" ?>"><?php printf( __('%s argument:', 'kc-essentials'), "<code>{$cond}</code>" ) ?></label>
		<input id="<?php echo "{$args_id}-{$cond}" ?>" name="<?php echo "{$args_name}[{$cond}]" ?>" class="widefat" type="text" value="<?php esc_attr_e($args_val) ?>" />
	</p>
	<?php } ?>
</div>
	<?php
		$return = null;
	}


	public static function _save( $instance, $new, $old, $widget ) {
		$setting = kcEssentials_widgets::get_setting( $widget->id );
		$setting['kc-logic-enable'] = ( isset($new['kc-logic-enable']) && $new['kc-logic-enable'] ) ? true : false;
		foreach ( array('kc-logic', 'kc-logic-mode', 'kc-logic-args') as $field )
			if ( isset($new[$field]) )
				$setting[$field] = $new[$field];

		kcEssentials_widgets::save_setting( $widget->id, $setting );

		return $instance;
	}


	public static function _filter_widgets( $sidebars_widgets ) {
		if ( is_admin() )
			return $sidebars_widgets;

		$settings = get_option( 'kc_essentials_we' );
		if ( !$settings )
			return $sidebars_widgets;

		foreach ( $sidebars_widgets as $sidebar => $widgets ) {
			if ( $sidebar == 'wp_inactive_widgets' )
				continue;

			foreach ( $widgets as $idx => $widget ) {
				if (
					!isset($settings[$widget]['kc-logic-enable'])
					|| !$settings[$widget]['kc-logic-enable']
					|| !isset($settings[$widget]['kc-logic'])
					|| !is_array($settings[$widget]['kc-logic'])
					|| empty($settings[$widget]['kc-logic'])
				)
					continue;

				$show = ( $settings[$widget]['kc-logic-mode'] && $settings[$widget]['kc-logic-mode'] == 'show' ) ? true : false;
				foreach ( $settings[$widget]['kc-logic'] as $func ) {
					if ( isset( $settings[$widget]['kc-logic-args'][$func] ) && !empty($settings[$widget]['kc-logic-args'][$func]) ) {
						$args =
							strpos($settings[$widget]['kc-logic-args'][$func], ',') === true
							? explode(',', $settings[$widget]['kc-logic-args'][$func])
							: $settings[$widget]['kc-logic-args'][$func];
					$res = call_user_func( $func, $args );
					}
					else {
						$res = call_user_func( $func );
					}

					if ( $res === $show )
						continue 2;
				}

				unset( $widgets[$idx] );
			}
			$sidebars_widgets[$sidebar] = $widgets;
		}

		return $sidebars_widgets;
	}
}

kcEssentials_widget_logic::init();

?>