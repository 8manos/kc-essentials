<?php

/**
 * @package KC_Essentials
 * @version 0.1
 */


class kc_widget_post extends WP_Widget {

	function __construct() {
		$widget_ops = array( 'classname' => 'kcw_post', 'description' => __('Query posts as you wish', 'kc-essentials') );
		$control_ops = array( 'width' => 200, 'height' => 350 );
		parent::__construct( 'kcw_post', 'KC Posts', $widget_ops, $control_ops );
	}


	function widget( $args, $instance ) {
		if ( $instance['action_id'] ) {
			$args['before_widget'] = apply_filters( "kc_widget-{$instance['action_id']}", $args['before_widget'], 'before_widget', 'widget_post' );
			$args['after_widget'] = apply_filters( "kc_widget-{$instance['action_id']}", $args['after_widget'], 'after_widget', 'widget_post' );
		}
		extract( $args );

		$debug  = "<h4>".__('KC Posts debug', 'kc-essentials')."</h4>\n";
		$debug .= "<h5>".__('Widget options', 'kc-essentials')."</h5>\n";
		$debug .= "<pre>".var_export($instance, true)."</pre>";

		$q_args = array(
			'post_type'      => $instance['post_type'],
			'posts_per_page' => $instance['posts_per_page'],
			'order'          => $instance['posts_order'],
			'orderby'        => $instance['posts_orderby']
		);

		# post orderby
		if ( $instance['posts_orderby'] == 'post__in' )
			add_filter( 'posts_orderby', array(&$this, 'sort_query_by_post_in'), 10, 2 );


		# Post status
		$q_args['post_status'] = implode( ',', $instance['post_status'] );

		# Included IDs
		if ( $instance['include'] )
			$q_args['post__in'] = explode( ',', str_replace(' ', '', $instance['include']) );

		# Excluded IDs
		if ( $instance['exclude'] )
			$q_args['post__not_in'] = explode( ',', str_replace(' ', '', $instance['exclude']) );

		# meta_query
		# Apply shortcodes for the values
		$meta_queries = array();
		foreach ( $instance['meta_query'] as $mq ) {
			if ( !empty($mq['key']) ) {
				$meta_queries[] = $mq;
			}
		}
		if ( !empty($meta_queries) )
			$q_args['meta_query'] = $meta_queries;

		# Taxonomies
		$tax_query_args = $instance['tax_query'];
		$tax_rel = $tax_query_args['relation'];
		unset( $tax_query_args['relation'] );
		$tax_queries = array();
		foreach ( $tax_query_args as $tq ) {
			if ( !empty($tq['taxonomy']) && !empty($tq['taxonomy']) ) {
				if ( empty($tq['operator']) )
					unset($tq['operator']);
				$tax_queries[] = $tq;
			}
		}
		if ( !empty($tax_queries) ) {
			if ( count($tax_queries) > 2 ) {
				if ( empty($tax_rel) )
					$tax_rel = 'OR';
			}
			$tax_queries['relation'] = $tax_rel;
			$q_args['tax_query'] = $tax_queries;
		}

		$debug .= "<h5>".__('Query parameters', 'kc-essentials')."</h5>\n";
		$debug .= "<pre>".var_export($q_args, true)."</pre>";

		$wp_query = new WP_Query($q_args);
		$output = '';

		if ( $wp_query->have_posts() ) {

			# Before widget
			$output .= $before_widget;

			$title = ( empty($instance['title']) ) ? apply_filters( 'widget_title', $instance['title'] ) : $instance['title'];
			# Widget title
			if ( $title )
				$output .= $before_title . $title . $after_title;

			# Posts wrapper (open)
			if ( $instance['posts_wrapper'] ) {
				if ( $instance['posts_class'] )
					$output .= "<{$instance['posts_wrapper']} class='{$instance['posts_class']}'>\n";
				else
					$output .= "<{$instance['posts_wrapper']}>\n";
			}

			while ( $wp_query->have_posts() ) {
				$wp_query->the_post();
				$post_id = get_the_ID();

				# Wrapper (open)
				if ( $instance['entry_wrapper'] ) {
					$output .= "<{$instance['entry_wrapper']}";
					if ( isset($instance['entry_class']) && $instance['entry_class'] )
						$output .= " class='{$instance['entry_class']}'";
					$output .= ">\n";
				}

				# Title
				if ( $instance['title_src'] )
					$output .= $this->_kc_get_title( $post_id, $instance );

				# Thumbnail
				if ( current_theme_supports('post-thumbnails') && $instance['thumb_size'] )
					$output .= $this->_kc_get_thumbnail( $post_id, $instance );

				# Content
				if ( $instance['content_src'] )
					$output .= $this->_kc_get_content( $post_id, $instance );

				# Wrapper (open)
				if ( $instance['entry_wrapper'] )
					$output .= "</{$instance['entry_wrapper']}>\n";
			}

			# Posts wrapper (close)
			if ( $instance['posts_wrapper'] )
				$output .= "</{$instance['posts_wrapper']}>\n";

			$output .= "{$after_widget}\n";
		}
		$wp_query = null;
		wp_reset_query();
		remove_filter( 'posts_orderby', array(&$this, 'sort_query_by_post_in') );

		echo $output;
		if ( $instance['debug'] )
			echo $debug;
	}


