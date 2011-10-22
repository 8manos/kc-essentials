<?php
add_filter( 'kc_plugin_settings', 'kc_essentials_options' );
function kc_essentials_options( $settings ) {
	$options = array();

	$taxonomies = get_taxonomies( array('show_ui' => true), 'objects' );
	if ( !empty($taxonomies) ) {
		$tax_list = array();
		foreach ( $taxonomies as $k => $v ) {
			$tax_list[$k] = $v->label;
		}
		asort( $tax_list );

		$options['taxonomies'] = array(
			'id'			=> 'taxonomies',
			'title'		=> __('Taxonomies', 'kc-essentials'),
			'fields'	=> array(
				'unique' => array(
					'id'			=> 'unique',
					'title'		=> __('Unique taxonomies', 'kc-essentials'),
					'type'		=> 'checkbox',
					'options'	=> $tax_list
				)
			)
		);

	}

	$my_settings = array(
		'prefix'				=> 'kc_essentials',
		'menu_title'		=> __('KC Essentials', 'kc-essentials'),
		'page_title'		=> __('KC Essentials Settings', 'kc-essentials'),
		'options'				=> $options
	);

	$settings[] = $my_settings;
	return $settings;
}

?>
