<?php

/**
 * @package KC_Essentials
 * @version 0.1
 */


class kc_widget_sbanner extends WP_Widget {
	var $defaults;

	function __construct() {
		$widget_ops = array( 'classname' => 'kcw_sbanner', 'description' => __('Simple banner', 'kc-essentials') );
		$control_ops = array( 'width' => 200, 'height' => 350 );
		parent::__construct( 'kcw_sbanner', 'KC Simple Banner', $widget_ops, $control_ops );
		$this->defaults = array(
			'title'       => '',
			'source'      => 'post',
			'post_id'     => '',
			'is_flash'    => 0,
			'width'       => '250',
			'height'      => '100',
			'url'         => '',
			'link'        => '',
			'text_before' => '',
			'text_after'  => '',
			'debug'       => 0
		);
	}


	function update( $new, $old ) {
		return $new;
	}


	function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, $this->defaults );
		$title    = strip_tags( $instance['title'] );
	?>
		<h5 class="kcw-head" title="<?php _e('Show/hide', 'kc-essentials') ?>"><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Widget title', 'kc-essentials') ?></label></h5>
		<ul class="kcw-control-block">
			<li>
				<?php echo kcForm::input(array(
					'attr'    => array('id' => $this->get_field_id('title'), 'name' => $this->get_field_name('title'), 'class' => 'widefat'),
					'current' => $title
				)) ?>
			</li>
		</ul>

		<h5 class="kcw-head"><?php _e('Basic', 'kc-essentials') ?></h5>
		<ul class="kcw-control-block">
			<li>
				<label for="<?php echo $this->get_field_id('source'); ?>"><?php _e('Source', 'kc-essentials') ?></label>
				<?php echo kcForm::field(array(
					'type'    => 'select',
					'attr'    => array(
						'id'    => $this->get_field_id('source'),
						'name'  => $this->get_field_name('source'),
						'class'      => 'hasdep',
						'data-child' => '.sbanner-src',
						'data-scope' => 'div'
					),
					'options' => array(
						'post' => __('Post', 'kc-essentials'),
						'url'  => __('Custom URL', 'kc-essentials')
					),
					'none'    => false,
					'current' => $instance['source']
				)) ?>
			</li>
			<li class="sbanner-src" data-dep="url">
				<label for="<?php echo $this->get_field_id('url') ?>"><?php _e('File URL', 'kc-essentials') ?></label>
				<?php echo kcForm::input(array(
					'attr'    => array(
						'id'   => $this->get_field_id('url'),
						'name' => $this->get_field_name('url')
					),
					'current' => $instance['url']
				)) ?>
			</li>
			<li class="sbanner-src" data-dep="post">
				<label for="<?php echo $this->get_field_id('post_id') ?>"><?php _e('Attachment', 'kc-essentials') ?></label>
				<?php echo _kc_field_file_single( array(
					'field' => array (
						'mode'  => 'single',
						'size'  => 'thumbnail'
					),
					'id'       => $this->get_field_id('post_id'),
					'name'     => $this->get_field_name('post_id'),
					'db_value' => $instance['post_id'],
					'up_url'   => 'media-upload.php?kcsfs=true&post_id=0&tab=library&TB_iframe=1',
				)); ?>
			</li>
			<li>
				<label for="<?php echo $this->get_field_id('is_flash') ?>"><?php _e('Flash?', 'kc-essentials') ?></label>
				<?php echo kcForm::field(array(
					'type'    => 'select',
					'attr'         => array(
						'id'         => $this->get_field_id('is_flash'),
						'name'       => $this->get_field_name('is_flash'),
						'class'      => 'hasdep',
						'data-child' => '.sbanner-prop',
						'data-scope' => 'ul'
					),
					'options' => kcSettings_options::$yesno,
					'none'    => false,
					'current' => $instance['is_flash']
				) ); ?>
			</li>
			<li class="sbanner-prop" data-dep='1'>
				<label for="<?php echo $this->get_field_id('width') ?>"><?php _e('Width', 'kc-essentials') ?></label>
				<?php echo kcForm::input(array(
					'attr'    => array('id' => $this->get_field_id('width'), 'name' => $this->get_field_name('width')),
					'current' => $instance['width']
				)) ?>
			</li>
			<li class="sbanner-prop" data-dep='1'>
				<label for="<?php echo $this->get_field_id('height') ?>"><?php _e('Height', 'kc-essentials') ?></label>
				<?php echo kcForm::input(array(
					'attr'    => array('id' => $this->get_field_id('height'), 'name' => $this->get_field_name('height')),
					'current' => $instance['height']
				)) ?>
			</li>
			<li class="sbanner-prop" data-dep='0'>
				<label for="<?php echo $this->get_field_id('link') ?>"><?php _e('Link URL', 'kc-essentials') ?></label>
				<?php echo kcForm::input(array(
					'attr'    => array(
						'id'   => $this->get_field_id('link'),
						'name' => $this->get_field_name('link')
					),
					'current' => $instance['link']
				)) ?>
			</li>
		</ul>
	<?php }


	function widget( $args, $instance ) {
		if ( !$instance['source'] )
			return;

		if ( $instance['source'] == 'post' && $instance['post_id'] )
			$url = wp_get_attachment_url($instance['post_id']);
		elseif ( $instance['source'] == 'url' && $instance['url'] )
			$url = $instance['url'];

		if ( !isset($url) )
			return;

		if ( isset($instance['is_flash']) && $instance['is_flash'] ) {
			$w = ( isset($instance['width']) && $instance['width'] ) ? $instance['width'] : 200;
			$h = ( isset($instance['height']) && $instance['height'] ) ? $instance['height'] : 100;

			$banner  = "<object classid='clsid:D27CDB6E-AE6D-11cf-96B8-444553540000' width='{$instance['width']}' height='{$instance['height']}'>\n";
			$banner .= "<param name='movie' value='{$url}' />\n";
			$banner .= "<!--[if !IE]>--><object type='application/x-shockwave-flash' data='{$url}' width='{$instance['width']}' height='{$instance['height']}'></object><!--<![endif]-->\n";
			$banner .= "</object>\n";
			wp_print_scripts( 'swfobject' );
		}
		else {
			$banner = "<img src='{$url}' alt='' />";
		}
			if ( $instance['link'] )
				$banner = "<a href='{$instance['link']}'>{$banner}</a>";
		$banner = "<div class='kcw-sbanner-wrap'>{$banner}</div>\n";

		$output  = $args['before_widget'];
		if ( $title = apply_filters( 'widget_title', $instance['title'] ) )
			$output .= $args['before_title'] . $title . $args['after_title'];
		$output .= $banner;
		$output .= $args['after_widget'];

		echo $output;
	}
}
?>