	function update( $new, $old ) {
		# Numberposts
		if ( !is_numeric($new['posts_per_page']) )
			$new['posts_per_page'] = get_option('posts_per_page');

		if ( empty($new['post_type']) )
			$new['post_type'] = array('post');

		# Post status
		## Media/attachment needs the 'inherit' status so force it when the 'attachment' post type is checked
		if ( in_array('attachment', $new['post_type']) && !in_array('inherit', $new['post_status']) )
			$new['post_status'][] = 'inherit';
		if ( empty($new['post_status']) )
			$new['post_status'] = array('publish');

		# Tax query
		$tax_query = $new['tax_query'];
		$tax_rel = $tax_query['relation'];
		unset( $tax_query['relation'] );

		$tax_queries = array();
		foreach ($tax_query as $tq ) {
			if ( !empty($tq['taxonomy']) && !empty($tq['terms']) )
				$tax_queries[] = $tq;
		}
		if ( empty($tax_queries) ) {
			$new['tax_query'] = array(
				'relation' => '',
				array(
					'taxonomy' => '',
					'terms'    => '',
					'field'    => 'slug',
					'operator' => ''
				)
			);
		}
		else {

			if ( count($tax_queries) > 1 && empty($tax_rel) )
				$tax_queries['relation'] = 'OR';
			else
				$tax_queries['relation'] = $tax_rel;

			$new['tax_query'] = $tax_queries;
		}

		# Meta query
		$meta_query = array();
		foreach ( $new['meta_query'] as $mq )
			if ( !empty($mq['key']) )
				$meta_query[] = $mq;
		if ( !empty($meta_query) )
			$new['meta_query'] = $meta_query;
		else
			$new['meta_query'] = array(
				array(
					'key'     => '',
					'value'   => '',
					'type'    => 'CHAR',
					'compare' => '='
				)
			);

		# Fix class names
		foreach ( array('posts', 'entry', 'title', 'content') as $el )
			if ( isset($new["{$el}_class"]) && !empty($new["{$el}_class"]) )
				$new["{$el}_class"] = kc_essentials_sanitize_html_classes( $new["{$el}_class"] );

		return $new;
	}


