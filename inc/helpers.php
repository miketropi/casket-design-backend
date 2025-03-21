<?php 
/**
 * Helpers
 */

function cbd_validate_upload_permissions() {
  return current_user_can('upload_files');
} 

function cbd_handle_image_upload($request) { 
  // Check if a file is provided
  if (empty($_FILES['file'])) {
      return new WP_Error('no_file', 'No file was provided.', ['status' => 400]);
  }
  require_once(ABSPATH.'wp-admin/includes/file.php');
  $file = $_FILES['file'];

  // Handle the upload
  $upload = wp_handle_upload($file, ['test_form' => false]);

  if (isset($upload['error'])) {
    return new WP_Error('upload_failed', $upload['error'], ['status' => 500]);
  }

  // Create an attachment post in the database
  $attachment_id = wp_insert_attachment([
    'guid'           => $upload['url'],
    'post_mime_type' => $upload['type'],
    'post_title'     => basename($upload['file']),
    'post_content'   => '',
    'post_status'    => 'inherit',
  ], $upload['file']);

  if (is_wp_error($attachment_id)) {
    return $attachment_id;
  }

  // Generate attachment metadata and save
  require_once ABSPATH . 'wp-admin/includes/image.php';
  $attach_data = wp_generate_attachment_metadata($attachment_id, $upload['file']);
  wp_update_attachment_metadata($attachment_id, $attach_data);

  return [
    'id'   => $attachment_id,
    'url'  => $upload['url'],
    'type' => $upload['type'],
  ];
}

function cbd_get_casket_settings() {
  return [
    'media_picker_default' => get_field('media_picker_default', 'option'),
  ];
}

function cbd_save_json_to_file() {
  $json_data = $data = json_decode(file_get_contents('php://input'), true);
  if ($json_data === false) {
    return new WP_Error('json_encode_failed', 'Failed to encode JSON data');
  }
  $filename = uniqid('casket_design_') . '_' . time() . '_' . rand(1000, 9999) . '.json';

  // Get WordPress upload directory
  $upload_dir = wp_upload_dir();
  
  // Ensure filename has .json extension
  if (!str_ends_with($filename, '.json')) {
    $filename .= '.json';
  }

  // Create full file path
  $file_path = $upload_dir['basedir'] . '/' . $filename;

  // Encode data to JSON if not already a string
  if (!is_string($json_data)) {
    $json_data = json_encode($json_data);
  }

  // Write JSON to file
  $result = file_put_contents($file_path, $json_data);

  if ($result === false) {
    return new WP_Error('json_save_failed', 'Failed to save JSON file');
  }

  return [
    'path' => $file_path,
    'url' => $upload_dir['baseurl'] . '/' . $filename,
    'size' => round($result / (1024 * 1024), 2) . ' MB'
  ];
}

function cbd_handle_base64_image_upload() {
  $json_data = $data = json_decode(file_get_contents('php://input'), true);
  if ($json_data === false) {
    return new WP_Error('json_encode_failed', 'Failed to encode JSON data');
  }

  // Check if the input is empty
  if (empty($json_data['base64_string'])) {
    return new WP_Error('no_data', 'No data was provided.', ['status' => 400]);
  }

  $base64_string = $json_data['base64_string'];
  if (empty($base64_string)) {
    return new WP_Error('empty_input', 'No base64 string provided');
  }

  // Get the base64 content
  $decoded_data = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $base64_string));
  
  if ($decoded_data === false) {
    return new WP_Error('decode_failed', 'Failed to decode base64 string');
  }

  // Get WordPress upload directory
  $upload_dir = wp_upload_dir();

  // Generate unique filename
  $filename = uniqid('casket_image_') . '_' . time() . '_' . rand(1000, 9999) . '.jpg';
  
  // Create full file path
  $file_path = $upload_dir['basedir'] . '/' . $filename;

  // Save the image file
  $result = file_put_contents($file_path, $decoded_data);

  if ($result === false) {
    return new WP_Error('save_failed', 'Failed to save image file');
  }

  // Prepare file information
  $file_type = wp_check_filetype($filename);
  
  // Insert into WordPress media library
  $attachment = array(
    'post_mime_type' => $file_type['type'],
    'post_title' => sanitize_file_name($filename),
    'post_content' => '',
    'post_status' => 'inherit'
  );

  $attach_id = wp_insert_attachment($attachment, $file_path);

  if (is_wp_error($attach_id)) {
    return $attach_id;
  }

  // Generate attachment metadata
  require_once(ABSPATH . 'wp-admin/includes/image.php');
  $attach_data = wp_generate_attachment_metadata($attach_id, $file_path);
  wp_update_attachment_metadata($attach_id, $attach_data);

  return array(
    'id' => $attach_id,
    'url' => $upload_dir['baseurl'] . '/' . $filename,
    'path' => $file_path,
    'type' => $file_type['type']
  );
}

