<?php

class kcEssentials_uniquetax {
  public static $data;


  public static function init() {
    if ( !isset(kcEssentials::$data['settings']['general']['uniquetax'])
					|| !is_array(kcEssentials::$data['settings']['general']['uniquetax'])
					|| empty(kcEssentials::$data['settings']['general']['uniquetax']) )
      return false;

    self::$data['taxonomies'] = kcEssentials::$data['settings']['general']['uniquetax'];
		add_action( 'add_meta_boxes', array(__CLASS__, '_create_meta_box'), 11, 2 );
  }


	# Create metabox
	public static function _create_meta_box( $post_type, $post ) {
    foreach ( self::$data['taxonomies'] as $tax_name ) {
      if ( !taxonomy_exists( $tax_name ) )
        continue;

      $tax_object = get_taxonomy( $tax_name );
      if ( !$tax_object->hierarchical || !$tax_object->show_ui || !in_array($post_type, $tax_object->object_type) )
        continue;

      remove_meta_box( "{$tax_name}div", $post_type, 'side' );
      add_meta_box( "unique-{$tax_name}-div", $tax_object->label, array(__CLASS__, '_fill_meta_box'), $post_type, 'side', 'low', $tax_name );
    }
	}


  # Fill em
  public static function _fill_meta_box( $post, $box ) {
    $taxonomy = $box['args'];
    $tax = get_taxonomy($taxonomy); ?>
    <div id="taxonomy-<?php echo $taxonomy; ?>" class="categorydiv">
      <?php
        $name = ( $taxonomy == 'category' ) ? 'post_category' : 'tax_input[' . $taxonomy . ']';
        echo "<input type='hidden' name='{$name}' value='0' />"; // Allows for an empty term set to be sent. 0 is an invalid Term ID and will be ignored by empty() checks.
      ?>
      <div class="tabs-panel">
      <ul id="<?php echo $taxonomy; ?>checklist" class="list:<?php echo $taxonomy?> categorychecklist form-no-clear">
        <?php self::_term_list( $post->ID, $taxonomy ) ?>
      </ul>
      </div>
    <?php if ( !current_user_can($tax->cap->assign_terms) ) { ?>
      <p><em><?php _e('You cannot modify this taxonomy.'); ?></em></p>
    <?php } ?>
    <?php if ( current_user_can($tax->cap->edit_terms) ) { ?>
      <div id="<?php echo $taxonomy; ?>-adder" class="wp-hidden-children">
        <h4><a id="<?php echo $taxonomy; ?>-add-toggle" href="#<?php echo $taxonomy; ?>-add" class="hide-if-no-js" tabindex="3"><?php printf( __( '+ %s' ), $tax->labels->add_new_item ); ?></a></h4>
        <p id="<?php echo $taxonomy; ?>-add" class="category-add wp-hidden-child">
        <label class="screen-reader-text" for="new<?php echo $taxonomy; ?>"><?php echo $tax->labels->add_new_item; ?></label>
        <input type="text" name="new<?php echo $taxonomy; ?>" id="new<?php echo $taxonomy; ?>" class="form-required form-input-tip" value="<?php echo esc_attr( $tax->labels->new_item_name ); ?>" tabindex="3" aria-required="true"/>
        <label class="screen-reader-text" for="new<?php echo $taxonomy; ?>_parent">
          <?php echo $tax->labels->parent_item_colon; ?>
        </label>
        <input type="button" id="<?php echo $taxonomy; ?>-add-submit" class="add:<?php echo $taxonomy ?>checklist:<?php echo $taxonomy ?>-add button category-add-sumbit" value="<?php echo esc_attr( $tax->labels->add_new_item ); ?>" tabindex="3" />
        <?php wp_nonce_field( 'add-'.$taxonomy, '_ajax_nonce-add-'.$taxonomy, false ); ?>
        <span id="<?php echo $taxonomy; ?>-ajax-response"></span>
        </p>
      </div>
    <?php } ?>
    </div>
  <?php
  }


  private static function _term_list( $post_id, $taxonomy, $echo = true ) {
    $walker = new kcEssentials_uniquetax_Walker;
    $args = array(
      'descendants_and_self' => 0,
      'selected_cats' => wp_get_object_terms($post_id, $taxonomy, array('fields' => 'ids')),
      'popular_cats' => array(),
      'walker' => null,
      'taxonomy' => $taxonomy,
      'checked_ontop' => false
    );
    $terms = get_terms( $taxonomy, array('hide_empty' => false) );

    $output = call_user_func_array(array(&$walker, 'walk'), array($terms, 0, $args));

    if ( $echo )
      echo $output;
    else
      return $output;
  }

}



class kcEssentials_uniquetax_Walker extends Walker {
  var $tree_type = 'category';
  var $db_fields = array ('parent' => 'parent', 'id' => 'term_id'); //TODO: decouple this


  function start_lvl(&$output, $depth, $args) {
    $indent = str_repeat("\t", $depth);
    $output .= "{$indent}<ul class='children'>\n";
  }


  function end_lvl(&$output, $depth, $args) {
    $indent = str_repeat("\t", $depth);
    $output .= "{$indent}</ul>\n";
  }


  function start_el(&$output, $category, $depth, $args) {
    extract($args);
    if ( empty($taxonomy) )
      $taxonomy = 'category';

    if ( $taxonomy == 'category' )
      $name = 'post_category[]';
    else
      $name = 'tax_input['.$taxonomy.'][]';

    $output .= "\n<li id='{$taxonomy}-{$category->term_id}'>\n";
    $output .= "\t<label class='selectit'>";
    $output .= "<input value='{$category->term_id}' type='radio' name='{$name}' id='in-{$taxonomy}-{$category->term_id}'" .checked( in_array( $category->term_id, $selected_cats ), true, false ) . disabled( empty( $args['disabled'] ), false, false ) . ' />';
    $output .= esc_html( apply_filters('the_category', $category->name )) . '</label>';
  }


  function end_el(&$output, $category, $depth, $args) {
    $output .= "</li>\n";
  }
}

?>
