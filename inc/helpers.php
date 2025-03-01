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
