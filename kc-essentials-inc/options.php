<?php
add_filter( 'kc_plugin_settings', 'kc_essentials_options' );
function kc_essentials_options( $settings ) {
	$options = array(
		'general'	=> array(
			'id'			=> 'general',
			'title'		=> __('KC Essentials Settings', 'kc-essentials'),
			'fields'	=> array(
				array(
					'id'			=> 'components',
					'title'		=> __('Components', 'kc-essentials'),
					'type'		=> 'checkbox',
					'options'	=> array(
						'uniquetax'								=> __('Unique taxonomies', 'kc-essentials'),
						'custom_widget_id_class'	=> __('Custom widget ID &amp; classes', 'kc-essentials'),
						'widgets'									=> __('Additional widgets', 'kc-essentials')
					)
				)
			)
		)
	);


	# Unique taxonomies
	$taxonomies = get_taxonomies( array('show_ui' => true, 'hierarchical' => true), 'objects' );
	if ( !empty($taxonomies) ) {
		$tax_list = array();
		foreach ( $taxonomies as $k => $v )
			$tax_list[$k] = $v->label;

		asort( $tax_list );

		$options['general']['fields'][] = array(
			'id'			=> 'uniquetax',
			'title'		=> __('Unique taxonomies', 'kc-essentials'),
			'type'		=> 'checkbox',
			'options'	=> $tax_list
		);

	}


	# Custom widget ID & classes
	$options['general']['fields'][] = array(
		'id'			=> 'custom_widget_id',
		'title'		=> __('Custom widget IDs', 'kc-essentials'),
		'type'		=> 'text',
		'attr'		=> array('style' => 'width:98%' ),
		'desc'		=> __('Predefined widget IDs (optional, separate with spaces)', 'kc-essentials'),
	);
	$options['general']['fields'][] = array(
		'id'			=> 'custom_widget_class',
		'title'		=> __('Custom widget classes', 'kc-essentials'),
		'type'		=> 'text',
		'attr'		=> array('style' => 'width:98%' ),
		'desc'		=> __('Predefined widget classes (optional, separate with spaces)', 'kc-essentials')
	);

	# Additional Widgets
	$options['general']['fields'][] = array(
		'id'			=> 'widgets',
		'title'		=> __('Additional widgets', 'kc-essentials'),
		'type'		=> 'checkbox',
		'options'	=> array(
			'post'	=> 'KC Posts'
		)
	);


	# The entry for KC Settings
	$my_settings = array(
		'prefix'			=> 'kc_essentials',
		'menu_title'	=> __('KC Essentials', 'kc-essentials'),
		'page_title'	=> __('KC Essentials Settings', 'kc-essentials'),
		'display'			=> 'metabox',
		'options'			=> $options
	);

	$settings[] = $my_settings;
	return $settings;
}


add_filter( 'kcv_setting_kc_essentials_general_custom_widget_id', 'kc_essentials_sanitize_html_classes' );
add_filter( 'kcv_setting_kc_essentials_general_custom_widget_class', 'kc_essentials_sanitize_html_classes' );


?>
