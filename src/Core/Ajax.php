<?php

namespace UPN\Core;

/**
 * AJAX handler for User Page Notes
 */
class Ajax {
    
    private $database;
    
    public function __construct() {
        $this->database = new Database();
        
        // Register AJAX actions for logged-in users
        add_action('wp_ajax_upn_add_note', [$this, 'addNote']);
        add_action('wp_ajax_upn_update_note', [$this, 'updateNote']);
        add_action('wp_ajax_upn_delete_note', [$this, 'deleteNote']);
        add_action('wp_ajax_upn_get_notes', [$this, 'getNotes']);
    }
    
    /**
     * Verify AJAX nonce and user permissions
     */
    private function verifyRequest() {
        // Check nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'upn_ajax_nonce')) {
            wp_die(__('Security check failed', 'user-page-notes'), 403);
        }
        
        // Check if user is logged in
        if (!is_user_logged_in()) {
            wp_die(__('You must be logged in to perform this action', 'user-page-notes'), 401);
        }
        
        // Check if plugin is enabled
        if (!get_option('upn_enabled', 1)) {
            wp_die(__('Notes feature is currently disabled', 'user-page-notes'), 403);
        }
        
        return get_current_user_id();
    }
    
    /**
     * Add a new note via AJAX
     */
    public function addNote() {
        $user_id = $this->verifyRequest();
        
        // Validate required fields
        $page_url = esc_url_raw($_POST['page_url'] ?? '');
        $page_title = sanitize_text_field($_POST['page_title'] ?? '');
        $content = sanitize_textarea_field($_POST['content'] ?? '');
        $position_x = absint($_POST['position_x'] ?? 0);
        $position_y = absint($_POST['position_y'] ?? 0);
        
        if (empty($page_url) || empty($content)) {
            wp_send_json_error([
                'message' => __('Page URL and note content are required', 'user-page-notes')
            ]);
        }
        
        // Add note to database
        $note_id = $this->database->addNote(
            $user_id,
            $page_url,
            $page_title,
            $content,
            $position_x,
            $position_y
        );
        
        if (is_wp_error($note_id)) {
            wp_send_json_error([
                'message' => $note_id->get_error_message()
            ]);
        }
        
        // Get the created note data
        $note = $this->database->getNote($note_id, $user_id);
        
        if (!$note) {
            wp_send_json_error([
                'message' => __('Failed to retrieve created note', 'user-page-notes')
            ]);
        }
        
        wp_send_json_success([
            'message' => __('Note added successfully', 'user-page-notes'),
            'note' => $this->formatNoteData($note)
        ]);
    }
    
    /**
     * Update an existing note via AJAX
     */
    public function updateNote() {
        $user_id = $this->verifyRequest();
        
        // Validate required fields
        $note_id = absint($_POST['note_id'] ?? 0);
        $content = sanitize_textarea_field($_POST['content'] ?? '');
        $position_x = isset($_POST['position_x']) ? absint($_POST['position_x']) : null;
        $position_y = isset($_POST['position_y']) ? absint($_POST['position_y']) : null;
        
        if (!$note_id || empty($content)) {
            wp_send_json_error([
                'message' => __('Note ID and content are required', 'user-page-notes')
            ]);
        }
        
        // Verify note belongs to user
        $existing_note = $this->database->getNote($note_id, $user_id);
        if (!$existing_note) {
            wp_send_json_error([
                'message' => __('Note not found or access denied', 'user-page-notes')
            ]);
        }
        
        // Update note
        $success = $this->database->updateNote($note_id, $user_id, $content, $position_x, $position_y);
        
        if (!$success) {
            wp_send_json_error([
                'message' => __('Failed to update note', 'user-page-notes')
            ]);
        }
        
        // Get updated note data
        $note = $this->database->getNote($note_id, $user_id);
        
        wp_send_json_success([
            'message' => __('Note updated successfully', 'user-page-notes'),
            'note' => $this->formatNoteData($note)
        ]);
    }
    
    /**
     * Delete a note via AJAX
     */
    public function deleteNote() {
        $user_id = $this->verifyRequest();
        
        // Validate required fields
        $note_id = absint($_POST['note_id'] ?? 0);
        
        if (!$note_id) {
            wp_send_json_error([
                'message' => __('Note ID is required', 'user-page-notes')
            ]);
        }
        
        // Verify note belongs to user
        $existing_note = $this->database->getNote($note_id, $user_id);
        if (!$existing_note) {
            wp_send_json_error([
                'message' => __('Note not found or access denied', 'user-page-notes')
            ]);
        }
        
        // Delete note
        $success = $this->database->deleteNote($note_id, $user_id);
        
        if (!$success) {
            wp_send_json_error([
                'message' => __('Failed to delete note', 'user-page-notes')
            ]);
        }
        
        wp_send_json_success([
            'message' => __('Note deleted successfully', 'user-page-notes')
        ]);
    }
    
    /**
     * Get notes for the current page via AJAX
     */
    public function getNotes() {
        $user_id = $this->verifyRequest();
        
        // Validate required fields
        $page_url = esc_url_raw($_POST['page_url'] ?? '');
        
        if (empty($page_url)) {
            wp_send_json_error([
                'message' => __('Page URL is required', 'user-page-notes')
            ]);
        }
        
        // Get notes for page
        $notes = $this->database->getUserNotesForPage($user_id, $page_url);
        
        // Format notes data
        $formatted_notes = array_map([$this, 'formatNoteData'], $notes);
        
        wp_send_json_success([
            'notes' => $formatted_notes
        ]);
    }
    
    /**
     * Format note data for frontend consumption
     */
    private function formatNoteData($note) {
        if (!$note) {
            return null;
        }
        
        return [
            'id' => absint($note['id']),
            'content' => wp_kses_post($note['note_content']),
            'position_x' => absint($note['note_position_x']),
            'position_y' => absint($note['note_position_y']),
            'created_at' => $note['created_at'],
            'updated_at' => $note['updated_at'],
            'formatted_date' => human_time_diff(strtotime($note['created_at'])) . ' ' . __('ago', 'user-page-notes')
        ];
    }
} 