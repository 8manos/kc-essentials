<?php

/**
 * Simple banner widget module
 * @package KC_Essentials
 */


class kc_widget_sbanner extends WP_Widget {
	var $defaults;

	function __construct() {
		$widget_ops = array( 'classname' => 'kcw_sbanner', 'description' => __('Simple banner', 'kc-essentials') );
		$control_ops = array( 'width' => 200, 'height' => 350 );
		$this->defaults = array(
			'title' => '',
			'items' => array(
				array(
					'source'      => 'post',
					'post_id'     => '',
					'image_size'  => 'full',
					'is_flash'    => 0,
					'width'       => '250',
					'height'      => '100',
					'url'         => '',
					'link'        => '',
					'text_before' => '',
					'text_after'  => '',
					'filter_text' => false,
					'shortcode'   => false,
				),
			),
			'debug' => 0
		);
		parent::__construct( 'kcw_sbanner', 'KC Simple Banner', $widget_ops, $control_ops );
	}


	function update( $new, $old ) {
		return self::_check_items( $new );
	}


	function form( $instance ) {
		if ( !empty($instance) )
			$instance = $this->_update_config( $instance );

		$instance = wp_parse_args( (array) $instance, $this->defaults );
		$title    = strip_tags( $instance['title'] );
		?>
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
			<input type="text" class="widefat" id="<?php echo $this->get_field_id('title') ?>" name="<?php echo $this->get_field_name('title') ?>" value="<?php echo esc_attr($title) ?>" />
		</p>
		<ul class="kc-sbanner-items">
		<?php
			$item_count = 0;
			$_id = $this->get_field_id( 'items' );
			$_name = $this->get_field_name( 'items' );
			$yesno = kcSettings_options::$yesno;
			$image_sizes = kcSettings_options::$image_sizes;
			$up_url = esc_url( add_query_arg( array(
				'kcsfs'     => 'true',
				'post_id'   => '0',
				'tab'       => 'library',
				'width'     => '640',
				'TB_iframe' => '1'
			), 'media-upload.php' ) );

			foreach ( $instance['items'] as $item ) :
				$item_values = wp_parse_args( $item, $this->defaults['items'][0] );
		?>
			<li class="row" data-mode="items">
				<h4><?php printf( __('Item #%s', 'kc-essentials'), '<span class="count">'. ($item_count + 1) .'</span>' ) ?></h5>
				<details open="true">
					<summary><?php _e('Basic', 'kc-essentials') ?></summary>
					<ul class="kcw-control-block">
						<li>
							<label for="<?php echo "{$_id}-{$item_count}-source"; ?>"><?php _e('Source', 'kc-essentials') ?></label>
							<?php echo kcForm::field(array(
								'type'    => 'select',
								'attr'    => array(
									'id'    => "{$_id}-{$item_count}-source",
									'name'  => "{$_name}[{$item_count}][source]",
									'class'      => 'hasdep',
									'data-child' => '.sbanner-src',
									'data-scope' => 'ul'
								),
								'options' => array(
									'post' => __('Attachment', 'kc-essentials'),
									'url'  => __('Custom URL', 'kc-essentials')
								),
								'none'    => false,
								'current' => $item_values['source']
							)) ?>
						</li>
						<li class="sbanner-src" data-dep="url">
							<label for="<?php echo "{$_id}-{$item_count}-url" ?>"><?php _e('File URL', 'kc-essentials') ?></label>
							<?php echo kcForm::input(array(
								'attr'    => array(
									'id'   => "{$_id}-{$item_count}-url",
									'name' => "{$_name}[{$item_count}][url]",
								),
								'current' => $item_values['url']
							)) ?>
						</li>
						<li class="sbanner-src" data-dep="post">
							<label for="<?php echo "{$_id}-{$item_count}-post_id" ?>"><?php _e('Attachment', 'kc-essentials') ?></label>
							<?php echo _kc_field_file_single( array(
								'field' => array (
									'mode'      => 'single',
									'size'      => 'thumbnail',
									'mime_type' => 'all'
								),
								'id'       => "{$_id}-{$item_count}-post_id",
								'name'     => "{$_name}[{$item_count}][post_id]",
								'db_value' => $item_values['post_id'],
								'up_url'   => $up_url
							)); ?>
						</li>
						<li class="sbanner-src" data-dep="post">
							<label for="<?php echo "{$_id}-{$item_count}-image_size" ?>"><?php _e('Size', 'kc-essentials') ?></label>
							<?php echo kcForm::field(array(
								'type'    => 'select',
								'attr'    => array(
									'id'   => "{$_id}-{$item_count}-image_size",
									'name' => "{$_name}[{$item_count}][image_size]",
								),
								'options'  => $image_sizes,
								'none'     => false,
								'current'  => $item_values['image_size']
							)) ?>
						</li>
						<li>
							<label for="<?php echo "{$_id}-{$item_count}-is_flash" ?>"><?php _e('Flash?', 'kc-essentials') ?></label>
							<?php echo kcForm::field(array(
								'type'    => 'select',
								'attr'         => array(
									'id'         => "{$_id}-{$item_count}-is_flash",
									'name'       => "{$_name}[{$item_count}][is_flash]",
									'class'      => 'hasdep',
									'data-child' => '.sbanner-prop',
									'data-scope' => 'ul'
								),
								'options' => $yesno,
								'none'    => false,
								'current' => $item_values['is_flash']
							) ); ?>
						</li>
						<li class="sbanner-prop" data-dep='1'>
							<label for="<?php echo "{$_id}-{$item_count}-width" ?>"><?php _e('Width', 'kc-essentials') ?></label>
							<?php echo kcForm::input(array(
								'attr'    => array(
									'id'   => "{$_id}-{$item_count}-width",
									'name' => "{$_name}[{$item_count}][width]",
								),
								'current' => $item_values['width']
							)) ?>
						</li>
						<li class="sbanner-prop" data-dep='1'>
							<label for="<?php echo "{$_id}-{$item_count}-height" ?>"><?php _e('Height', 'kc-essentials') ?></label>
							<?php echo kcForm::input(array(
								'attr'    => array(
									'id'   => "{$_id}-{$item_count}-height",
									'name' => "{$_name}[{$item_count}][height]",
								),
								'current' => $item_values['height']
							)) ?>
						</li>
						<li class="sbanner-prop" data-dep='0'>
							<label for="<?php echo "{$_id}-{$item_count}-link" ?>" title="<?php _e('You can enter a post ID here to use its permalink, &#xA;double-click the input field to find posts.') ?>"><?php _e('Link URL', 'kc-essentials') ?> <span class="impo">(?)</span></label>
							<?php echo kcForm::input(array(
								'attr'    => array(
									'id'    => "{$_id}-{$item_count}-link",
									'name'  => "{$_name}[{$item_count}][link]",
									'class' => 'kc-find-post'
								),
								'current' => $item_values['link']
							)) ?>
						</li>
					</ul>
				</details>

				<details<?php if ( !empty($item_values['text_before']) || !empty($item_values['text_after']) ) echo ' open="true" ' ?>>
					<summary><?php _e('Misc.', 'kc-essentials') ?></summary>
					<ul>
						<li>
							<label for="<?php echo "{$_id}-{$item_count}-text_before" ?>"><?php _e('Text before banner', 'kc-essentials') ?></label>
							<textarea class="widefat" rows="4" cols="10" id="<?php echo "{$_id}-{$item_count}-text_before" ?>" name="<?php echo "{$_name}[{$item_count}][text_before]" ?>"><?php echo esc_textarea($item_values['text_before']) ?></textarea>
						</li>
						<li>
							<label for="<?php echo "{$_id}-{$item_count}-text_after" ?>"><?php _e('Text after banner', 'kc-essentials') ?></label>
							<textarea class="widefat" rows="4" cols="10" id="<?php echo "{$_id}-{$item_count}-text_after" ?>" name="<?php echo "{$_name}[{$item_count}][text_after]" ?>"><?php echo esc_textarea($item_values['text_after']) ?></textarea>
						</li>
						<li>
							<input id="<?php echo "{$_id}-{$item_count}-filter_text" ?>" name="<?php echo "{$_name}[{$item_count}][filter_text]" ?>" type="checkbox" <?php checked(isset($item_values['filter_text']) ? $item_values['filter_text'] : false); ?> value="1" />
							<label for="<?php echo "{$_id}-{$item_count}-filter_text" ?>"><?php _e('Automatically add paragraphs'); ?></label>
						</li>
						<li>
							<input id="<?php echo "{$_id}-{$item_count}-shortcode" ?>" name="<?php echo "{$_name}[{$item_count}][shortcode]" ?>" type="checkbox" <?php checked(isset($item_values['shortcode']) ? $item_values['shortcode'] : false); ?> value="1" />
							<label for="<?php echo "{$_id}-{$item_count}-shortcode" ?>"><?php _e('Enable shortcode'); ?></label>
						</li>
					</ul>
				</details>
				<p class="hide-if-no-js actions">
					<a class="add" title="<?php _e('Add item', 'kc-essentials') ?>"><?php _e('Add', 'kc-essentials') ?></a>
					<a class="del" title="<?php _e('Remove item', 'kc-essentials') ?>"><?php _e('Remove', 'kc-essentials') ?></a>
				</p>
			</li>
		<?php $item_count++; endforeach; ?>
		</ul>
	<?php }


