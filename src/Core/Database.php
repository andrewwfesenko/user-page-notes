<?php

namespace UPN\Core;

/**
 * Database handler for User Page Notes
 */
class Database {
    
    private $table_name;
    
    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'user_page_notes';
        
        // Hook into WordPress init to ensure database is ready
        add_action('init', [$this, 'checkDatabaseVersion']);
    }
    
    /**
     * Create the notes table
     */
    public function createTables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE {$this->table_name} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            user_id bigint(20) unsigned NOT NULL,
            page_url varchar(500) NOT NULL,
            page_title varchar(500) DEFAULT '',
            note_content longtext NOT NULL,
            note_position_x int(11) DEFAULT 0,
            note_position_y int(11) DEFAULT 0,
            is_private tinyint(1) DEFAULT 1,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY page_url (page_url(191)),
            KEY created_at (created_at)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        
        // Update version option
        update_option('upn_db_version', UPN_VERSION);
    }
    
    /**
     * Check if database needs updating
     */
    public function checkDatabaseVersion() {
        $installed_version = get_option('upn_db_version');
        
        if ($installed_version !== UPN_VERSION) {
            $this->createTables();
        }
    }
    
    /**
     * Add a new note
     */
    public function addNote($user_id, $page_url, $page_title, $content, $position_x = 0, $position_y = 0) {
        global $wpdb;
        
        // Sanitize inputs
        $user_id = absint($user_id);
        $page_url = esc_url_raw($page_url);
        $page_title = sanitize_text_field($page_title);
        $content = wp_kses_post($content);
        $position_x = absint($position_x);
        $position_y = absint($position_y);
        
        $result = $wpdb->insert(
            $this->table_name,
            [
                'user_id' => $user_id,
                'page_url' => $page_url,
                'page_title' => $page_title,
                'note_content' => $content,
                'note_position_x' => $position_x,
                'note_position_y' => $position_y,
                'is_private' => 1,
                'created_at' => current_time('mysql'),
                'updated_at' => current_time('mysql')
            ],
            [
                '%d', '%s', '%s', '%s', '%d', '%d', '%d', '%s', '%s'
            ]
        );
        
        if ($result === false) {
            return new \WP_Error('db_error', __('Failed to add note', 'user-page-notes'));
        }
        
        return $wpdb->insert_id;
    }
    
    /**
     * Get notes for a specific page and user
     */
    public function getUserNotesForPage($user_id, $page_url) {
        global $wpdb;
        
        $user_id = absint($user_id);
        $page_url = esc_url_raw($page_url);
        
        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$this->table_name} 
                WHERE user_id = %d AND page_url = %s 
                ORDER BY created_at DESC",
                $user_id,
                $page_url
            ),
            ARRAY_A
        );
        
        return $results ?: [];
    }
    
    /**
     * Update a note
     */
    public function updateNote($note_id, $user_id, $content, $position_x = null, $position_y = null) {
        global $wpdb;
        
        $note_id = absint($note_id);
        $user_id = absint($user_id);
        $content = wp_kses_post($content);
        
        // Build update data
        $update_data = [
            'note_content' => $content,
            'updated_at' => current_time('mysql')
        ];
        $update_format = ['%s', '%s'];
        
        if ($position_x !== null) {
            $update_data['note_position_x'] = absint($position_x);
            $update_format[] = '%d';
        }
        
        if ($position_y !== null) {
            $update_data['note_position_y'] = absint($position_y);
            $update_format[] = '%d';
        }
        
        $result = $wpdb->update(
            $this->table_name,
            $update_data,
            [
                'id' => $note_id,
                'user_id' => $user_id
            ],
            $update_format,
            ['%d', '%d']
        );
        
        return $result !== false;
    }
    
    /**
     * Delete a note
     */
    public function deleteNote($note_id, $user_id) {
        global $wpdb;
        
        $note_id = absint($note_id);
        $user_id = absint($user_id);
        
        $result = $wpdb->delete(
            $this->table_name,
            [
                'id' => $note_id,
                'user_id' => $user_id
            ],
            ['%d', '%d']
        );
        
        return $result !== false;
    }
    
    /**
     * Get a specific note by ID and user
     */
    public function getNote($note_id, $user_id) {
        global $wpdb;
        
        $note_id = absint($note_id);
        $user_id = absint($user_id);
        
        $result = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$this->table_name} 
                WHERE id = %d AND user_id = %d",
                $note_id,
                $user_id
            ),
            ARRAY_A
        );
        
        return $result;
    }
    
    /**
     * Get all notes for a user
     */
    public function getAllUserNotes($user_id, $limit = 50, $offset = 0) {
        global $wpdb;
        
        $user_id = absint($user_id);
        $limit = absint($limit);
        $offset = absint($offset);
        
        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$this->table_name} 
                WHERE user_id = %d 
                ORDER BY updated_at DESC 
                LIMIT %d OFFSET %d",
                $user_id,
                $limit,
                $offset
            ),
            ARRAY_A
        );
        
        return $results ?: [];
    }
} 