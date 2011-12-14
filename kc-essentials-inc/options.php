<?php

/**
 * @package KC_Essentials
 * @version 0.1
 */


add_filter( 'kc_plugin_settings', 'kc_essentials_options' );
function kc_essentials_options( $settings ) {
	$options = array(
		'general'	=> array(
			'id'			=> 'general',
			'title'		=> __('General Settings', 'kc-essentials'),
			'fields'	=> array(
				'components' => array(
					'id'			=> 'components',
					'title'		=> __('Components', 'kc-essentials'),
					'type'		=> 'checkbox',
					'options'	=> array(
						'uniquetax'								=> __('Unique taxonomies', 'kc-essentials'),
						'mediatax'								=> __('Media taxonomies', 'kc-essentials'),
						'widget_custom_id_class'	=> __('Custom widget ID &amp; classes', 'kc-essentials'),
						'widget_logic'						=> __('Widget logic', 'kc-essentials'),
						'widgets'									=> __('Additional widgets', 'kc-essentials'),
						'adaptive_images'					=> __('Adaptive Images', 'kc-essentials'),
						'insert_custom_size'			=> __('Insert images with custom sizes', 'kc-essentials'),
						'cc_archive_menu'					=> __('Custom post type archive menu', 'kc-essentials'),
						'history_js'							=> sprintf( __('Ajaxify the whole site using <a href="%s">history.js</a> and jQuery', 'kc-essentials'), 'https://github.com/balupton/History.js/' )
					)
				),
				'helper' => array(
					'id'			=> 'helper',
					'title'		=> __('Helper functions', 'kc-essentials'),
					'type'		=> 'checkbox',
					'options'	=> array(
						'adjacent_post'	=> __('Get adjacent posts', 'kc-essentials') . ' (<code>KC_Adjacent_Post</code>)',
					)
				)
			)
		)
	);

	# Wp 3.3+
	if ( version_compare(get_bloginfo('version'), '3.2.9999', '>=') ) {
		$options['general']['fields']['components']['options']['help_popup'] = __('Contextual help popup', 'kc-essentials');
	}


	# Unique taxonomies
	$taxonomies = get_taxonomies( array('show_ui' => true), 'objects' );
	if ( !empty($taxonomies) ) {
		$tax_media = $tax_unique = array();
		foreach ( $taxonomies as $tax_name => $tax_object ) {
			$tax_media[$tax_name] = "{$tax_object->label} (<code>{$tax_name}</code>)";
			if ( $tax_object->hierarchical )
				$tax_unique[$tax_name] = "{$tax_object->label} (<code>{$tax_name}</code>)";
		}

		asort( $tax_media );
		asort( $tax_unique );

		$options[] = array(
			'id'			=> 'uniquetax',
			'title'		=> __('Unique taxonomies', 'kc-essentials'),
			'fields'	=> array(
				array(
					'id'			=> 'taxonomies',
					'title'		=> __('Taxonomies', 'kc-essentials'),
					'type'		=> 'checkbox',
					'options'	=> $tax_unique
				)
			)
		);

		$options[] = array(
			'id'			=> 'mediatax',
			'title'		=> __('Media taxonomies', 'kc-essentials'),
			'fields'	=> array(
				array(
					'id'			=> 'taxonomies',
					'title'		=> __('Taxonomies', 'kc-essentials'),
					'type'		=> 'checkbox',
					'options'	=> $tax_media
				)
			)
		);
	}

	# Widget enhancements
	$options[] = array(
		'id'			=> 'widget_custom_id_class',
		'title'		=> __('Custom widget ID &amp; classes', 'kc-essentials'),
		'fields'	=> array(
			array(
				'id'			=> 'id',
				'title'		=> __('Custom widget IDs', 'kc-essentials'),
				'type'		=> 'text',
				'attr'		=> array('style' => 'width:98%' ),
				'desc'		=> __('Predefined widget IDs (optional, separate with spaces)', 'kc-essentials'),
			),
			array(
				'id'			=> 'class',
				'title'		=> __('Custom widget classes', 'kc-essentials'),
				'type'		=> 'text',
				'attr'		=> array('style' => 'width:98%' ),
				'desc'		=> __('Predefined widget classes (optional, separate with spaces)', 'kc-essentials')
			)
		)
	);

	# Additional widgets
	$options[] = array(
		'id'			=> 'widgets',
		'title'		=> __('Additional widgets', 'kc-essentials'),
		'fields'	=> array(
			array(
				'id'			=> 'widgets',
				'title'		=> __('Widgets', 'kc-essentials'),
				'type'		=> 'checkbox',
				'options'	=> array(
					'post'		=> __('KC Posts', 'kc-essentials'),
					'menu'		=> __('KC Custom Menu', 'kc-essentials')
				)
			)
		)
	);

	# Adaptive images
	$rgt_link = class_exists( 'RegenerateThumbnails' ) ? admin_url('tools.php?page=regenerate-thumbnails') : 'http://wordpress.org/extend/plugins/regenerate-thumbnails/';
	$options[] = array(
		'id'			=> 'adaptive_images',
		'title'		=> __('Adaptive Images', 'kc-essentials'),
		'fields'	=> array(
			array(
				'id'			=> 'sizes',
				'title'		=> __('Image sizes', 'kc-essentials'),
				'type'		=> 'text',
				'attr'		=> array( 'style' => 'width:98%' ),
				'desc'		=> sprintf( __('Comma separated list of image widths. <span class="impo">Don&#39;t forget to <a href="%s">regenerate the thumbnails</a></span>.', 'kc-essentials'), $rgt_link )
			),
			array(
				'id'			=> 'default',
				'title'		=> __('Default size', 'kc-essentials'),
				'type'		=> 'text'
			)
		)
	);

	# History.js
	$options[] = array(
		'id'			=> 'history_js',
		'title'		=> __('History.js', 'kc-essentials'),
		'fields'	=> array(
			array(
				'id'			=> 'el_excludes',
				'title'		=> __('Excluded link selectors', 'kc-essentials'),
				'type'		=> 'text',
				'attr'		=> array( 'style' => 'width:98%' ),
				'desc'		=> __('Comma separated list of jQuery selectors for link elements that shouldn&#39;t be ajaxified, default is <code>#comment-popup-link</code>', 'kc-essentials'),
				'default'	=> '#comment-popup-link'
			),
			array(
				'id'			=> 'el_content',
				'title'		=> __('Content selectors', 'kc-essentials'),
				'type'		=> 'text',
				'attr'		=> array( 'style' => 'width:98%' ),
				'desc'		=> __('Comma separated list of jQuery selectors for the content element, default is <code>#main, #content, article:first, .article:first, .post:first</code>', 'kc-essentials'),
				'default'	=> '#main, #content, article:first, .article:first, .post:first'
			),
			array(
				'id'			=> 'el_menu',
				'title'		=> __('Menu selectors', 'kc-essentials'),
				'type'		=> 'text',
				'attr'		=> array( 'style' => 'width:98%' ),
				'desc'		=> __('Comma separated list of jQuery selectors for the menu elements, default is <code>.menu, nav</code>', 'kc-essentials'),
				'default'	=> '.menu, nav'
			),
			array(
				'id'			=> 'el_menu_children',
				'title'		=> __('Children menu selectors', 'kc-essentials'),
				'type'		=> 'text',
				'attr'		=> array( 'style' => 'width:98%' ),
				'desc'		=> __('Comma separated list of jQuery selectors for the children menu elements, <code>.sub-menu, .children</code> will always be used.', 'kc-essentials')
			),
			array(
				'id'			=> 'el_active_menu',
				'title'		=> __('Active menu item selectors', 'kc-essentials'),
				'type'		=> 'text',
				'attr'		=> array( 'style' => 'width:98%' ),
				'desc'		=> __('Comma separated list of additional jQuery selectors for active menu item, <code>.current-menu-item</code> will always be used.', 'kc-essentials')
			),
			array(
				'id'			=> 'class_active_menu',
				'title'		=> __('Active menu item classes', 'kc-essentials'),
				'type'		=> 'text',
				'attr'		=> array( 'style' => 'width:98%' ),
				'desc'		=> __('<b>Space</b> separated list of additional active menu classes. <code>current-menu-item</code> class will always be added to the clicked <b>menu</b> item.', 'kc-essentials')
			),
			array(
				'id'			=> 'el_active_others',
				'title'		=> __('Active non-menu item selectors', 'kc-essentials'),
				'type'		=> 'text',
				'attr'		=> array( 'style' => 'width:98%' ),
				'desc'		=> __('Comma separated list of jQuery selectors for active non-menu link elements, default is <code>.current, .active</code>.', 'kc-essentials')
			),
			array(
				'id'			=> 'class_active_others',
				'title'		=> __('Active classes (non-menu)', 'kc-essentials'),
				'type'		=> 'text',
				'attr'		=> array( 'style' => 'width:98%' ),
				'desc'		=> __('<b>Space</b> separated list of active non-menu item classes.', 'kc-essentials'),
				'default'	=> 'current active'
			)
		)
	);



	# The entry for KC Settings
	$kcss_settings = array(
		'prefix'			=> 'kc_essentials',
		'menu_title'	=> 'KC Essentials',
		'page_title'	=> __('KC Essentials Settings', 'kc-essentials'),
		'display'			=> 'metabox',
		'options'			=> $options
	);

	$settings[] = $kcss_settings;
	return $settings;
}


add_filter( 'kcv_setting_kc_essentials_widget_custom_id_class_id', 'kc_essentials_sanitize_html_classes' );
add_filter( 'kcv_setting_kc_essentials_widget_custom_id_class_class', 'kc_essentials_sanitize_html_classes' );


function _kc_essentials_sanitize_image_sizes( $value ) {
	$_sizes = explode( ',', $value );
	foreach ( $_sizes as $idx => $_s ) {
		$_w = absint( $_s );
		if ( !$_w )
			unset( $_sizes[$idx] );
	}
	return implode( ',', $_sizes );
}
add_filter( 'kcv_setting_kc_essentials_adaptive_images_sizes', '_kc_essentials_sanitize_image_sizes' );


?>
