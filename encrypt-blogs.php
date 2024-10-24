<?php
/**
 * Plugin Name: Encrypt Blogs
 * Plugin URI: https://github.com/bokumin/encrypt-blogs
 * Description: A plugin to encrypt blog content with time-based encryption using either PHP or GPG encryption
 * Version: 1.0.2
 * Author:bokumin 
 * Author URI:https://bokumin45.server-on.net 
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: encrypt-blogs
 */

if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('ENCRYPT_BLOGS_VERSION', '1.0.0');
define('ENCRYPT_BLOGS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('ENCRYPT_BLOGS_PLUGIN_URL', plugin_dir_url(__FILE__));

// Include required files
require_once ENCRYPT_BLOGS_PLUGIN_DIR . 'includes/class-encrypt-blogs.php';
require_once ENCRYPT_BLOGS_PLUGIN_DIR . 'includes/class-encrypt-blogs-admin.php';
require_once ENCRYPT_BLOGS_PLUGIN_DIR . 'includes/class-encrypt-blogs-block.php';
require_once ENCRYPT_BLOGS_PLUGIN_DIR . 'includes/class-encrypt-blogs-encryptor.php';


// Initialize the plugin
function encrypt_blogs_init() {
    $plugin = new Encrypt_Blogs();
    $plugin->run();
}
add_action('plugins_loaded', 'encrypt_blogs_init');


// Activation hook
register_activation_hook(__FILE__, 'encrypt_blogs_activate');
function encrypt_blogs_activate() {
    // Initialize default settings
    if (!get_option('encrypt_blogs_settings')) {
        update_option('encrypt_blogs_settings', array(
            'encryption_method' => 'php',
            'php_passphrase' => '',
            'gpg_public_key' => '',
            'gpg_private_key' => '',
            'gpg_passphrase' => ''
        ));
    }
}


// Deactivation hook
register_deactivation_hook(__FILE__, 'encrypt_blogs_deactivate');
function encrypt_blogs_deactivate() {
    // Cleanup if necessary
}


// Uninstall hook
register_uninstall_hook(__FILE__, 'encrypt_blogs_uninstall');
function encrypt_blogs_uninstall() {
    delete_option('encrypt_blogs_settings');
}

