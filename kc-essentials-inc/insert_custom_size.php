<?php

class kcEssentials_insert_custom_size {
  public static $data;


	public static function init() {
		$image_sizes = array();
		global $_wp_additional_image_sizes;
		if ( isset($_wp_additional_image_sizes) && count($_wp_additional_image_sizes) )
			$image_sizes = apply_filters( 'intermediate_image_sizes', $_wp_additional_image_sizes );

		if ( empty($image_sizes) )
			return false;

		self::$data['image_sizes'] = $image_sizes;
		add_filter( 'attachment_fields_to_edit', array(__CLASS__, '_custom_image_sizes'), 11, 2 );
	}


	public static function _custom_image_sizes( $fields, $post ) {
		if ( !isset($fields['image-size']['html']) || substr($post->post_mime_type, 0, 5) != 'image' )
			return $fields;

		$items = array();
		foreach ( array_keys(self::$data['image_sizes']) as $size ) {
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

}

?>
