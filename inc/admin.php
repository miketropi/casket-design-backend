<?php 
/**
 * Admin
 */

add_action('init', 'cbd_register_casket_editor_post_type');

function cbd_register_casket_editor_post_type() {
  $labels = array(
    'name'               => 'Casket Editor',
    'singular_name'      => 'Casket Editor',
    'menu_name'          => 'Casket Editor',
    'add_new'           => 'Add New',
    'add_new_item'      => 'Add New Casket Design',
    'edit_item'         => 'Edit Casket Design',
    'new_item'          => 'New Casket Design',
    'view_item'         => 'View Casket Design',
    'search_items'      => 'Search Casket Designs',
    'not_found'         => 'No casket designs found',
    'not_found_in_trash'=> 'No casket designs found in trash',
  );

  $args = array(
    'labels'              => $labels,
    'public'              => true,
    'has_archive'         => true,
    'publicly_queryable'  => true,
    'show_ui'             => true,
    'show_in_menu'        => true,
    'query_var'           => true,
    'rewrite'             => array('slug' => 'casket-editor'),
    'capability_type'     => 'post',
    'hierarchical'        => false,
    'menu_position'       => 5,
    'menu_icon'           => 'dashicons-edit',
    'supports'            => array('title', 'editor', 'thumbnail'),
  );

  register_post_type('casket_editor', $args);
}

add_action('add_meta_boxes', 'cbd_add_casket_design_meta_box');
add_action('save_post', 'cbd_save_casket_design_meta_box');

function cbd_add_casket_design_meta_box() {
  add_meta_box(
    'casket_design_meta_box', // Unique ID
    'Casket Design Details', // Box title
    'cbd_render_casket_design_meta_box', // Content callback, must be of type callable
    'casket_editor' // Post type
  );
}

function cbd_render_casket_design_meta_box($post) {
  // Add nonce for security
  wp_nonce_field('cbd_casket_design_meta_box', 'cbd_casket_design_meta_box_nonce');

  // Get existing values
  $design_data = get_post_meta($post->ID, '_casket_design_data', true);
  $images = get_post_meta($post->ID, '_casket_images', true);
  if (!is_array($images)) {
    $images = array(''); // Initialize with one empty field
  }
  ?>
  <div class="casket-design-fields">

    <p>
      <label for="casket_firstname"><strong>First Name:</strong></label><br>
      <input type="text" id="casket_firstname" name="casket_firstname" value="<?php echo esc_attr(get_post_meta($post->ID, '_casket_firstname', true)); ?>" style="width: 80%;">
    </p>

    <p>
      <label for="casket_lastname"><strong>Last Name:</strong></label><br>
      <input type="text" id="casket_lastname" name="casket_lastname" value="<?php echo esc_attr(get_post_meta($post->ID, '_casket_lastname', true)); ?>" style="width: 80%;">
    </p>

    <p>
      <label for="casket_email"><strong>Email:</strong></label><br>
      <input type="email" id="casket_email" name="casket_email" value="<?php echo esc_attr(get_post_meta($post->ID, '_casket_email', true)); ?>" style="width: 80%;">
    </p>

    <hr style="margin: 20px 0;" />

    <p>
      <label for="casket_design_data"><strong>Casket Design Data:</strong></label><br>
      <input type="text" id="casket_design_data" name="casket_design_data" value="<?php echo $design_data; ?>" style="width: 80%;">
    </p>
    <hr style="margin: 20px 0;" />
    <p>
      <label for="casket_lib"><strong>Lib:</strong></label><br>
      <input type="text" id="casket_lib" name="casket_lib" value="<?php echo esc_attr(get_post_meta($post->ID, '_casket_lib', true)); ?>" style="width: 80%;">
    </p>

    <p>
      <label for="casket_right"><strong>Right:</strong></label><br>
      <input type="text" id="casket_right" name="casket_right" value="<?php echo esc_attr(get_post_meta($post->ID, '_casket_right', true)); ?>" style="width: 80%;">
    </p>

    <p>
      <label for="casket_left"><strong>Left:</strong></label><br>
      <input type="text" id="casket_left" name="casket_left" value="<?php echo esc_attr(get_post_meta($post->ID, '_casket_left', true)); ?>" style="width: 80%;">
    </p>

    <p>
      <label for="casket_bottom"><strong>Bottom:</strong></label><br>
      <input type="text" id="casket_bottom" name="casket_bottom" value="<?php echo esc_attr(get_post_meta($post->ID, '_casket_bottom', true)); ?>" style="width: 80%;">
    </p>

    <p>
      <label for="casket_top"><strong>Top:</strong></label><br>
      <input type="text" id="casket_top" name="casket_top" value="<?php echo esc_attr(get_post_meta($post->ID, '_casket_top', true)); ?>" style="width: 80%;">
    </p>

    <hr style="margin: 20px 0;" />
    
    <div class="casket-images-wrapper">
      <p><strong>Images Used:</strong></p>
      <div id="casket-images-container">
        <?php foreach ($images as $index => $image) : ?>
          <p class="image-field">
            <input type="text" name="casket_images[]" value="<?php echo esc_attr($image); ?>" style="width: 80%;" /><button type="button" class="button remove-image" <?php echo ($index === 0) ? 'style="display:none;"' : ''; ?>>Remove</button>
          </p>
        <?php endforeach; ?>
      </div>
      <p><button type="button" class="button add-image">Add Another Image</button></p>
    </div>
  </div>

  <style>
    .casket-images-wrapper .image-field > * {
      vertical-align: middle !important;
    }
  </style>

  <script>
  jQuery(document).ready(function($) {
    // Add new image field
    $('.add-image').on('click', function() {
      var field = '<p class="image-field">' +
                  '<input type="text" name="casket_images[]" value="" style="width: 80%;" />' +
                  '<button type="button" class="button remove-image">Remove</button>' +
                  '</p>';
      $('#casket-images-container').append(field);
    });

    // Remove image field
    $('#casket-images-container').on('click', '.remove-image', function() {
      $(this).parent('.image-field').remove();
    });
  });
  </script>
  <?php
}

