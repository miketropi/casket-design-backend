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