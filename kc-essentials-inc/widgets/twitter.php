<?php

/**
 * @package KC_Essentials
 * @version 0.1
 */


class kc_widget_twitter extends WP_Widget {
	var $defaults;

	function __construct() {
		$widget_ops = array( 'classname' => 'kcw_twitter', 'description' => __('Simple Twitter timeline', 'kc-essentials') );
		$control_ops = array( 'width' => 300, 'height' => 450 );
		parent::__construct( 'kcw_twitter', 'KC Twitter Timeline', $widget_ops, $control_ops );
		$this->defaults = array(
			'title'       => '',
			'username'    => '',
			'expiration'  => 60,
			'count'       => 5,
			'follow_text' => __('Follow me', 'kc-essentials'),
			'debug'       => 0
		);
	}


	function update( $new, $old ) {
		$new['expiration'] = ( isset($new['expiration']) && absint($new['expiration']) >= 5 ) ? $new['expiration'] : $this->defaults['expiration'];
		$new['count'] = ( isset($new['count']) && absint($new['count']) >= 1 ) ? $new['count'] : $this->defaults['count'];
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
				<label for="<?php echo $this->get_field_id('username') ?>"><?php _e('Username', 'kc-essentials') ?></label>
				<?php echo kcForm::input(array(
					'attr'    => array(
						'id'   => $this->get_field_id('username'),
						'name' => $this->get_field_name('username')
					),
					'current' => $instance['username']
				)) ?>
			</li>
			<li>
				<label for="<?php echo $this->get_field_id('expiration') ?>" title="<?php _e('Cache expiration time in minutes, minimum is 5', 'kc-essentials'); ?>"><?php _e('Expiration (m)', 'kc-essentials') ?> <small class="impo">(?)</small></label>
				<?php echo kcForm::input(array(
					'attr'    => array(
						'id'   => $this->get_field_id('expiration'),
						'name' => $this->get_field_name('expiration')
					),
					'current' => $instance['expiration']
				)) ?>
			</li>
			<li>
				<label for="<?php echo $this->get_field_id('count') ?>" title="<?php _e('Number of statuses to show, minimum is 1', 'kc-essentials') ?>"><?php _e('Count', 'kc-essentials') ?> <small class="impo">(?)</small></label>
				<?php echo kcForm::input(array(
					'attr'    => array(
						'id'   => $this->get_field_id('count'),
						'name' => $this->get_field_name('count')
					),
					'current' => $instance['count']
				)) ?>
			</li>
			<li>
				<label for="<?php echo $this->get_field_id('follow_text') ?>" title="<?php _e('Leave empty to not show it', 'kc-essentials') ?>"><?php _e('Follow text', 'kc-essentials') ?> <small class="impo">(?)</small></label>
				<?php echo kcForm::input(array(
					'attr'    => array(
						'id'   => $this->get_field_id('follow_text'),
						'name' => $this->get_field_name('follow_text')
					),
					'current' => $instance['follow_text']
				)) ?>
			</li>
		</ul>
	<?php }


	function widget( $args, $instance ) {
		if ( !$instance['username'] )
			return;

		$list = get_transient( "kc_twitter_{$instance['username']}" );
		if ( !$list ) {
			$json = wp_remote_get("http://api.twitter.com/1/statuses/user_timeline.json?screen_name={$instance['username']}&count={$instance['count']}");
			if ( is_wp_error($json) ) {
				return;
			}
			else {
				$list = json_decode( $json['body'], true );
				set_transient( "kc_twitter_{$instance['username']}", $list, $instance['expiration'] );
			}
		}

		$out = "<ul>\n";
		foreach ( $list as $item ) {
			$text = $item['text'];
			$text = apply_filters(
				'kc_twitter_status_text',
				"<p>".preg_replace(
					array('/(^|\s)#(\w*[a-zA-Z_]+\w*)/', '/(^|\s)@(\w*[a-zA-Z_]+\w*)/'),
					array('\1#<a href="http://search.twitter.com/search?q=%23\2">\2</a>',
					'<a href="http://twitter.com/\2">@\2</a>'),
					$text
				)."</p>\n",
				$text,
				$item
			);
			$out .= "<li class='item'>{$text}</li>\n";
		}
		$out .= "</ul>\n";

		$output  = $args['before_widget'];
		if ( $title = apply_filters( 'widget_title', $instance['title'] ) )
			$output .= $args['before_title'] . $title . $args['after_title'];
		$output .= $out;
		$output .= $args['after_widget'];

		echo $output;
	}
}
?>