	function form( $instance ) {
		$defaults = array(
			'title'             => '',
			'post_type'         => array('post'),
			'post_status'       => array('publish'),
			'posts_per_page'    => get_option('posts_per_page'),
			'include'           => '',
			'exclude'           => '',
			'posts_order'       => 'DESC',
			'posts_orderby'     => 'date',
			'meta_key'          => '',
			'tax_query'         => array(
				'relation' => '',
				array(
					'taxonomy' => '',
					'terms'    => '',
					'field'    => 'slug',
					'operator' => ''
				)
			),
			'meta_query'        => array(
				array(
					'key'     => '',
					'value'   => '',
					'type'    => 'CHAR',
					'compare' => '='
				)
			),
			'posts_wrapper'   => '',
			'posts_class'     => '',
			'entry_wrapper'   => 'div',
			'entry_class'     => '',
			'title_src'       => 'default',
			'title_meta'      => '',
			'title_tag'       => 'h4',
			'title_link'      => 'default',
			'title_link_meta' => '',
			'title_class'     => 'title',
			'content_src'     => 'excerpt',
			'content_wrapper' => '',
			'content_class'   => '',
			'content_meta'    => '',
			'thumb_size'      => '',
			'thumb_src'       => '',
			'thumb_meta'      => '',
			'thumb_link'      => 'post',
			'more_link'       => '',
			'index_link'      => '',
			'action_id'       => '',
			'debug'           => false
		);
		$instance = wp_parse_args( (array) $instance, $defaults );
		$title = strip_tags( $instance['title'] );

		# Options
		$post_types = kcSettings_options::$post_types;
		$post_statuses = kcSettings_options::$post_statuses;

		$taxonomies = $terms = array();
		foreach ( get_taxonomies( array('public' => true), 'objects' ) as $t ) {
			$taxonomies[$t->name] = array( 'value' => $t->name, 'label' => $t->label );

			$terms[$t->name] = array();
			if ( $t->name == 'post_format' ) {
				if ( current_theme_supports( 'post-formats' ) ) {
					$formats = get_theme_support('post-formats');
					if ( is_array($formats[0]) ) {
						foreach ( $formats[0] as $format )
							$terms[$t->name][$format] = get_post_format_string( $format );
					}
					else {
						unset( $taxonomies[$t->name] );
						unset( $terms[$t->name] );
					}
				}
				else {
					unset( $taxonomies[$t->name] );
					unset( $terms[$t->name] );
				}
			}
			else {
				$t_terms = get_terms( $t->name );
				if ( !empty($t_terms) ) {
					foreach ( $t_terms as $tt )
						$terms[$t->name][$tt->slug] = array('value' => $tt->slug, 'label' => $tt->name);
				}
			}
		}

		$relations = array(
			array( 'value' => 'AND', 'label'	=> 'AND'),
			array( 'value' => 'OR',  'label'	=> 'OR')
		);
		$operators = array(
			array( 'value' => 'IN',          'label' => 'IN' ),
			array( 'value' => 'NOT IN',      'label' => 'NOT IN' ),
			array( 'value' => 'LIKE',        'label' => 'LIKE' ),
			array( 'value' => 'NOT LIKE',    'label' => 'NOT LIKE' ),
			array( 'value' => 'BETWEEN',     'label' => 'BETWEEN' ),
			array( 'value' => 'NOT BETWEEN', 'label' => 'NOT BETWEEN' )
		);
		$meta_compare = array(
			array( 'value' => '=',  'label' => '=' ),
			array( 'value' => '!=', 'label' => '!=' ),
			array( 'value' => '>',  'label' => '>' ),
			array( 'value' => '>=', 'label' => '>=' ),
			array( 'value' => '<',  'label' => '<' ),
			array( 'value' => '<=', 'label' => '<=' )
		);
		$meta_type = array(
			array( 'value' => 'BINARY',   'label'=> 'Binary' ),
			array( 'value' => 'CHAR',     'label'=> 'Char' ),
			array( 'value' => 'DATE',     'label'=> 'Date' ),
			array( 'value' => 'DATETIME', 'label'=> 'Datetime' ),
			array( 'value' => 'DECIMAL',  'label'=> 'Decimal' ),
			array( 'value' => 'NUMERIC',  'label'=> 'Numeric' ),
			array( 'value' => 'SIGNED',   'label'=> 'Signed' ),
			array( 'value' => 'UNSIGNED', 'label'=> 'Unsigned' ),
			array( 'value' => 'TIME',     'label'=> 'Time' )
		);

		$image_sizes = kcSettings_options::$image_sizes;
		$src_common = array(
			'default' => __('Default', 'kc-essentials'),
			'meta'    => __('Custom field', 'kc-essentials')
		);
		$tags_title = array(
			'h2'   => 'h2',
			'h3'   => 'h3',
			'h4'   => 'h4',
			'h5'   => 'h5',
			'h6'   => 'h6',
			'p'    => 'p',
			'span' => 'span',
			'div'  => 'div'
		);
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

		<h5 class="kcw-head" title="<?php _e('Show/hide', 'kc-essentials') ?>"><?php _e('Basic', 'kc-essentials') ?></h5>
		<ul class="kcw-control-block">
			<li>
				<label><?php _e('Post type', 'kc-essentials') ?></label>
				<div class="checks">
					<?php echo kcForm::field(array(
						'type'    => 'checkbox',
						'attr'    => array('id' => $this->get_field_id('post_type'), 'name' => $this->get_field_name('post_type').'[]'),
						'current' => $instance['post_type'],
						'options' => $post_types
					)) ?>
				</div>
			</li>
			<li>
				<label><?php _e('Post status', 'kc-essentials') ?></label>
				<div class="checks">
					<?php echo kcForm::field(array(
						'type'    => 'checkbox',
						'attr'    => array('id' => $this->get_field_id('post_status'), 'name' => $this->get_field_name('post_status').'[]'),
						'current' => $instance['post_status'],
						'options' => $post_statuses
					)) ?>
				</div>
			</li>
			<li>
				<label for="<?php echo $this->get_field_id('posts_per_page'); ?>" title="<?php _e("Use -1 to show all posts") ?>"><?php _e('Count', 'kc-essentials') ?> <small class="impo">(?)</small></label>
				<?php echo kcForm::input(array(
					'attr'    => array('id' => $this->get_field_id('posts_per_page'), 'name' => $this->get_field_name('posts_per_page')),
					'current' => $instance['posts_per_page']
				)) ?>
			</li>
			<li>
				<label for="<?php echo $this->get_field_id('include'); ?>" title="<?php _e('Separate post IDs with commas') ?>"><?php _e('Incl. IDs', 'kc-essentials') ?> <small class="impo">(?)</small></label>
				<?php echo kcForm::input(array(
					'attr'    => array('id' => $this->get_field_id('include'), 'name' => $this->get_field_name('include')),
					'current' => $instance['include']
				)) ?>
			</li>
			<li>
				<label for="<?php echo $this->get_field_id('exclude'); ?>" title="<?php _e('Separate post IDs with commas') ?>"><?php _e('Excl. IDs', 'kc-essentials') ?> <small class="impo">(?)</small></label>
				<?php echo kcForm::input(array(
					'attr'    => array('id' => $this->get_field_id('exclude'), 'name' => $this->get_field_name('exclude')),
					'current' => $instance['exclude']
				)) ?>
			</li>
			<li>
				<label for="<?php echo $this->get_field_id('posts_order'); ?>"><?php _e('Order', 'kc-essentials') ?></label>
				<?php echo kcForm::select(array(
					'attr'    => array('id' => $this->get_field_id('posts_order'), 'name' => $this->get_field_name('posts_order')),
					'current' => $instance['posts_order'],
					'options' => array(
						array( 'value' => 'DESC', 'label' => __('Descending', 'kc-essentials') ),
						array( 'value' => 'ASC',  'label' => __('Ascending', 'kc-essentials') )
					),
					'none'		=> false
				)) ?>
			</li>
			<li>
				<label for="<?php echo $this->get_field_id('posts_orderby'); ?>"><?php _e('Order by', 'kc-essentials') ?></label>
				<?php echo kcForm::select(array(
					'attr' => array(
						'id'         => $this->get_field_id('posts_orderby'),
						'name'       => $this->get_field_name('posts_orderby'),
						'class'      => 'hasdep',
						'data-child' => '#p-'.$this->get_field_id('meta_key')
					),
					'current' => $instance['posts_orderby'],
					'options' => array(
						array( 'value' => 'date',           'label' => __('Publish date', 'kc-essentials') ),
						array( 'value' => 'ID',             'label' => __('ID', 'kc-essentials') ),
						array( 'value' => 'title',          'label' => __('Title', 'kc-essentials') ),
						array( 'value' => 'author',         'label' => __('Author', 'kc-essentials') ),
						array( 'value' => 'modified',       'label' => __('Modification date', 'kc-essentials') ),
						array( 'value' => 'menu_order',     'label' => __('Menu order', 'kc-essentials') ),
						array( 'value' => 'parent',         'label' => __('Parent', 'kc-essentials') ),
						array( 'value' => 'comment_count',  'label' => __('Comment count', 'kc-essentials') ),
						array( 'value' => 'rand',           'label' => __('Random', 'kc-essentials') ),
						array( 'value' => 'post__in',       'label' => __('Included IDs', 'kc-essentials') ),
						array( 'value' => 'meta_value',     'label' => __('Meta value', 'kc-essentials') ),
						array( 'value' => 'meta_value_num', 'label'	=> __('Meta value num', 'kc-essentials') )
					),
					'none'    => false
				)) ?>
			</li>
			<li id="<?php echo 'p-'.$this->get_field_id('meta_key') ?>" data-dep='["meta_value", "meta_value_num"]'>
				<label for="<?php echo $this->get_field_id('meta_key') ?>" title="<?php _e("Fill this if you select 'Meta value' or 'Meta value num' above", 'kc-essentials') ?>"><?php _e('Meta key', 'kc-essentials') ?> <small class="impo">(?)</small></label>
				<?php echo kcForm::input(array(
					'attr'    => array('id' => $this->get_field_id('meta_key'), 'name' => $this->get_field_name('meta_key') ),
					'current' => $instance['meta_key']
				)) ?>
			</li>
		</ul>

		<?php
			$tq_id = $this->get_field_id('tax_query');
			$tq_name = $this->get_field_name('tax_query');

			$tq_values = $instance['tax_query'];
			$tq_rel = $tq_values['relation'];
			unset( $tq_values['relation'] );
		?>
		<h5 class="kcw-head" title="<?php _e('Show/hide', 'kc-essentials') ?>"><?php _e('Taxonomies', 'kc-essentials') ?></h5>
		<ul class="kcw-control-block taxonomies<?php if ( count($tq_values) == 1 && empty($tq_values[0]['taxonomy']) ) echo ' hide-if-js' ?>">
			<li class="relation">
				<label for="<?php echo "{$tq_id}-relation" ?>"><?php _e('Relation', 'kc-essentials') ?></label>
				<?php echo kcForm::select(array(
					'attr'    => array('id' => "{$tq_id}-relation", 'name' => "{$tq_name}[relation]"),
					'current' => $tq_rel,
					'options' => $relations,
					'none'    => false
				)) ?>
			</li>
			<li>
				<ul class="tax-queries">
					<?php foreach ( $tq_values as $idx => $query ) { ?>
					<li class="row">
						<label for="<?php echo "{$tq_id}-{$idx}-taxonomy" ?>"><?php _e('Taxonomy', 'kc-essentials') ?></label>
						<?php echo kcForm::select(array(
							'attr'    => array(
								'id'         => "{$tq_id}-{$idx}-taxonomy",
								'name'       => "{$tq_name}[{$idx}][taxonomy]",
								'class'      => 'hasdep',
								'data-scope' => 'li.row',
								'data-child' => '.terms'
							),
							'current' => $query['taxonomy'],
							'options' => $taxonomies
						)) ?>
						<label for="<?php echo "{$tq_id}-{$idx}-operator" ?>"><?php _e('Operator', 'kc-essentials') ?></label>
						<?php echo kcForm::select(array(
							'attr'    => array('id' => "{$tq_id}-{$idx}-operator", 'name' => "{$tq_name}[{$idx}][operator]"),
							'current' => $query['operator'],
							'options' => $operators,
							'none'    => false
						)) ?>
						<label><?php _e('Terms', 'kc-essentials') ?></label>
						<p class='checks terms hide-if-js info' data-dep=''><?php _e('Please select a taxonomy above to see its terms.', 'kc-essentials') ?>
						<?php if ( !empty($terms) ) { foreach ( $terms as $tax_name => $tax_terms ) { ?>
						<h6 class='hide-if-js'><?php echo $taxonomies[$tax_name]['label'] ?></h6>
						<div class='checks terms hide-if-js' data-dep='<?php echo $tax_name ?>'>
						<?php  if ( !empty($terms[$tax_name]) ) {
							echo kcForm::checkbox(array(
								'attr'    => array('name' => "{$tq_name}[{$idx}][terms][]", 'class' => 'term'),
								'current' => $query['terms'],
								'options' => $tax_terms
							));
						} else {
							echo "\t<p>".__("This taxonomy doesn't have any term with posts.", 'kc-essentials')."</p>\n\n";
						} ?>
						</div>
						<?php } } ?>
						<a class="hide-if-no-js del action" rel="tax_query" title="<?php _e('Remove this taxonomy query', 'kc-essentials') ?>"><?php _e('Remove', 'kc-essentials') ?></a>
						<input type='hidden' name="<?php echo "{$tq_name}[{$idx}][field]" ?>" value="slug"/>
					</li>
					<?php } ?>
					<li><a class="hide-if-no-js add action" rel="tax_query" title="<?php _e('Add new taxonomy query', 'kc-essentials') ?>"><?php _e('Add', 'kc-essentials') ?></a></li>
				</ul>
			</li>
		</ul>

		<?php
			$mq_name = $this->get_field_name('meta_query');
			$mq_id = $this->get_field_id('meta_query');
		?>
		<h5 class="kcw-head" title="<?php _e('Show/hide', 'kc-essentials') ?>"><?php _e('Metadata', 'kc-essentials') ?></h5>
		<ul class="kcw-control-block metadata<?php if ( count($instance['meta_query']) == 1 && empty($instance['meta_query'][0]['key']) ) echo ' hide-if-js' ?>">
			<li>
				<ul class="meta-queries">
					<?php foreach ( $instance['meta_query'] as $mq_idx => $mq ) { ?>
					<li class="row">
						<label for="<?php echo "{$mq_id}-{$mq_idx}-key" ?>"><?php _e('Key', 'kc-essentials') ?></label>
						<?php echo kcForm::input(array(
							'attr'    => array('id' => "{$mq_id}-{$mq_idx}-key", 'name' => "{$mq_name}[{$mq_idx}][key]"),
							'current' => $mq['key']
						)) ?>
						<label for="<?php echo "{$mq_id}-{$mq_idx}-value" ?>"><?php _e('Value', 'kc-essentials') ?></label>
						<?php echo kcForm::input(array(
							'attr'    => array('id' => "{$mq_id}-{$mq_idx}-value", 'name' => "{$mq_name}[{$mq_idx}][value]"),
							'current' => $mq['value']
						)) ?>
						<label for="<?php echo "{$mq_id}-{$mq_idx}-compare" ?>"><?php _e('Compare', 'kc-essentials') ?></label>
						<?php echo kcForm::select(array(
							'attr'    => array('id' => "{$mq_id}-{$idx}-compare", 'name' => "{$mq_name}[{$mq_idx}][compare]"),
							'current' => $mq['compare'],
							'options' => array_merge($meta_compare, $operators),
							'none'    => false
						)) ?>
						<label for="<?php echo "{$mq_id}-{$mq_idx}-type" ?>"><?php _e('Type', 'kc-essentials') ?></label>
						<?php echo kcForm::select(array(
							'attr'    => array('id' => "{$mq_id}-{$mq_idx}-type", 'name' => "{$mq_name}[{$mq_idx}][type]"),
							'current' => $mq['type'],
							'options' => $meta_type,
							'none'    => false
						)) ?>
					<a class="hide-if-no-js del action" rel="meta_query" title="<?php _e('Remove this taxonomy query', 'kc-essentials') ?>"><?php _e('Remove', 'kc-essentials') ?></a>
					</li>
					<?php } ?>
					<li><a class="hide-if-no-js add action" rel="meta_query" title="<?php _e('Add new meta query', 'kc-essentials') ?>"><?php _e('Add', 'kc-essentials') ?></a></li>
				</ul>
			</li>
		</ul>

		<h5 class="kcw-head" title="<?php _e('Show/hide', 'kc-essentials') ?>"><?php _e('Posts wrapper', 'kc-essentials') ?></h5>
		<ul class="kcw-control-block hide-if-js">
			<li>
				<label for="<?php echo $this->get_field_id('posts_wrapper') ?>"><?php _e('Tag', 'kc-essentials') ?></label>
				<?php echo kcForm::select(array(
					'attr'    => array('id' => $this->get_field_id('posts_wrapper'), 'name' => $this->get_field_name('posts_wrapper')),
					'current' => $instance['posts_wrapper'],
					'options' => array(
						array( 'value' => 'div',     'label' => 'div' ),
						array( 'value' => 'section', 'label' => 'section' ),
						array( 'value' => 'ol',      'label' => 'ol' ),
						array( 'value' => 'ul',      'label' => 'ul' )
					)
				)) ?>
			</li>
			<li>
				<label for="<?php echo $this->get_field_id('posts_class') ?>"><?php _e('Class', 'kc-essentials') ?></label>
				<?php echo kcForm::input(array(
					'attr'    => array('id' => $this->get_field_id('posts_class'), 'name' => $this->get_field_name('posts_class')),
					'current' => $instance['posts_class']
				)) ?>
			</li>
		</ul>

		<h5 class="kcw-head" title="<?php _e('Show/hide', 'kc-essentials') ?>"><?php _e('Entry wrapper', 'kc-essentials') ?></h5>
		<ul class="kcw-control-block hide-if-js">
			<li>
				<label for="<?php echo $this->get_field_id('entry_wrapper') ?>"><?php _e('Tag', 'kc-essentials') ?></label>
				<?php echo kcForm::select(array(
					'attr'    => array('id' => $this->get_field_id('entry_wrapper'), 'name' => $this->get_field_name('entry_wrapper')),
					'current' => $instance['entry_wrapper'],
					'options' => array(
						array( 'value' => 'div',     'label' => 'div' ),
						array( 'value' => 'article', 'label' => 'article' ),
						array( 'value' => 'li',      'label' => 'li' )
					)
				)) ?>
			</li>
			<li>
				<label for="<?php echo $this->get_field_id('entry_class') ?>"><?php _e('Class', 'kc-essentials') ?></label>
				<?php echo kcForm::input(array(
					'attr'    => array('id' => $this->get_field_id('entry_class'), 'name' => $this->get_field_name('entry_class')),
					'current' => $instance['entry_class']
				)) ?>
			</li>
		</ul>

		<h5 class="kcw-head" title="<?php _e('Show/hide', 'kc-essentials') ?>"><?php _e('Entry title', 'kc-essentials') ?></h5>
		<ul class="kcw-control-block hide-if-js">
			<li>
				<label for="<?php echo $this->get_field_id('title_src') ?>"><?php _e('Source', 'kc-essentials') ?></label>
				<?php echo kcForm::field(array(
					'type'    => 'select',
					'attr'    => array(
						'id'         => $this->get_field_id('title_src'),
						'name'       => $this->get_field_name('title_src'),
						'class'      => 'hasdep',
						'data-child' => '.chTitle',
						'data-scope' => 'ul'
					),
					'current' => $instance['title_src'],
					'options' => $src_common
				)) ?>
			</li>
			<li class="chTitle" data-dep='meta'>
				<label for="<?php echo $this->get_field_id('title_meta') ?>" title="<?php _e("Fill this if you select 'Custom field' above", 'kc-essentials') ?>"><?php _e('Meta key', 'kc-essentials') ?> <small class="impo">(?)</small></label>
				<?php echo kcForm::input(array(
					'attr'    => array('id' => $this->get_field_id('title_meta'), 'name' => $this->get_field_name('title_meta')),
					'current' => $instance['title_meta']
				)) ?>
			</li>
			<li class="chTitle" data-dep='<?php echo json_encode(array_keys($src_common) )?>'>
				<label for="<?php echo $this->get_field_id('title_tag') ?>"><?php _e('Tag', 'kc-essentials') ?></label>
				<?php echo kcForm::field(array(
					'type'    => 'select',
					'attr'    => array(
						'id'         => $this->get_field_id('title_tag'),
						'name'       => $this->get_field_name('title_tag'),
						'class'      => 'hasdep',
						'data-child' => '.chTitleTag',
						'data-scope' => 'ul'
					),
					'current' => $instance['title_tag'],
					'options' => $tags_title
				)) ?>
			</li>
			<li class="chTitleTag" data-dep='<?php echo json_encode(array_keys($tags_title)) ?>'>
				<label for="<?php echo $this->get_field_id('title_class') ?>"><?php _e('Class', 'kc-essentials') ?></label>
				<?php echo kcForm::input(array(
					'attr'    => array('id' => $this->get_field_id('title_class'), 'name' => $this->get_field_name('title_class')),
					'current' => $instance['title_class']
				)) ?>
			</li>
			<li class="chTitle" data-dep='<?php echo json_encode(array_keys($src_common) )?>'>
				<label for="<?php echo $this->get_field_id('title_link') ?>"><?php _e('Link', 'kc-essentials') ?></label>
				<?php echo kcForm::field(array(
					'type'    => 'select',
					'attr'    => array(
						'id'         => $this->get_field_id('title_link'),
						'name'       => $this->get_field_name('title_link'),
						'class'      => 'hasdep',
						'data-child' => '.chTitleLink',
						'data-scope' => 'ul'
					),
					'current' => $instance['title_link'],
					'options' => $src_common,
					'none'    => false
				)) ?>
			</li>
			<li class="chTitleLink" data-dep='meta'>
				<label for="<?php echo $this->get_field_id('title_link_meta') ?>" title="<?php _e("Fill this if you select 'Custom field' above", 'kc-essentials') ?>"><?php _e('Meta key', 'kc-essentials') ?> <small class="impo">(?)</small></label>
				<?php echo kcForm::input(array(
					'attr'    => array('id' => $this->get_field_id('title_link_meta'), 'name' => $this->get_field_name('title_link_meta')),
					'current' => $instance['title_link_meta']
				)) ?>
			</li>
		</ul>

		<h5 class="kcw-head" title="<?php _e('Show/hide', 'kc-essentials') ?>"><?php _e('Entry content', 'kc-essentials') ?></h5>
		<ul class="kcw-control-block hide-if-js">
			<li>
				<label for="<?php echo $this->get_field_id('content_src') ?>"><?php _e('Source', 'kc-essentials') ?></label>
				<?php echo kcForm::select(array(
					'attr'    => array(
						'id'         => $this->get_field_id('content_src'),
						'name'       => $this->get_field_name('content_src'),
						'class'      => 'hasdep',
						'data-child' => '.contentSrc',
						'data-scope' => 'ul'
					),
					'current' => $instance['content_src'],
					'options' => array(
						array( 'value' => 'excerpt', 'label' => __('Excerpt', 'kc-essentials') ),
						array( 'value' => 'content', 'label' => __('Full Content', 'kc-essentials') ),
						array( 'value' => 'meta',    'label' => __('Custom field', 'kc-essentials') )
					)
				)) ?>
			</li>
			<li class="contentSrc" data-dep='<?php echo json_encode(array('excerpt', 'content', 'meta')) ?>'>
				<label for="<?php echo $this->get_field_id('content_wrapper') ?>"><?php _e('Tag', 'kc-essentials') ?></label>
				<?php echo kcForm::select(array(
					'attr'    => array(
						'id'         => $this->get_field_id('content_wrapper'),
						'name'       => $this->get_field_name('content_wrapper'),
						'class'      => 'hasdep',
						'data-child' => '.contentClass',
						'data-scope' => 'ul'
					),
					'current' => $instance['content_wrapper'],
					'options' => array(
						array( 'value' => 'div',        'label' => 'div' ),
						array( 'value' => 'article',    'label' => 'article' ),
						array( 'value' => 'blockquote', 'label' => 'blockquote' )
					)
				)) ?>
			</li>
			<li class="contentClass" data-dep='<?php echo json_encode(array('div', 'article', 'blockquote')) ?>'>
				<label for="<?php echo $this->get_field_id('content_class') ?>"><?php _e('Class', 'kc-essentials') ?></label>
				<?php echo kcForm::input(array(
					'attr'    => array('id' => $this->get_field_id('content_class'), 'name' => $this->get_field_name('content_class')),
					'current' => $instance['content_class']
				)) ?>
			</li>
			<li class="contentSrc" data-dep='meta'>
				<label for="<?php echo $this->get_field_id('content_meta') ?>" title="<?php _e("Fill this if you select 'Custom field' above", 'kc-essentials') ?>"><?php _e('Meta key', 'kc-essentials') ?> <small class="impo">(?)</small></label>
				<?php echo kcForm::input(array(
					'attr'    => array('id' => $this->get_field_id('content_meta'), 'name' => $this->get_field_name('content_meta')),
					'current' => $instance['content_meta']
				)) ?>
			</li>
			<li class="contentSrc" data-dep='<?php echo json_encode(array('excerpt', 'content', 'meta')) ?>'>
				<label for="<?php echo $this->get_field_id('more_link') ?>" title="<?php _e("Fill this with some text if you want to have a 'more link' on each post", 'kc-essentials') ?>"><?php _e('More link', 'kc-essentials') ?> <small class="impo">(?)</small></label>
				<?php echo kcForm::input(array(
					'attr'    => array('id' => $this->get_field_id('more_link'), 'name' => $this->get_field_name('more_link')),
					'current' => $instance['more_link']
				)) ?>
			</li>
		</ul>

		<?php if ( !empty($image_sizes) ) { ?>
		<h5 class="kcw-head" title="<?php _e('Show/hide', 'kc-essentials') ?>"><?php _e('Thumbnail', 'kc-essentials') ?></h5>
		<ul class="kcw-control-block hide-if-js">
			<li>
				<label for="<?php echo $this->get_field_id('thumb_size') ?>"><?php _e('Size', 'kc-essentials') ?></label>
				<?php echo kcForm::field(array(
					'type'    => 'select',
					'attr'    => array(
						'id'         => $this->get_field_id('thumb_size'),
						'name'       => $this->get_field_name('thumb_size'),
						'class'      => 'hasdep',
						'data-child' => '.thumb-config',
						'data-scope' => 'ul'
					),
					'current' => $instance['thumb_size'],
					'options' => $image_sizes
				)) ?>
			</li>
			<li class="thumb-config" data-dep='<?php echo json_encode(array_keys($image_sizes)) ?>'>
				<label for="<?php echo $this->get_field_id('thumb_src') ?>"><?php _e('Source', 'kc-essentials') ?></label>
				<?php echo kcForm::field(array(
					'type'    => 'select',
					'attr'    => array(
						'id'         => $this->get_field_id('thumb_src'),
						'name'       => $this->get_field_name('thumb_src'),
						'class'      => 'hasdep',
						'data-child' => '#p-'.$this->get_field_id('thumb_meta')
					),
					'current' => $instance['thumb_src'],
					'options' => array( '' => __('Default', 'kc-essentials'), 'meta' => __('Custom field', 'kc-settings') ),
					'none'    => false
				)) ?>
			</li>
			<li id='p-<?php echo $this->get_field_id('thumb_meta') ?>' class="hide-if-js" data-dep="meta">
				<label for="<?php echo $this->get_field_id('thumb_meta') ?>" title="<?php _e("Fill this if you select 'Custom field' above", 'kc-essentials') ?>"><?php _e('Meta key', 'kc-essentials') ?> <small class="impo">(?)</small></label>
				<?php echo kcForm::input(array(
					'attr'    => array('id' => $this->get_field_id('thumb_meta'), 'name' => $this->get_field_name('thumb_meta')),
					'current' => $instance['thumb_meta']
				)) ?>
			</li>
			<li data-dep='<?php echo json_encode(array_keys($image_sizes)) ?>' class="hide-if-js thumb-config">
				<label for="<?php echo $this->get_field_id('thumb_link') ?>"><?php _e('Link', 'kc-essentials') ?></label>
				<?php echo kcForm::select(array(
					'attr'    => array('id' => $this->get_field_id('thumb_link'), 'name' => $this->get_field_name('thumb_link')),
					'current' => $instance['thumb_link'],
					'options' => array(
						array( 'value' => 'post',       'label'	=> __('Post page', 'kc-essentials') ),
						array( 'value' => 'media_page', 'label'	=> __('Attachment page', 'kc-essentials') ),
						array( 'value' => 'media_file', 'label'	=> __('Attachment file', 'kc-essentials') )
					)
				)) ?>
			</li>
		</ul>
		<?php } ?>

		<h5 class="kcw-head" title="<?php _e('Show/hide', 'kc-essentials') ?>"><?php _e('Advanced', 'kc-essentials') ?></h5>
		<ul class="kcw-control-block hide-if-js">
			<li>
				<label for="<?php echo $this->get_field_id('action_id') ?>" title="<?php _e('Please refer to the documentation about this', 'kc-essentials') ?>"><?php _e('Identifier', 'kc-essentials') ?> <small class="impo">(?)</small></label>
				<?php echo kcForm::input(array(
					'attr'    => array('id' => $this->get_field_id('action_id'), 'name' => $this->get_field_name('action_id')),
					'current' => $instance['action_id']
				)) ?>
			</li>
			<li>
				<label for="<?php echo $this->get_field_id('debug') ?>" title="<?php _e('Select Yes to view the widget options and query parameters on the frontend') ?>"><?php _e('Debug', 'kc-essentials') ?> <small class="impo">(?)</small></label>
				<?php echo kcForm::field(array(
					'type'    => 'select',
					'attr'    => array('id' => $this->get_field_id('debug'), 'name' => $this->get_field_name('debug')),
					'current' => $instance['debug'],
					'options' => kcSettings_options::$yesno,
					'none'    => false
				)) ?>
			</li>
		</ul>
	<?php }