	function widget( $args, $instance ) {
		$instance = $this->_update_config( $instance );
		$instance = self::_check_items( $instance );

		if ( empty( $instance['items']) )
			return;

		?>
		<?php echo $args['before_widget'] ?>
			<?php if ( $title = apply_filters( 'widget_title', $instance['title'] ) ) : ?>
			<?php echo $args['before_title'] . $title . $args['after_title'] ?>
			<?php endif; ?>
			<?php
				foreach ( $instance['items'] as $item ) :
					$has_link = false;
			?>
			<div class="sbanner-item">
				<?php
					if ( !empty($item['text_before']) ) :
						$text_before = trim( $item['text_before'] );
						if ( !empty($item['shortcode']) )
							$text_before = do_shortcode( $text_before );

						if ( !empty($text_before) ) :
							$text_before = !empty( $item['filter_text'] ) ? wpautop( $text_before ) : $text_before;
				?>
				<div class="text text-before">
					<?php echo $text_before ?>
				</div>
				<?php endif; endif; ?>
				<?php
					if ( !empty($item['link']) ) :
						if ( is_numeric($item['link']) )
							$item['link'] = get_permalink( $item['link'] );
						else
							$item['link'] = esc_url( $item['link'] );
				?>
				<a href="<?php echo $item['link'] ?>">
				<?php endif; ?>
					<?php
						if ( $item['source'] == 'post' && $item['post_id'] ) {
							if ( empty($item['is_flash']) ) {
								$size = !empty($item['image_size']) ? $item['image_size'] : 'full';
								$image_src = wp_get_attachment_image_src( $item['post_id'], $size );
								$item['url'] = $image_src[0];
							}
							else {
								$item['url'] = wp_get_attachment_url( $item['post_id'] );
							}
						}

						if ( empty($item['is_flash']) ) :
					?>
					<img src="<?php echo esc_url( $item['url'] ) ?>" alt="" />
					<?php else : ?>
					<?php
						echo self::_get_flash_template( $item );
						wp_enqueue_script( 'swfobject' );
					?>
					<?php endif; ?>
				<?php if ( !empty($item['link']) ) : ?>
				</a>
				<?php endif; ?>
				<?php
					if ( !empty($item['text_after']) ) :
						$text_after = trim( $item['text_after'] );
						if ( !empty($item['shortcode']) )
							$text_after = do_shortcode( $text_after );

						if ( !empty($text_after) ) :
							$text_after = !empty( $item['filter_text'] ) ? wpautop( $text_after ) : $text_after;
				?>
				<div class="text text-after">
					<?php echo $text_after ?>
				</div>
				<?php endif; endif; ?>
			</div>
			<?php endforeach; ?>
		<?php echo $args['after_widget'] ?>
		<?php
	}


