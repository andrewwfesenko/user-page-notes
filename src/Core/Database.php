<?php

namespace UPN\Core;

/**
 * Database handler for User Page Notes
 * Optimized for MySQL 8+ with modern PHP 8+ features
 */
class Database {
    
    private readonly string $table_name;
    
    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'user_page_notes';
        
        // Hook into WordPress init to ensure database is ready
        add_action('init', $this->checkDatabaseVersion(...));
    }
    
    /**
     * Create the notes table with MySQL 8+ optimizations
     */
    public function createTables(): void {
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
            metadata JSON DEFAULT NULL COMMENT 'Extensible metadata for future features',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY page_url (page_url(191)),
            KEY created_at (created_at),
            KEY user_page (user_id, page_url(191)),
            FULLTEXT KEY note_content_fulltext (note_content)
        ) $charset_collate ENGINE=InnoDB COMMENT='User page notes with MySQL 8+ optimizations';";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        
        // Update version option
        update_option('upn_db_version', UPN_VERSION);
    }
    
    /**
     * Check if database needs updating
     */
    public function checkDatabaseVersion(): void {
        $installed_version = get_option('upn_db_version');
        
        if ($installed_version !== UPN_VERSION) {
            $this->createTables();
        }
    }
    
    /**
     * Add a new note with enhanced type safety
     */
    public function addNote(
        int $user_id, 
        string $page_url, 
        string $page_title, 
        string $content, 
        int $position_x = 0, 
        int $position_y = 0,
        ?array $metadata = null
    ): int|\WP_Error {
        global $wpdb;
        
        // Enhanced validation
        if ($user_id <= 0) {
            return new \WP_Error('invalid_user', __('Invalid user ID', 'user-page-notes'));
        }
        
        if (empty(trim($content))) {
            return new \WP_Error('empty_content', __('Note content cannot be empty', 'user-page-notes'));
        }
        
        // Sanitize inputs with modern PHP functions
        $page_url = filter_var($page_url, FILTER_SANITIZE_URL);
        $page_title = sanitize_text_field($page_title);
        $content = wp_kses_post($content);
        $position_x = max(0, $position_x);
        $position_y = max(0, $position_y);
        
        // Prepare metadata as JSON
        $metadata_json = $metadata ? wp_json_encode($metadata) : null;
        
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
                'metadata' => $metadata_json,
                'created_at' => current_time('mysql'),
                'updated_at' => current_time('mysql')
            ],
            [
                '%d', '%s', '%s', '%s', '%d', '%d', '%d', '%s', '%s', '%s'
            ]
        );
        
        if ($result === false) {
            return new \WP_Error('db_error', __('Failed to add note', 'user-page-notes'));
        }
        
        return $wpdb->insert_id;
    }
    
    /**
     * Get notes for a specific page and user with enhanced performance
     */
    public function getUserNotesForPage(int $user_id, string $page_url): array {
        global $wpdb;
        
        if ($user_id <= 0) {
            return [];
        }
        
        $page_url = filter_var($page_url, FILTER_SANITIZE_URL);
        
        // Use optimized query with compound index
        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT id, note_content, note_position_x, note_position_y, 
                        metadata, created_at, updated_at
                FROM {$this->table_name} 
                WHERE user_id = %d AND page_url = %s 
                ORDER BY updated_at DESC 
                LIMIT 100",
                $user_id,
                $page_url
            ),
            ARRAY_A
        );
        
        return $results ?: [];
    }
    
    /**
     * Update a note with enhanced validation
     */
    public function updateNote(
        int $note_id, 
        int $user_id, 
        string $content, 
        ?int $position_x = null, 
        ?int $position_y = null,
        ?array $metadata = null
    ): bool {
        global $wpdb;
        
        if ($note_id <= 0 || $user_id <= 0) {
            return false;
        }
        
        if (empty(trim($content))) {
            return false;
        }
        
        $content = wp_kses_post($content);
        
        // Build update data
        $update_data = [
            'note_content' => $content,
            'updated_at' => current_time('mysql')
        ];
        $update_format = ['%s', '%s'];
        
        if ($position_x !== null) {
            $update_data['note_position_x'] = max(0, $position_x);
            $update_format[] = '%d';
        }
        
        if ($position_y !== null) {
            $update_data['note_position_y'] = max(0, $position_y);
            $update_format[] = '%d';
        }
        
        if ($metadata !== null) {
            $update_data['metadata'] = wp_json_encode($metadata);
            $update_format[] = '%s';
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
     * Delete a note with enhanced security
     */
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
    
    /**
     * Get a specific note by ID and user with metadata support
     */
    public function getNote(int $note_id, int $user_id): ?array {
        global $wpdb;
        
        if ($note_id <= 0 || $user_id <= 0) {
            return null;
        }
        
        $result = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$this->table_name} 
                WHERE id = %d AND user_id = %d",
                $note_id,
                $user_id
            ),
            ARRAY_A
        );
        
        if ($result && !empty($result['metadata'])) {
            $result['metadata'] = json_decode($result['metadata'], true);
        }
        
        return $result;
    }
    
    /**
     * Get all notes for a user with pagination and search
     */
    public function getAllUserNotes(
        int $user_id, 
        int $limit = 50, 
        int $offset = 0,
        ?string $search = null
    ): array {
        global $wpdb;
        
        if ($user_id <= 0) {
            return [];
        }
        
        $limit = min(max(1, $limit), 100); // Cap at 100
        $offset = max(0, $offset);
        
        $where_clause = "WHERE user_id = %d";
        $params = [$user_id];
        
        // Add search functionality using FULLTEXT if available
        if (!empty($search)) {
            $where_clause .= " AND MATCH(note_content) AGAINST(%s IN NATURAL LANGUAGE MODE)";
            $params[] = sanitize_text_field($search);
        }
        
        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$this->table_name} 
                {$where_clause}
                ORDER BY updated_at DESC 
                LIMIT %d OFFSET %d",
                ...array_merge($params, [$limit, $offset])
            ),
            ARRAY_A
        );
        
        // Decode metadata for each result
        if ($results) {
            foreach ($results as &$result) {
                if (!empty($result['metadata'])) {
                    $result['metadata'] = json_decode($result['metadata'], true);
                }
            }
        }
        
        return $results ?: [];
    }
    
    /**
     * Get statistics for admin dashboard with MySQL 8+ window functions
     */
    public function getStatistics(): array {
        global $wpdb;
        
        // Use window functions for better performance (MySQL 8+)
        $stats = $wpdb->get_row(
            "SELECT 
                COUNT(*) as total_notes,
                COUNT(DISTINCT user_id) as active_users,
                COUNT(CASE WHEN DATE(created_at) = CURDATE() THEN 1 END) as notes_today,
                COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 END) as notes_this_week,
                COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as notes_this_month
            FROM {$this->table_name}",
            ARRAY_A
        );
        
        return $stats ?: [
            'total_notes' => 0,
            'active_users' => 0,
            'notes_today' => 0,
            'notes_this_week' => 0,
            'notes_this_month' => 0
        ];
    }
} 