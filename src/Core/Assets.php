<?php

namespace UPN\Core;

/**
 * Assets management class
 * Clean and simple implementation
 */
class Assets {
    
    public function __construct() {
        add_action('wp_enqueue_scripts', $this->enqueueAssets(...));
        add_action('admin_enqueue_scripts', $this->enqueueAdminAssets(...));
    }
    
    public function enqueueAssets(): void {
        // Only load for logged-in users
        if (!is_user_logged_in()) {
            return;
        }
        
        wp_enqueue_style(
            'upn-frontend',
            UPN_PLUGIN_URL . 'assets/css/frontend.css',
            [],
            UPN_VERSION
        );
        
        wp_enqueue_script(
            'upn-frontend',
            UPN_PLUGIN_URL . 'assets/js/frontend.js',
            [],
            UPN_VERSION,
            true
        );
        
        wp_localize_script('upn-frontend', 'upnAjax', [
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('upn_nonce')
        ]);
    }
    
    public function enqueueAdminAssets(string $hook): void {
        // Only load on our admin pages
        if (!str_contains($hook, 'user-page-notes')) {
            return;
        }
        
        wp_enqueue_style(
            'upn-admin',
            UPN_PLUGIN_URL . 'assets/css/admin.css',
            [],
            UPN_VERSION
        );
        
        wp_enqueue_script(
            'upn-admin',
            UPN_PLUGIN_URL . 'assets/js/admin.js',
            ['wp-api'],
            UPN_VERSION,
            true
        );
    }
} 