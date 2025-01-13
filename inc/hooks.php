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