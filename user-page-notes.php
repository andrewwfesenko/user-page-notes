<?php
/**
 * Plugin Name: User Page Notes
 * Plugin URI: https://yourwebsite.com/user-page-notes
 * Description: Allows logged-in users to add, view, and delete personal notes on any page using modern AJAX functionality.
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://yourwebsite.com
 * Text Domain: user-page-notes
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('UPN_PLUGIN_URL', plugin_dir_url(__FILE__));
define('UPN_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('UPN_VERSION', '1.0.0');

// Autoloader for plugin classes
spl_autoload_register(function ($class) {
    if (strpos($class, 'UPN\\') === 0) {
        $class_file = str_replace(['UPN\\', '\\'], ['', '/'], $class);
        $file_path = UPN_PLUGIN_PATH . 'src/' . $class_file . '.php';
        if (file_exists($file_path)) {
            require_once $file_path;
        }
    }
});

// Main plugin class
final class UserPageNotesPlugin {
    
    private static $instance = null;
    
    private function __construct() {
        $this->init();
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function init() {
        // Hook into WordPress
        add_action('plugins_loaded', [$this, 'loadTextDomain']);
        add_action('init', [$this, 'initPlugin']);
        
        // Activation and deactivation hooks
        register_activation_hook(__FILE__, [$this, 'activate']);
        register_deactivation_hook(__FILE__, [$this, 'deactivate']);
    }
    
    public function loadTextDomain() {
        load_plugin_textdomain(
            'user-page-notes',
            false,
            dirname(plugin_basename(__FILE__)) . '/languages'
        );
    }
    
    public function initPlugin() {
        // Initialize plugin components
        new UPN\Core\Database();
        new UPN\Core\Assets();
        new UPN\Core\Ajax();
        new UPN\Frontend\NotesDisplay();
        
        if (is_admin()) {
            new UPN\Admin\Settings();
        }
    }
    
    public function activate() {
        // Create database tables and set up initial configuration
        $database = new UPN\Core\Database();
        $database->createTables();
        
        // Set default options
        add_option('upn_version', UPN_VERSION);
        add_option('upn_enabled', 1);
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    public function deactivate() {
        // Clean up if needed
        flush_rewrite_rules();
    }
}

// Initialize the plugin
UserPageNotesPlugin::getInstance(); 