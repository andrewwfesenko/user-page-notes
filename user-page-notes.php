<?php
/**
 * Plugin Name: User Page Notes
 * Plugin URI: https://github.com/andrewwfesenko/user-page-notes
 * Description: Allows logged-in users to add personal notes to any page using modern AJAX functionality. Built with PHP 8+, WordPress 6+, and modern JavaScript.
 * Version: 1.0.0
 * Author: Andrew Fesenko
 * Author URI: https://github.com/andrewwfesenko
 * Text Domain: user-page-notes
 * Domain Path: /languages
 * Requires at least: 6.0
 * Tested up to: 6.4
 * Requires PHP: 8.0
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Network: false
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Check minimum requirements
if (version_compare(PHP_VERSION, '8.0', '<')) {
    add_action('admin_notices', function() {
        echo '<div class="notice notice-error"><p>';
        echo esc_html__('User Page Notes requires PHP 8.0 or higher. Please upgrade your PHP version.', 'user-page-notes');
        echo '</p></div>';
    });
    return;
}

if (version_compare(get_bloginfo('version'), '6.0', '<')) {
    add_action('admin_notices', function() {
        echo '<div class="notice notice-error"><p>';
        echo esc_html__('User Page Notes requires WordPress 6.0 or higher. Please upgrade your WordPress installation.', 'user-page-notes');
        echo '</p></div>';
    });
    return;
}

// Define plugin constants
define('UPN_PLUGIN_URL', plugin_dir_url(__FILE__));
define('UPN_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('UPN_VERSION', '1.0.0');
define('UPN_MIN_PHP', '8.0');
define('UPN_MIN_WP', '6.0');

// Autoloader for plugin classes using modern PHP 8+ syntax
spl_autoload_register(function (string $class): void {
    if (!str_starts_with($class, 'UPN\\')) {
        return;
    }
    
    $class_file = str_replace(['UPN\\', '\\'], ['', '/'], $class);
    $file_path = UPN_PLUGIN_PATH . 'src/' . $class_file . '.php';
    
    if (file_exists($file_path)) {
        require_once $file_path;
    }
});

// Main plugin class with modern PHP 8+ features
final class UserPageNotesPlugin {
    
    private static ?self $instance = null;
    
    private function __construct() {
        $this->init();
    }
    
    public static function getInstance(): self {
        return self::$instance ??= new self();
    }
    
    private function init(): void {
        // Hook into WordPress
        add_action('plugins_loaded', $this->loadTextDomain(...));
        add_action('init', $this->initPlugin(...));
        
        // Activation and deactivation hooks
        register_activation_hook(__FILE__, $this->activate(...));
        register_deactivation_hook(__FILE__, $this->deactivate(...));
        
        // Add plugin action links
        add_filter('plugin_action_links_' . plugin_basename(__FILE__), $this->addActionLinks(...));
    }
    
    public function loadTextDomain(): void {
        load_plugin_textdomain(
            'user-page-notes',
            false,
            dirname(plugin_basename(__FILE__)) . '/languages'
        );
    }
    
    public function initPlugin(): void {
        // Initialize plugin components using dependency injection pattern
        $database = new UPN\Core\Database();
        $assets = new UPN\Core\Assets();
        $ajax = new UPN\Core\Ajax($database);
        $notesDisplay = new UPN\Frontend\NotesDisplay();
        
        if (is_admin()) {
            $settings = new UPN\Admin\Settings($database);
        }
    }
    
    public function activate(): void {
        // Create database tables and set up initial configuration
        $database = new UPN\Core\Database();
        $database->createTables();
        
        // Set default options with modern WordPress options
        add_option('upn_version', UPN_VERSION);
        add_option('upn_enabled', true);
        add_option('upn_activated_time', time());
        
        // Clear any caches
        if (function_exists('wp_cache_flush')) {
            wp_cache_flush();
        }
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    public function deactivate(): void {
        // Clean up
        flush_rewrite_rules();
        
        // Clear any caches
        if (function_exists('wp_cache_flush')) {
            wp_cache_flush();
        }
    }
    
    public function addActionLinks(array $links): array {
        $settings_link = sprintf(
            '<a href="%s">%s</a>',
            admin_url('options-general.php?page=user-page-notes'),
            esc_html__('Settings', 'user-page-notes')
        );
        
        array_unshift($links, $settings_link);
        return $links;
    }
}

// Initialize the plugin
UserPageNotesPlugin::getInstance(); 