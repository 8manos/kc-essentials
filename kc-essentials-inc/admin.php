<?php
add_filter( 'kc_plugin_settings', 'kc_essentials_options' );
function kc_essentials_options( $settings ) {
	$options = array(
		'general' => array(
			'id'			=> 'general',
			'title'		=> __('General', 'kc-essentials'),
			'fields'	=> array(
				'components' => array(
					'id'			=> 'components',
					'title'		=> __('Components', 'kc-essentials'),
					'type'		=> 'checkbox',
					'options'	=> array(
						'unique_taxonomies'	=> __('Unique taxonomies', 'kc-essentials')
					)
				)
			)
		)
	);

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