function cbd_save_casket_design_meta_box($post_id) {
  // Check if our nonce is set
  if (!isset($_POST['cbd_casket_design_meta_box_nonce'])) {
    return;
  }

  // Verify the nonce
  if (!wp_verify_nonce($_POST['cbd_casket_design_meta_box_nonce'], 'cbd_casket_design_meta_box')) {
    return;
  }

  // If this is an autosave, don't do anything
  if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
    return;
  }

  // Check user permissions
  if (!current_user_can('edit_post', $post_id)) {
    return;
  }

  // Save the design data
  if (isset($_POST['casket_design_data'])) {
    update_post_meta($post_id, '_casket_design_data', sanitize_textarea_field($_POST['casket_design_data']));
  }

  if (isset($_POST['casket_lib'])) {
    update_post_meta($post_id, '_casket_lib', sanitize_text_field($_POST['casket_lib']));
  }

  if (isset($_POST['casket_right'])) {
    update_post_meta($post_id, '_casket_right', sanitize_text_field($_POST['casket_right']));
  }

  if (isset($_POST['casket_left'])) {
    update_post_meta($post_id, '_casket_left', sanitize_text_field($_POST['casket_left']));
  }

  if (isset($_POST['casket_bottom'])) { 
    update_post_meta($post_id, '_casket_bottom', sanitize_text_field($_POST['casket_bottom']));
  }

  if (isset($_POST['casket_top'])) {
    update_post_meta($post_id, '_casket_top', sanitize_text_field($_POST['casket_top']));
  }

  if (isset($_POST['casket_firstname'])) {
    update_post_meta($post_id, '_casket_firstname', sanitize_text_field($_POST['casket_firstname']));
  }

  if (isset($_POST['casket_lastname'])) {
    update_post_meta($post_id, '_casket_lastname', sanitize_text_field($_POST['casket_lastname']));
  }

  if (isset($_POST['casket_email'])) {
    update_post_meta($post_id, '_casket_email', sanitize_email($_POST['casket_email']));
  }

  // Save the images array
  if (isset($_POST['casket_images'])) {
    $images = array_filter($_POST['casket_images']); // Remove empty values
    $sanitized_images = array_map('sanitize_text_field', $images);
    update_post_meta($post_id, '_casket_images', $sanitized_images);
  }
}
