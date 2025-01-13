<?php
/*
 * Plugin Name:       Casket Design Backend
 * Plugin URI:        #
 * Description:       Casket design backend API.
 * Version:           1.0.0
 * Requires at least: 6.7
 * Requires PHP:      7.2
 * Author:            Mike
 * Author URI:        #
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Update URI:        #
 * Text Domain:       casket-design-backend
 * Domain Path:       /languages
 */

{
  /**
   * Define
   */
  define('CDB_VER', '1.0.0');
  define('CDB_DIR', plugin_dir_path( __FILE__ ));
  define('CDB_URI', plugin_dir_url( __FILE__ ));
}

{
  /**
   * Inc
   */
  require_once( CDB_DIR . '/inc/helpers.php' );
  require_once( CDB_DIR . '/inc/api.php' );
}

function cdb_add_cors_headers() {
  header("Access-Control-Allow-Origin: *");
}
add_action('init', 'cdb_add_cors_headers');