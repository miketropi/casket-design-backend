<?php 
/**
 * API register
 */

add_action('rest_api_init', function () {
  register_rest_route('custom/v1', '/casket-upload-image', [
    'methods' => 'POST',
    'callback' => 'cbd_handle_image_upload',
    // 'permission_callback' => 'cbd_validate_upload_permissions',
  ]);
});

add_action('rest_api_init', function () {
  register_rest_route('custom/v1', '/casket-settings', [
    'methods' => 'GET',
    'callback' => 'cbd_get_casket_settings',
    // 'permission_callback' => 'cbd_validate_upload_permissions',
  ]);
});

add_action('rest_api_init', function () {
  register_rest_route('custom/v1', '/casket-save-json', [
    'methods' => 'POST',
    'callback' => 'cbd_save_json_to_file',
  ]);
});

add_action('rest_api_init', function () { 
  register_rest_route('custom/v1', '/casket-base64-image-upload', [
    'methods' => 'POST',
    'callback' => 'cbd_handle_base64_image_upload',
  ]);
});

add_action('rest_api_init', function () {
  register_rest_route('custom/v1', '/casket-create-design', [
    'methods' => 'POST',
    'callback' => 'cbd_create_casket_design_post',
  ]);
});
