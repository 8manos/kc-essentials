<?php

/**
 * Dropdown menu module
 * @package KC_Essentials
 * @since 0.3
 */

if ( !class_exists('kcEssentials_Dropdown_Menu') ) {
	class kcEssentials_Dropdown_Menu {
		private static $did_js = false;

		public static function get_menu( $menu_id, $args = array() ) {
			$menu = wp_get_nav_menu_object( $menu_id );
			if ( !$menu )
				return;

			$items = wp_get_nav_menu_items( $menu_id );
			if ( !$items || is_wp_error($items) )
				return;

			$args = wp_parse_args( $args, array(
				'depth'       => 0,
				'pad'         => '&mdash;',
				'echo'        => true,
				'submit_text' => __('Go', 'kc-essentials'),
				'select_text' => '&mdash;&nbsp;'.__('Navigate', 'kc-essentials').'&nbsp;&mdash;',
				'js'          => false,
				'menu_class'  => '',
				'menu_id'     => ''
			) );

			$walk = new kcWalker_Menu;
			$walk->pad = $args['pad'];
			$menu_items = $walk->walk( $items, $args['depth'], $args );

			$class = 'kcform kcmenu';
			if ( $args['menu_class'] )
				$class .= " {$args['menu_class']}";

			$f_attr = 'class="'.$class.'" method="post"';
			if ( $args['menu_id'] )
				$f_attr .= ' id="'.$args['menu_id'].'"';

			$out  = '<form '.$f_attr.'>' . PHP_EOL;
			$out .= '<select name="kcform[menu-id]">' . PHP_EOL;
			if ( !empty($args['select_text']) )
				$out .= '<option value="">'.$args['select_text'].'</option>' . PHP_EOL;
			foreach( $menu_items as $_id => $_title )
				$out .= '<option value="'.$_id.'">'.$_title.'</option>' . PHP_EOL;
			$out .= '</select>' . PHP_EOL;
			$out .= '<input type="hidden" value="menu" name="kcform[action]" />' . PHP_EOL;
			$out .= '<button type="submit">'.$args['submit_text'].'</button>' . PHP_EOL;
			$out .= '</form>' . PHP_EOL;

			if ( $args['js'] && !self::$did_js ) {
				self::$did_js = true;
				wp_enqueue_script( 'jquery' );
				add_action( 'wp_print_footer_scripts', array(__CLASS__, 'print_js'), 999 );
			}

			if ( $args['echo'] )
				echo $out;
			else
				return $out;
		}


		public static function print_js() { ?>
	<script>
		jQuery(document).ready(function($) {
			$('form.kcmenu').submit(function(e) {
				if ( !$(this).children('select').val() )
					e.preventDefault();
			})
				.children('select').change(function() {
					if ( this.value )
						this.form.submit();
				});
		});
	</script>
		<?php }
	}


	function kc_dropdown_menu( $menu_id, $args = array() ) {
		return kcEssentials_Dropdown_Menu::get_menu( $menu_id, $args );
	}
}
