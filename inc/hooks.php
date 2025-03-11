<?php 
/**
 * 
 */

add_action( 'init', function() {
  if(!isset($_GET['image_source'])) return;
  $name = $_GET['image_source'];
  $fp = fopen($name, 'rb');

  // send the right headers
  header("Access-Control-Allow-Origin: *");
  header('Cache-Control: no-cache, no-store, max-age=0, must-revalidate');
  header('Pragma: no-cache');
  header("Content-Type: image/jpg");
  /* header("Content-Length: " . filesize($name)); */

  // dump the picture and stop the script
  fpassthru($fp);
  exit;
} ); 

// Hook the notification function to the casket design creation action
add_action('cbd_create_casket_design_post', 'cbd_send_admin_notification_for_new_design');
// Hook the customer notification function to the casket design creation action
add_action('cbd_create_casket_design_post', 'cbd_send_customer_notification_for_new_design');