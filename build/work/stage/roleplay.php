<?php
/**
 * Plugin Name: Roleplay
 * Description: Wordpress User Role Editor
 * Version: 0.1.0
 * Author: abrayall
 * Author URI: https://brayall.com
 * License: GPL-2.0+
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Requires at least: 5.0
 * Requires PHP: 7.4
 */
if (!defined('ABSPATH')) {
    exit;
}

define('ROLEPLAY_VERSION', '1.0.0');
define('ROLEPLAY_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('ROLEPLAY_PLUGIN_URL', plugin_dir_url(__FILE__));

require_once ROLEPLAY_PLUGIN_DIR . 'includes/class-roleplay-manager.php';
require_once ROLEPLAY_PLUGIN_DIR . 'includes/class-roleplay-admin.php';

function roleplay_init() {
    $admin = new Roleplay_Admin();
    $admin->init();
}
add_action('plugins_loaded', 'roleplay_init');
