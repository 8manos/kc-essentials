<?php

class kcsst_Walker_Category_Radiolist extends Walker {
  var $tree_type = 'category';
  var $db_fields = array ('parent' => 'parent', 'id' => 'term_id'); //TODO: decouple this

  function start_lvl(&$output, $depth, $args) {
    $indent = str_repeat("\t", $depth);
    $output .= "$indent<ul class='children'>\n";
  }

  function end_lvl(&$output, $depth, $args) {
    $indent = str_repeat("\t", $depth);
    $output .= "$indent</ul>\n";
  }

  function start_el(&$output, $category, $depth, $args) {
    extract($args);
    if ( empty($taxonomy) )
      $taxonomy = 'category';

    if ( $taxonomy == 'category' )
      $name = 'post_category';
    else
      $name = 'tax_input['.$taxonomy.']';

    $output .= "\n<li id='{$taxonomy}-{$category->term_id}'>" . '<label class="selectit"><input value="' . $category->term_id . '" type="radio" name="'.$name.'" id="in-'.$taxonomy.'-' . $category->term_id . '"' . checked( in_array( $category->term_id, $selected_cats ), true, false ) . disabled( empty( $args['disabled'] ), false, false ) . ' /> ' . esc_html( apply_filters('the_category', $category->name )) . '</label>';
  }

  function end_el(&$output, $category, $depth, $args) {
    $output .= "</li>\n";
  }
}



function kcsst_unique_taxonomies() {
  $unique_taxonomies = apply_filters( 'kcsst_unique_taxonomies', array_keys( kcEssentials::$settings['taxonomies']['unique'] ) );
  if ( empty($unique_taxonomies) )
    return;

  foreach ( $unique_taxonomies as $tax_name ) {
    $taxonomy = get_taxonomy( $tax_name );
    if ( !$taxonomy->show_ui || empty($taxonomy->object_type) )
      continue;

    foreach ( $taxonomy->object_type as $pt ) {
      # Remove default metabox
      if ( $taxonomy->hierarchical ) {
	remove_meta_box( "{$tax_name}div", $pt, 'side' );
      } else {
	remove_meta_box( "tagsdiv-{$tax_name}", $pt, 'side' );
      }

      # Add our own
      add_meta_box( "unique-{$tax_name}-div", $taxonomy->labels->singular_name, 'kcsst_unique_taxonomies_metabox', $pt, 'side', 'low', array('taxonomy' => $tax_name) );
    }
  }
}


function kcsst_terms_radiolist( $post_id, $taxonomy, $echo = true ) {
  $walker = new kcsst_Walker_Category_Radiolist;
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


function kcsst_unique_taxonomies_metabox( $post, $box ) {
  if ( !isset($box['args']) || !is_array($box['args']) )
    $args = array();
  else
    $args = $box['args'];

  $defaults = array('taxonomy' => 'category');
  extract( wp_parse_args($args, $defaults), EXTR_SKIP );
  $tax = get_taxonomy($taxonomy);

  ?>
  <div id="taxonomy-<?php echo $taxonomy; ?>" class="categorydiv">
    <?php
      $name = ( $taxonomy == 'category' ) ? 'post_category' : 'tax_input[' . $taxonomy . ']';
      echo "<input type='hidden' name='{$name}' value='0' />"; // Allows for an empty term set to be sent. 0 is an invalid Term ID and will be ignored by empty() checks.
    ?>
    <ul id="<?php echo $taxonomy; ?>checklist" class="list:<?php echo $taxonomy?> categorychecklist form-no-clear">
      <?php kcsst_terms_radiolist( $post->ID, $taxonomy ) ?>
    </ul>
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
<?php }

?>
