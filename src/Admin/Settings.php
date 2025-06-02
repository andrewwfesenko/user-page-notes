<?php

namespace UPN\Admin;

/**
 * Admin settings handler for User Page Notes
 */
class Settings {
    
    public function __construct() {
        add_action('admin_menu', [$this, 'addAdminMenu']);
        add_action('admin_init', [$this, 'initSettings']);
    }
    
    /**
     * Add admin menu item
     */
    public function addAdminMenu() {
        add_options_page(
            __('User Page Notes Settings', 'user-page-notes'),
            __('User Page Notes', 'user-page-notes'),
            'manage_options',
            'user-page-notes',
            [$this, 'settingsPage']
        );
    }
    
    /**
     * Initialize settings
     */
    public function initSettings() {
        register_setting(
            'upn_settings_group',
            'upn_enabled',
            [
                'type' => 'boolean',
                'default' => 1,
                'sanitize_callback' => 'rest_sanitize_boolean'
            ]
        );
        
        add_settings_section(
            'upn_general_section',
            __('General Settings', 'user-page-notes'),
            [$this, 'generalSectionCallback'],
            'user-page-notes'
        );
        
        add_settings_field(
            'upn_enabled',
            __('Enable Notes', 'user-page-notes'),
            [$this, 'enabledFieldCallback'],
            'user-page-notes',
            'upn_general_section'
        );
    }
    
    /**
     * General settings section callback
     */
    public function generalSectionCallback() {
        echo '<p>' . esc_html__('Configure the User Page Notes plugin settings.', 'user-page-notes') . '</p>';
    }
    
    /**
     * Enabled field callback
     */
    public function enabledFieldCallback() {
        $enabled = get_option('upn_enabled', 1);
        ?>
        <label>
            <input type="checkbox" name="upn_enabled" value="1" <?php checked($enabled, 1); ?> />
            <?php esc_html_e('Allow users to add notes to pages', 'user-page-notes'); ?>
        </label>
        <p class="description">
            <?php esc_html_e('When disabled, the notes interface will be hidden from all users.', 'user-page-notes'); ?>
        </p>
        <?php
    }
    
    /**
     * Settings page content
     */
    public function settingsPage() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'user-page-notes'));
        }
        
        $stats = $this->getStatistics();
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('User Page Notes Settings', 'user-page-notes'); ?></h1>
            
            <?php settings_errors(); ?>
            
            <div class="upn-admin-container">
                <div class="upn-admin-main">
                    <form method="post" action="options.php">
                        <?php
                        settings_fields('upn_settings_group');
                        do_settings_sections('user-page-notes');
                        submit_button();
                        ?>
                    </form>
                </div>
                
                <div class="upn-admin-sidebar">
                    <div class="upn-stats-widget">
                        <h3><?php esc_html_e('Statistics', 'user-page-notes'); ?></h3>
                        <div class="upn-stat-item">
                            <span class="upn-stat-number"><?php echo esc_html(number_format($stats['total_notes'])); ?></span>
                            <span class="upn-stat-label"><?php esc_html_e('Total Notes', 'user-page-notes'); ?></span>
                        </div>
                        <div class="upn-stat-item">
                            <span class="upn-stat-number"><?php echo esc_html(number_format($stats['active_users'])); ?></span>
                            <span class="upn-stat-label"><?php esc_html_e('Active Users', 'user-page-notes'); ?></span>
                        </div>
                        <div class="upn-stat-item">
                            <span class="upn-stat-number"><?php echo esc_html(number_format($stats['notes_today'])); ?></span>
                            <span class="upn-stat-label"><?php esc_html_e('Notes Today', 'user-page-notes'); ?></span>
                        </div>
                    </div>
                    
                    <div class="upn-info-widget">
                        <h3><?php esc_html_e('About', 'user-page-notes'); ?></h3>
                        <p><?php esc_html_e('User Page Notes allows logged-in users to add personal notes to any page on your website.', 'user-page-notes'); ?></p>
                        <p><strong><?php esc_html_e('Version:', 'user-page-notes'); ?></strong> <?php echo esc_html(UPN_VERSION); ?></p>
                        
                        <h4><?php esc_html_e('Features', 'user-page-notes'); ?></h4>
                        <ul>
                            <li><?php esc_html_e('Add, edit, and delete notes', 'user-page-notes'); ?></li>
                            <li><?php esc_html_e('Notes are private to each user', 'user-page-notes'); ?></li>
                            <li><?php esc_html_e('AJAX-powered interface', 'user-page-notes'); ?></li>
                            <li><?php esc_html_e('Responsive design', 'user-page-notes'); ?></li>
                            <li><?php esc_html_e('Secure and sanitized', 'user-page-notes'); ?></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        
        <style>
        .upn-admin-container {
            display: flex;
            gap: 20px;
            margin-top: 20px;
        }
        
        .upn-admin-main {
            flex: 2;
        }
        
        .upn-admin-sidebar {
            flex: 1;
        }
        
        .upn-stats-widget,
        .upn-info-widget {
            background: #fff;
            border: 1px solid #ccd0d4;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        
        .upn-stats-widget h3,
        .upn-info-widget h3 {
            margin-top: 0;
            margin-bottom: 15px;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }
        
        .upn-stat-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #f0f0f1;
        }
        
        .upn-stat-item:last-child {
            border-bottom: none;
        }
        
        .upn-stat-number {
            font-size: 24px;
            font-weight: bold;
            color: #2271b1;
        }
        
        .upn-stat-label {
            color: #646970;
        }
        
        .upn-info-widget ul {
            margin-left: 20px;
        }
        
        .upn-info-widget li {
            margin-bottom: 5px;
        }
        </style>
        <?php
    }
    
    /**
     * Get plugin statistics
     */
    private function getStatistics() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'user_page_notes';
        
        // Total notes
        $total_notes = $wpdb->get_var("SELECT COUNT(*) FROM {$table_name}");
        
        // Active users (users with at least one note)
        $active_users = $wpdb->get_var("SELECT COUNT(DISTINCT user_id) FROM {$table_name}");
        
        // Notes created today
        $today = current_time('Y-m-d');
        $notes_today = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$table_name} WHERE DATE(created_at) = %s",
            $today
        ));
        
        return [
            'total_notes' => intval($total_notes),
            'active_users' => intval($active_users),
            'notes_today' => intval($notes_today)
        ];
    }
} 