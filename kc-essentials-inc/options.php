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
						'uniquetax'	=> __('Unique taxonomies', 'kc-essentials')
					)
				)
			)
		)
	);

	$taxonomies = get_taxonomies( array('show_ui' => true), 'objects' );
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

?>
