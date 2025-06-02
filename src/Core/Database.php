<?php

namespace UPN\Core;

/**
 * Database handler for User Page Notes
 * Clean and simple implementation
 */
class Database {
    
    private readonly string $table_name;
    
    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'user_page_notes';
        
        add_action('init', $this->checkDatabaseVersion(...));
    }
    
    public function createTables(): void {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE {$this->table_name} (
            id int(11) NOT NULL AUTO_INCREMENT,
            user_id int(11) NOT NULL,
            page_url varchar(2048) NOT NULL,
            page_title varchar(512) NOT NULL,
            note_content text NOT NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY idx_user_page (user_id, page_url(255))
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        
        update_option('upn_db_version', UPN_VERSION);
    }
    
    public function checkDatabaseVersion(): void {
        $installed_version = get_option('upn_db_version');
        
        if ($installed_version !== UPN_VERSION) {
            $this->createTables();
        }
    }
    
    public function addNote(int $user_id, string $page_url, string $page_title, string $content): int|\WP_Error {
        global $wpdb;
        
        if ($user_id <= 0) {
            return new \WP_Error('invalid_user', __('Invalid user ID', 'user-page-notes'));
        }
        
        if (empty(trim($content))) {
            return new \WP_Error('empty_content', __('Note content cannot be empty', 'user-page-notes'));
        }
        
        $page_url = filter_var($page_url, FILTER_SANITIZE_URL);
        $page_title = sanitize_text_field($page_title);
        $content = wp_kses_post($content);
        
        $result = $wpdb->insert(
            $this->table_name,
            [
                'user_id' => $user_id,
                'page_url' => $page_url,
                'page_title' => $page_title,
                'note_content' => $content
            ],
            ['%d', '%s', '%s', '%s']
        );
        
        if ($result === false) {
            return new \WP_Error('db_error', __('Failed to add note', 'user-page-notes'));
        }
        
        return $wpdb->insert_id;
    }
    
    public function getUserNotesForPage(int $user_id, string $page_url): array {
        global $wpdb;
        
        if ($user_id <= 0) {
            return [];
        }
        
        $page_url = filter_var($page_url, FILTER_SANITIZE_URL);
        
        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT id, note_content, created_at, updated_at
                FROM {$this->table_name} 
                WHERE user_id = %d AND page_url = %s 
                ORDER BY updated_at DESC 
                LIMIT 50",
                $user_id,
                $page_url
            ),
            ARRAY_A
        );
        
        return $results ?: [];
    }
    
    public function updateNote(int $note_id, int $user_id, string $content): bool {
        global $wpdb;
        
        if ($note_id <= 0 || $user_id <= 0) {
            return false;
        }
        
        if (empty(trim($content))) {
            return false;
        }
        
        $content = wp_kses_post($content);
        
        $result = $wpdb->update(
            $this->table_name,
            [
                'note_content' => $content,
                'updated_at' => current_time('mysql')
            ],
            [
                'id' => $note_id,
                'user_id' => $user_id
            ],
            ['%s', '%s'],
            ['%d', '%d']
        );
        
        return $result !== false;
    }
    
    public function deleteNote(int $note_id, int $user_id): bool {
        global $wpdb;
        
        if ($note_id <= 0 || $user_id <= 0) {
            return false;
        }
        
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
    
    public function getNote(int $note_id, int $user_id): ?array {
        global $wpdb;
        
        if ($note_id <= 0 || $user_id <= 0) {
            return null;
        }
        
        $result = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT id, note_content, created_at, updated_at
                FROM {$this->table_name} 
                WHERE id = %d AND user_id = %d",
                $note_id,
                $user_id
            ),
            ARRAY_A
        );
        
        return $result ?: null;
    }
    
    public function getAllUserNotes(int $user_id, int $limit = 50, int $offset = 0): array {
        global $wpdb;
        
        if ($user_id <= 0) {
            return [];
        }
        
        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT id, page_url, page_title, note_content, created_at, updated_at
                FROM {$this->table_name} 
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
    
    public function getStatistics(): array {
        global $wpdb;
        
        $total_notes = $wpdb->get_var("SELECT COUNT(*) FROM {$this->table_name}");
        $total_users = $wpdb->get_var("SELECT COUNT(DISTINCT user_id) FROM {$this->table_name}");
        
        return [
            'total_notes' => (int) $total_notes,
            'total_users' => (int) $total_users
        ];
    }
} 