function cbd_create_casket_design_post() {
  $json_data = $data = json_decode(file_get_contents('php://input'), true);
  if ($json_data === false) {
    return new WP_Error('json_encode_failed', 'Failed to encode JSON data');
  }
  
  $post_id = wp_insert_post([
    'post_type' => 'casket_editor',
    'post_title' => $json_data['post_title'],
    'post_content' => $json_data['post_content'],
    'post_status' => 'publish'
  ]);

  if (is_wp_error($post_id)) {
    return new WP_Error('post_creation_failed', 'Failed to create casket design post');
  }

  // Add meta fields
  update_post_meta($post_id, '_casket_firstname', $json_data['casket_firstname']);
  update_post_meta($post_id, '_casket_lastname', $json_data['casket_lastname']);
  update_post_meta($post_id, '_casket_email', $json_data['casket_email']);

  // casket_design_data
  update_post_meta($post_id, '_casket_design_data', $json_data['casket_design_data']);

  // casket_lib
  update_post_meta($post_id, '_casket_lib', $json_data['casket_lib']);

  // casket_right
  update_post_meta($post_id, '_casket_right', $json_data['casket_right']);

  // casket_left
  update_post_meta($post_id, '_casket_left', $json_data['casket_left']);

  // casket_bottom
  update_post_meta($post_id, '_casket_bottom', $json_data['casket_bottom']);

  // casket_top
  update_post_meta($post_id, '_casket_top', $json_data['casket_top']); 

  // casket_images
  update_post_meta($post_id, '_casket_images', $json_data['casket_images']);

  do_action('cbd_create_casket_design_post', $post_id);

  return [
    'success' => true,
    'post_id' => $post_id,
    'message' => 'Casket design post created successfully',
  ]; 
}

/**
 * Get casket design by ID
 * 
 * @param int $design_id The ID of the casket design post
 * @return array|WP_Error The casket design data or error
 */
function cbd_get_casket_design_by_id($design_id) {
  $post = get_post($design_id);
  
  if (!$post || $post->post_type !== 'casket_editor') {
    return new WP_Error('invalid_design', 'Invalid casket design ID or design not found');
  }
  
  $_casket_design_data = get_post_meta($post->ID, '_casket_design_data', true);
  $json_data = json_decode(file_get_contents($_casket_design_data));

  $design_data = [
    'post_id' => $post->ID,
    'post_title' => $post->post_title,
    'post_content' => $post->post_content,
    'casket_firstname' => get_post_meta($post->ID, '_casket_firstname', true),
    'casket_lastname' => get_post_meta($post->ID, '_casket_lastname', true),
    'casket_email' => get_post_meta($post->ID, '_casket_email', true),
    'casket_design_data' => $json_data,
    'casket_lib' => get_post_meta($post->ID, '_casket_lib', true),
    'casket_right' => get_post_meta($post->ID, '_casket_right', true),
    'casket_left' => get_post_meta($post->ID, '_casket_left', true),
    'casket_bottom' => get_post_meta($post->ID, '_casket_bottom', true),
    'casket_top' => get_post_meta($post->ID, '_casket_top', true),
    'casket_images' => get_post_meta($post->ID, '_casket_images', true),
  ];
  
  return $design_data;
}

/**
 * Send email notification to admin when a new casket design is created
 * 
 * @param int $post_id The ID of the newly created casket design post
 * @return bool Whether the email was sent successfully
 */
function cbd_send_admin_notification_for_new_design($post_id) {
  $post = get_post($post_id);
  
  if (!$post || $post->post_type !== 'casket_editor') {
    return false;
  }
  
  $admin_email = get_option('admin_email');
  $site_name = get_bloginfo('name');
  
  $firstname = get_post_meta($post_id, '_casket_firstname', true);
  $lastname = get_post_meta($post_id, '_casket_lastname', true);
  $customer_email = get_post_meta($post_id, '_casket_email', true);
  
  $subject = sprintf('[%s] New Casket Design Created', $site_name);
  
  $message = sprintf(
    "A new casket design has been created on your website.\n\n" .
    "Design Details:\n" .
    "- Design ID: %d\n" .
    "- Design Title: %s\n" .
    "- Customer Name: %s %s\n" .
    "- Customer Email: %s\n\n" .
    "You can view the full design at: %s",
    $post_id,
    $post->post_title,
    $firstname,
    $lastname,
    $customer_email,
    admin_url('post.php?post=' . $post_id . '&action=edit')
  );
  
  $headers = array('Content-Type: text/plain; charset=UTF-8');
  
  return wp_mail($admin_email, $subject, $message, $headers);
}

/**
 * Send email notification to customer when they submit a new casket design
 * 
 * @param int $post_id The ID of the newly created casket design post
 * @return bool Whether the email was sent successfully
 */
function cbd_send_customer_notification_for_new_design($post_id) {
  $post = get_post($post_id);
  
  if (!$post || $post->post_type !== 'casket_editor') {
    return false;
  }
  
  $site_name = get_bloginfo('name');
  
  $firstname = get_post_meta($post_id, '_casket_firstname', true);
  $lastname = get_post_meta($post_id, '_casket_lastname', true);
  $customer_email = get_post_meta($post_id, '_casket_email', true);
  
  if (empty($customer_email)) {
    return false;
  }
  
  $subject = sprintf('Your Casket Design Submission - %s', $site_name);
  
  $message = sprintf(
    "Dear %s %s,\n\n" .
    "Thank you for submitting your casket design with %s.\n\n" .
    "Your design has been received and is being processed. Your design reference number is: %d\n\n" .
    "Design Details:\n" .
    "- Design Title: %s\n" .
    "- Submission Date: %s\n\n" .
    "If you have any questions about your design, please contact us.\n\n" .
    "Thank you,\n" .
    "%s Team",
    $firstname,
    $lastname,
    $site_name,
    $post_id,
    $post->post_title,
    date_i18n(get_option('date_format'), strtotime($post->post_date)),
    $site_name
  );
  
  $headers = array('Content-Type: text/plain; charset=UTF-8');
  
  return wp_mail($customer_email, $subject, $message, $headers);
}
