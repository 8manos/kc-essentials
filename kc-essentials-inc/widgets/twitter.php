<?php

/**
 * Twitter timeline widget module
 * @package KC_Essentials
 */


class kc_widget_twitter extends WP_Widget {
	var $defaults;

	function __construct() {
		$widget_ops = array( 'classname' => 'kcw_twitter', 'description' => __('Simple Twitter timeline', 'kc-essentials') );
		$control_ops = array( 'width' => 300, 'height' => 450 );
		parent::__construct( 'kcw_twitter', 'KC Twitter Timeline', $widget_ops, $control_ops );
		$this->defaults = array(
			'title'           => '',
			'username'        => '',
			'consumer_key'    => '',
			'consumer_secret' => '',
			'access_token'    => '',
			'access_secret'   => '',
			'expiration'      => 30,
			'count'           => 5,
			'follow_text'     => __('Follow me', 'kc-essentials'),
			'show_date'       => true,
			'date_format'     => 'relative',
			'date_custom'     => get_option('date_format'),
			'debug'           => 0,
		);
	}


	function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, $this->defaults );
		$title    = strip_tags( $instance['title'] );
	?>
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
			<input type="text" class="widefat" id="<?php echo $this->get_field_id('title') ?>" name="<?php echo $this->get_field_name('title') ?>" value="<?php echo $title ?>" />
		</p>

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
				<label for="<?php echo $this->get_field_id('consumer_key') ?>"><?php _e('Consumer Key', 'kc-essentials') ?></label>
				<?php echo kcForm::input(array(
					'attr'    => array(
						'id'   => $this->get_field_id('consumer_key'),
						'name' => $this->get_field_name('consumer_key')
					),
					'current' => $instance['consumer_key']
				)) ?>
			</li>
			<li>
				<label for="<?php echo $this->get_field_id('consumer_secret') ?>"><?php _e('Consumer Secret', 'kc-essentials') ?></label>
				<?php echo kcForm::input(array(
					'attr'    => array(
						'id'   => $this->get_field_id('consumer_secret'),
						'name' => $this->get_field_name('consumer_secret')
					),
					'current' => $instance['consumer_secret']
				)) ?>
			</li>
			<?php /*if ( empty($instance['access_token']) ) : ?>
				<li><p class="description"><?php _e('Access token and secret is not saved yet. Save the configuration to get them.', 'kc-essentials') ?></p></li>
			<?php else : */?>
				<li>
					<label for="<?php echo $this->get_field_id('access_token') ?>"><?php _e('Access Token', 'kc-essentials') ?></label>
					<?php echo kcForm::input(array(
						'attr'    => array(
							'id'   => $this->get_field_id('access_token'),
							'name' => $this->get_field_name('access_token')
						),
						'current' => $instance['access_token'],
						//'attr'    => array('disabled' => 'disabled'),
					)) ?>
				</li>
				<li>
					<label for="<?php echo $this->get_field_id('access_secret') ?>"><?php _e('Access Secret', 'kc-essentials') ?></label>
					<?php echo kcForm::input(array(
						'attr'    => array(
							'id'   => $this->get_field_id('access_secret'),
							'name' => $this->get_field_name('access_secret')
						),
						'current' => $instance['access_secret'],
						//'attr'    => array('disabled' => 'disabled'),
					)) ?>
				</li>
			<?php # endif; ?>
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
				<label for="<?php echo $this->get_field_id('show_date') ?>"><?php _e('Show date', 'kc-essentials') ?></label>
				<?php echo kcForm::field(array(
					'type'    => 'select',
					'attr'    => array(
						'id'         => $this->get_field_id('show_date'),
						'name'       => $this->get_field_name('show_date'),
						'class'      => 'hasdep',
						'data-child' => '#p-'.$this->get_field_id('date_format')
					),
					'options' => kcSettings_options::$yesno,
					'none'    => false,
					'current' => $instance['show_date']
				)) ?>
			</li>
			<li id="<?php echo 'p-'.$this->get_field_id('date_format') ?>" data-dep="1">
				<label for="<?php echo $this->get_field_id('date_format') ?>"><?php _e('Date format', 'kc-essentials') ?></label>
				<?php echo kcForm::field(array(
					'type'    => 'select',
					'attr'    => array(
						'id'         => $this->get_field_id('date_format'),
						'name'       => $this->get_field_name('date_format'),
						'class'      => 'hasdep',
						'data-child' => '#p-'.$this->get_field_id('date_custom')
					),
					'options' => array(
						'relative'   => __('Relative', 'kc-essentials'),
						'relative_m' => __('Relative if &lt; 30 days', 'kc-essentials'),
						'global'     => __('Use global setting', 'kc-essentials'),
						'custom'     => __('Custom', 'kc-essentials')
					),
					'none'    => false,
					'current' => $instance['date_format']
				)) ?>
			</li>
			<li id="<?php echo 'p-'.$this->get_field_id('date_custom') ?>" data-dep="custom">
				<label for="<?php echo $this->get_field_id('date_custom') ?>" title="<?php _e('Use PHP date format', 'kc-essentials') ?>"><?php _e('Custom format', 'kc-essentials') ?> <small class="impo">(?)</small></label>
				<?php echo kcForm::input(array(
					'attr'    => array(
						'id'   => $this->get_field_id('date_custom'),
						'name' => $this->get_field_name('date_custom')
					),
					'current' => $instance['date_custom']
				)) ?>
			</li>
			<li>
				<label for="<?php echo $this->get_field_id('follow_text') ?>" title="<?php _e('Leave empty to disable', 'kc-essentials') ?>"><?php _e('Follow text', 'kc-essentials') ?> <small class="impo">(?)</small></label>
				<?php echo kcForm::input(array(
					'attr'    => array(
						'id'   => $this->get_field_id('follow_text'),
						'name' => $this->get_field_name('follow_text')
					),
					'current' => $instance['follow_text']
				)) ?>
			</li>
			<li>
				<label for="<?php echo $this->get_field_id('debug') ?>" title="<?php _e('Show debug messages?', 'kc-essentials') ?>"><?php _e('Debug', 'kc-essentials') ?> <small class="impo">(?)</small></label>
				<?php echo kcForm::field(array(
					'type'    => 'select',
					'attr'    => array(
						'id'   => $this->get_field_id('debug'),
						'name' => $this->get_field_name('debug')
					),
					'options' => kcSettings_options::$yesno,
					'current' => $instance['debug'],
				)) ?>
			</li>
		</ul>
	<?php
	}


	function update( $new, $old ) {
		$new = wp_parse_args( $new, $this->defaults );
		$new['expiration'] = absint( $new['expiration'] );
		if ( $new['expiration'] < $this->defaults['expiration'] )
			$new['expiration'] = $this->defaults['expiration'];

		$new['count'] = absint( $new['count'] );
		if ( $new['count'] < 1 )
			$new['count'] = 1;

		if ( 'custom' === $new['date_format'] && $new['date_custom'] == '' )
			$new['date_custom'] = get_option( 'date_format' );

		if ( !empty($new['username']) && $new['username'] !== $old['username'] )
			self::_fetch_timeline( $new );

		return $new;
	}


	private static function _fetch_timeline( $instance ) {
		$data = get_transient( sprintf('kcw_twitter_%s', $instance['username']) );
		if (
			!empty($data)
			&& count( (array) $data['tweets']) >= $instance['count']
			&& ( time() - $data['timestamp'] < (60 * $instance['expiration']) )
		) {
			return $data;
		}

		require_once kcEssentials::get_data('paths', 'inc') . '/libs/codebird.php';
		\Codebird\Codebird::setConsumerKey( $instance['consumer_key'], $instance['consumer_secret'] );
		$cb = \Codebird\Codebird::getInstance();
		$cb->setToken( $instance['access_token'], $instance['access_secret'] );
		$timeline = $cb->statuses_userTimeline( sprintf(
			'screen_name=%s&count=%d&exclude_replies=true',
			$instance['username'],
			$instance['count']
		) );
		if ( empty($timeline->httpstatus) || 200 !== $timeline->httpstatus )
			return false;

		unset( $timeline->httpstatus );
		$data = array(
			'timestamp' => time(),
			'username'  => $instance['username'],
			'tweets'    => $timeline,
		);

		set_transient( sprintf('kcw_twitter_%s', $instance['username']), $data, ( $instance['expiration'] * 60 ) );

		return $data;
	}


	function widget( $args, $instance ) {
		$title = apply_filters( 'widget_title', $instance['title'], $this, $instance );
		?>
		<?php echo $args['before_widget'] ?>
			<?php if ( !empty($title) ) : ?>
				<?php printf( '%s%s%s', $args['before_title'], $title, $args['after_title'] ); ?>
			<?php endif; ?>

			<?php if ( empty($instance['username']) ) : ?>
				<?php if ( $instance['debug'] ) : ?>
					<p><?php printf( __('Check your <a href="%s">config</a>!', 'kc-essentials'), admin_url('/widgets.php') ) ?></p>
				<?php endif; ?>
		<?php echo $args['after_widget']; ?>
			<?php return; endif; // if ( empty($instance['username']) )?>

			<?php
				$data = self::_fetch_timeline( $instance );
				if ( empty($data['tweets']) ) :
			?>
				<?php if ( $instance['debug'] ) : ?>
					<p><?php printf( __('An error occured!', 'kc-essentials'), admin_url('/widgets.php') ) ?></p>
				<?php endif; ?>
		<?php echo $args['after_widget']; ?>
			<?php return; endif; // if ( empty($data['tweets']) )?>
			<ul>
				<?php do_action( 'kcw_twitter_before_list', $this, $instance ); ?>
				<?php
					foreach ( $data['tweets'] as $idx => $item ) :
						if ( $idx == $instance['count'] )
							break;
				?>
				<li class='item'>
					<?php echo $this->process_tweet( $item ) ?>
					<?php if ( $instance['show_date'] ) : ?>
						<?php echo $this->_get_tweet_date( $item, $instance ) ?>
					<?php endif; ?>
				</li>
				<?php endforeach; ?>
			</ul>
			<?php if ( $instance['follow_text'] ) : ?>
				<?php echo apply_filters( 'kcw_twitter_follow_text', "<a href='http://twitter.com/{$instance['username']}' class='follow'><span>{$instance['follow_text']}</span></a>", $this, $instance ); ?>
			<?php endif; ?>
			<?php do_action( 'kcw_twitter_after_list', $this, $instance ); ?>
		<?php echo $args['after_widget']; ?>
	<?php
	}


	/**
	 * Process tweet object
	 *
	 * @link http://wp.tutsplus.com/tutorials/plugins/how-to-create-a-recent-tweets-widget/
	 */
	function process_tweet( $tweet ) {
		$entities = $tweet->entities;
		$content = $tweet->text;

		# Links
		if ( !empty($entities->urls) ) {
			foreach ( $entities->urls as $url ) {
				$content = str_ireplace($url->url, '<a href="'.esc_url($url->expanded_url).'">'.$url->display_url.'</a>', $content);
			}
		}

		# Hashtags
		if ( !empty($entities->hashtags) ) {
			foreach ( $entities->hashtags as $hashtag ) {
				$url = 'http://search.twitter.com/search?q=' . urlencode($hashtag->text);
				$content = str_ireplace('#'.$hashtag->text, '<a href="'.esc_url($url).'">#'.$hashtag->text.'</a>', $content);
			}
		}

		# Usernames
		if ( !empty($entities->user_mentions) ) {
			foreach ( $entities->user_mentions as $user ) {
				$url = 'http://twitter.com/'.urlencode($user->screen_name);
				$content = str_ireplace('@'.$user->screen_name, '<a href="'.esc_url($url).'">@'.$user->screen_name.'</a>', $content);
			}
		}

		# Media URLs
		if ( !empty($entities->media) ) {
			foreach ( $entities->media as $media ) {
				$content = str_ireplace($media->url, '<a href="'.esc_url($media->expanded_url).'">'.$media->display_url.'</a>', $content);
			}
		}

		return $content;
	}


	function _get_tweet_date( $item, $instance ) {
		static $now;
		if ( empty($now) )
			$now = time();

		$date = strtotime( $item->created_at );
		$diff = (int) abs( $now - $date );
		if ( $instance['date_format'] == 'relative' || ($instance['date_format'] == 'relative_m' && $diff <= 2592000) )
			$date = sprintf( __('%s ago', 'kc-essentials'), human_time_diff($date) );
		elseif ( $instance['date_format'] == 'global' || ($instance['date_format'] == 'relative_m' && $diff >= 2592000) )
			$date = sprintf( __('%1$s at %2$s', 'kc-essentials'), date(get_option('date_format'), $date), date(get_option('time_format'), $date) );
		else
			$date = date( $instance['date_custom'], $date );

		return apply_filters( 'kcw_twitter_date', "<abbr class='datetime' title='{$item->created_at}'>{$date}</abbr>", $date, $item->created_at, $this, $instance );
	}


	public static function kcml_fields( $widgets ) {
		$widgets['widget_kcw_twitter'] = array(
			array(
				'id'    => 'title',
				'type'  => 'text',
				'label' => __('Title')
			),
			array(
				'id'    => 'username',
				'type'  => 'text',
				'label' => __('Username', 'kc-essentials')
			),
			array(
				'id'    => 'follow_text',
				'type'  => 'text',
				'label' => __('Follow text', 'kc-essentials')
			)
		);

		return $widgets;
	}
}
add_filter( 'kcml_widget_fields', array('kc_widget_twitter', 'kcml_fields') );