	function sort_query_by_post_in( $sortby, $query ) {
		if ( isset($query->query['post__in']) && !empty($query->query['post__in']) && isset($query->query['orderby']) && $query->query['orderby'] == 'post__in' )
			$sortby = "find_in_set(ID, '" . implode( ',', $query->query['post__in'] ) . "')";

		return $sortby;
	}


	function _kc_get_title( $post_id, $instance ) {
		$title = '';
		switch ( $instance['title_src'] ) {
			case 'meta' :
				if ( isset($instance['title_meta']) && $instance['title_meta'] && $meta = get_post_meta($post_id, $instance['title_meta'], true) )
					$title = $meta;
			break;
			default :
				$title = get_the_title();
			break;
		}

		# Link
		if ( isset($instance['title_link']) && $instance['title_link'] ) {
			switch ( $instance['title_link'] ) {
				case 'meta' :
					if ( isset($instance['title_link_meta']) && $instance['title_link_meta'] && $meta = get_post_meta($post_id, $instance['title_link_meta'], true) )
						$link = $meta;
				break;
				default :
					$link = get_permalink();
				break;
			}

			$title = "<a href='{$link}'>{$title}</a>";
		}

		if ( !isset($instance['title_tag']) || !$instance['title_tag'] )
			return $title;

		$output = "<{$instance['title_tag']}";
		if ( $instance['title_class'] )
			$output .= " class='{$instance['title_class']}'";
		$output .= ">{$title}</{$instance['title_tag']}>\n";

		return $output;
	}


