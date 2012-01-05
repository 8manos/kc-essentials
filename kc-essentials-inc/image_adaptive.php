<?php

/**
 * @package KC_Essentials
 * @version 0.1
 */


class kcEssentials_adaptive_images {
	private static $pdata = array();


	public static function init() {
		$settings = kcEssentials::get_data('settings', 'image_adaptive');
		if ( !$settings )
			$settings = array();

		$sizes = kc_essentials_get_image_sizes( true );
		if ( isset($settings['sizes']) && !empty($settings['sizes']) ) {
			$_sizes = explode( ',', $settings['sizes'] );
			$count = 0;
			foreach ( $_sizes as $_s ) {
				$_w = absint( $_s );
				if ( !$_w || in_array($_w, $sizes) )
					continue;

				add_image_size( "kcai-{$count}", $_w );
				$count++;
			}
		}

		self::$pdata['sizes'] = kc_essentials_get_image_sizes( true );
		self::$pdata['default'] = ( isset($settings['default']) && absint($settings['default']) ) ? $settings['default'] : kc_get_default('kc_essentials', 'image_adaptive', 'default');

		add_action( 'wp_head', array(__CLASS__, '_cookie_script') );
	}


	/**
	* Set cookie
	*/
	function _cookie_script() {
		if ( !isset($_COOKIE['kc-resolution']) ) { ?>
<script type="text/javascript">
	document.cookie='kc-resolution='+Math.max(screen.width,screen.height)+'; path=<?php echo SITECOOKIEPATH ?>';
</script>
	<?php }
	}


	/**
	 * Get image depending on max screen size
	 *
	 * @param int $id Image attachment ID
	 * @param int $max Maximum width, default is false (use resolution from cookie or setting's default or 1280)
	 * @param bool $stepup true will get higher resolution than $max, false will get lower one
	 * @param bool $get_url Upon succes, true will return image URL, false will return image data array
	 *
	 * @return bool|string|array False on failure, string or array on success, depends on $get_url value
	 */
	function get_image( $id, $max = false, $stepup = true, $get_url = true ) {
		if ( !absint($max) )
			$max = isset( $_COOKIE['kc-resolution'] ) ? $_COOKIE['kc-resolution'] : self::$pdata['default'];

		$sizes = self::$pdata['sizes'];
		if ( $stepup === true ) {
			$last = end( $sizes );
			foreach ( $sizes as $size => $width ) {
				if ( ($width >= $max || $width == $last) && $image = image_get_intermediate_size($id, $size) )
					break;
			}
		}
		else {
			arsort( $sizes );
			$last = end( $sizes );
			foreach ( $sizes as $size => $width ) {
				if ( ($width <= $max || $width == $last) && $image = image_get_intermediate_size($id, $size) )
					break;
			}
		}

		if ( !$image )
			$image = image_get_intermediate_size($id, 'full');

		if ( $get_url && isset($image['url']) )
			return $image['url'];
		else
			return $image;
	}
}
kcEssentials_adaptive_images::init();


/**
 * Get image depending on max screen size
 *
 * @see kcEssentials_adaptive_images::get_image()
 */
function kc_get_adaptive_image( $id, $max = false, $stepup = true, $get_url = true ) {
	return kcEssentials_adaptive_images::get_image( $id, $max, $stepup, $get_url );
}

?>