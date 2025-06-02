<?php

namespace UPN\Core;

/**
 * Assets handler for User Page Notes
 */
class Assets {
    
    public function __construct() {
        add_action('wp_enqueue_scripts', [$this, 'enqueueScripts']);
        add_action('admin_enqueue_scripts', [$this, 'enqueueAdminScripts']);
    }
    
    /**
     * Enqueue frontend scripts and styles
     */
    public function enqueueScripts() {
        // Only load for logged-in users
        if (!is_user_logged_in()) {
            return;
        }
        
        // Check if plugin is enabled
        if (!get_option('upn_enabled', 1)) {
            return;
        }
        
        // Enqueue CSS
        wp_enqueue_style(
            'upn-frontend-style',
            UPN_PLUGIN_URL . 'assets/css/frontend.css',
            [],
            UPN_VERSION
        );
        
        // Enqueue JavaScript (no jQuery dependency)
        wp_enqueue_script(
            'upn-frontend-script',
            UPN_PLUGIN_URL . 'assets/js/frontend.js',
            [], // Removed jQuery dependency
            UPN_VERSION,
            true
        );
        
        // Localize script with AJAX data
        wp_localize_script('upn-frontend-script', 'upnAjax', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('upn_ajax_nonce'),
            'currentUrl' => $this->getCurrentPageUrl(),
            'currentTitle' => $this->getCurrentPageTitle(),
            'strings' => [
                'addNote' => __('Add Note', 'user-page-notes'),
                'editNote' => __('Edit Note', 'user-page-notes'),
                'deleteNote' => __('Delete Note', 'user-page-notes'),
                'saveNote' => __('Save Note', 'user-page-notes'),
                'cancel' => __('Cancel', 'user-page-notes'),
                'confirmDelete' => __('Are you sure you want to delete this note?', 'user-page-notes'),
                'errorSaving' => __('Error saving note. Please try again.', 'user-page-notes'),
                'errorDeleting' => __('Error deleting note. Please try again.', 'user-page-notes'),
                'errorLoading' => __('Error loading notes. Please refresh the page.', 'user-page-notes'),
                'notePlaceholder' => __('Enter your note here...', 'user-page-notes'),
            ]
        ]);
    }
    
    /**
     * Enqueue admin scripts and styles
     */
    public function enqueueAdminScripts($hook) {
        // Only load on plugin settings page
        if ($hook !== 'settings_page_user-page-notes') {
            return;
        }
        
        wp_enqueue_style(
            'upn-admin-style',
            UPN_PLUGIN_URL . 'assets/css/admin.css',
            [],
            UPN_VERSION
        );
        
        wp_enqueue_script(
            'upn-admin-script',
            UPN_PLUGIN_URL . 'assets/js/admin.js',
            ['jquery'], // Keep jQuery for admin since it's already loaded in WP admin
            UPN_VERSION,
            true
        );
    }
    
    /**
     * Get the current page URL
     */
    private function getCurrentPageUrl() {
        global $wp;
        
        if (is_home() || is_front_page()) {
            return home_url('/');
        }
        
        // Get the current request URI
        $current_url = home_url($_SERVER['REQUEST_URI'] ?? '');
        
        // Fallback to WordPress request if available
        if (empty($_SERVER['REQUEST_URI']) && isset($wp->request)) {
            $current_url = home_url($wp->request);
        }
        
        // Final fallback
        if (empty($current_url)) {
            $current_url = home_url('/');
        }
        
        return esc_url($current_url);
    }
    
    /**
     * Get the current page title
     */
    private function getCurrentPageTitle() {
        if (is_home() || is_front_page()) {
            return get_bloginfo('name');
        }
        
        return wp_get_document_title();
    }
} 