	private function _update_config( $instance ) {
		if ( !empty($instance['items']) )
			return $instance;

		$item_0 = array();
		foreach ( array_keys( $this->defaults['items'][0] ) as $key ) {
			$item_0[$key] = isset( $instance[$key] ) ? $instance[$key] : '';
			unset( $instance[$key] );
		}
		$instance['items'][0] = $item_0;

		$all_settings = get_option( 'widget_kcw_sbanner' );
		$all_settings[$this->number] = $instance;
		update_option( 'widget_kcw_sbanner', $all_settings );

		return $instance;
	}


	private static function _check_items( $instance ) {
		foreach( $instance['items'] as $item_idx => $item ) {
			if ( empty($item['url']) && empty($item['post_id']) )
				unset( $instance['items'][$item_idx] );
		}

		return $instance;
	}


	private static function _get_flash_template( $item ) {
		$tpl = '
<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" width="%width%" height="%height%">
	<param name="movie" value="%url%" />
	<!--[if !IE]>--><object type="application/x-shockwave-flash" data="%url%" width="%width%" height="%height%"></object><!--<![endif]-->
</object>
';
		return str_replace(
			array( '%width%', '%height%', '%url%' ),
			array( $item['width'], $item['height'], $item['url'] ),
			$tpl
		);
	}


	public static function kcml_fields( $widgets ) {
		$widgets['widget_kcw_sbanner'] = array(
			array(
				'id'    => 'title',
				'type'  => 'text',
				'label' => __('Title')
			),
			array(
				'id'    => 'link',
				'type'  => 'text',
				'label' => __('Link URL', 'kc-essentials')
			),
			array(
				'id'    => 'text_before',
				'type'  => 'textarea',
				'label' => __('Text before banner', 'kc-essentials'),
				'attr'  => array('cols' => 10, 'rows' => 4)
			),
			array(
				'id'    => 'text_after',
				'type'  => 'textarea',
				'label' => __('Text after banner', 'kc-essentials'),
				'attr'  => array('cols' => 10, 'rows' => 4)
			)
		);

		return $widgets;
	}
}
add_filter( 'kcml_widget_fields', array('kc_widget_sbanner', 'kcml_fields') );
