<?php

function kc_essentials_insert_custom_image_sizes( $fields, $post ) {
	$image_sizes = kc_essentials_get_aditional_image_sizes();
	if ( empty($image_sizes) || !isset($fields['image-size']['html']) || substr($post->post_mime_type, 0, 5) != 'image' )
		return $fields;

	$items = array();
	foreach ( array_keys($image_sizes) as $size ) {
		$downsize = image_downsize( $post->ID, $size );
		$enabled = $downsize[3];
		$css_id = "image-size-{$size}-{$post->ID}";
		$label = apply_filters( 'kc_image_size_name', $size );

		$html  = "<div class='image-size-item'>\n";
		$html .= "\t<input type='radio' " . disabled( $enabled, false, false ) . "name='attachments[{$post->ID}][image-size]' id='{$css_id}' value='{$size}' />\n";
		$html .= "\t<label for='{$css_id}'>{$label}</label>\n";
		if ( $enabled )
			$html .= "\t<label for='{$css_id}' class='help'>" . sprintf( "(%d&nbsp;&times;&nbsp;%d)", $downsize[1], $downsize[2] ). "</label>\n";
		$html .= "</div>";

		$items[] = $html;
	}

	$items = join( "\n", $items );
	$fields['image-size']['html'] = "{$fields['image-size']['html']}\n{$items}";

	return $fields;
}

add_filter( 'attachment_fields_to_edit', 'kc_essentials_insert_custom_image_sizes', 11, 2 );



?>