	function _kc_get_thumbnail( $post_id, $instance ) {
		if ( $instance['thumb_src'] == 'meta' && $meta = get_post_meta($post_id, $instance['thumb_meta'], true) )
			$thumb_id = $meta;
		elseif ( has_post_thumbnail() )
			$thumb_id = get_post_thumbnail_id( $post_id );

		if ( !isset($thumb_id) )
			return;

		$thumb_size = apply_filters( 'post_thumbnail_size', $instance['thumb_size'] );
		$thumb_img = wp_get_attachment_image($thumb_id, $thumb_size);

		if ( !$instance['thumb_link'] )
			return "<span class='post-thumb'>{$thumb_img}</span>\n";

		switch ( $instance['thumb_link'] ) {
			case 'post' :
				$thumb_link = get_permalink();
			break;
			case 'media_page' :
				$thumb_link = get_permalink( $thumb_id );
			break;
			case 'media_file' :
				$thumb_link = wp_get_attachment_url( $thumb_id );
			break;
		}

		return "<a href='{$thumb_link}' class='post-thumb'>{$thumb_img}</a>\n";
	}


	function _kc_get_content( $post_id, $instance ) {
		switch ( $instance['content_src'] ) {
			case 'content' :
				$output = get_the_content();
			break;
			case 'excerpt' :
				$output = get_the_excerpt();
			break;
			case 'meta' :
				if ( !empty($instance['content_meta']) )
					$output = get_post_meta( $post_id, $instance['content_meta'], true );
			break;
		}

		$output = apply_filters( 'the_content', $output );
		if ( $instance['action_id'] ) {
			$output = apply_filters( "kcw_post_content-{$instance['action_id']}", $output, $post_id );
			$output = apply_filters( 'kcw_post_content', $output, $post_id, $instance['action_id'] );
		}
		$output .= $output;

		# More link
		if ( isset($instance['more_link']) && $instance['more_link'] )
			$output .= "<a href='".get_permalink()."' class='more-link'><span>{$instance['more_link']}</span></a>\n";

		if ( !isset($instance['content_wrapper']) || !$instance['content_wrapper'] )
			return $output;

		$wrap_tag = $instance['content_wrapper'];
		if ( isset($instance['content_class']) && $instance['content_class'] )
			$wrap_tag .= " class='{$instance['content_class']}'";

		$output = "<{$wrap_tag}>\n{$output}\n</{$instance['content_wrapper']}>\n";

		return $output;
	}
}

?>
