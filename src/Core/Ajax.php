<?php

namespace UPN\Core;

/**
 * AJAX handler for User Page Notes
 * Clean and simple implementation
 */
class Ajax {
    
    private readonly Database $database;
    
    public function __construct(Database $database) {
        $this->database = $database;
        $this->init();
    }
    
    private function init(): void {
        add_action('wp_ajax_upn_add_note', $this->addNote(...));
        add_action('wp_ajax_upn_update_note', $this->updateNote(...));
        add_action('wp_ajax_upn_delete_note', $this->deleteNote(...));
        add_action('wp_ajax_upn_get_notes', $this->getNotes(...));
    }
    
    private function verifyRequest(): int {
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'upn_nonce')) {
            wp_send_json_error(['message' => __('Security check failed', 'user-page-notes')], 403);
        }
        
        if (!is_user_logged_in()) {
            wp_send_json_error(['message' => __('You must be logged in', 'user-page-notes')], 401);
        }
        
        return get_current_user_id();
    }
    
    public function addNote(): void {
        $user_id = $this->verifyRequest();
        
        $page_url = filter_var($_POST['page_url'] ?? '', FILTER_SANITIZE_URL);
        $page_title = sanitize_text_field($_POST['page_title'] ?? '');
        $content = wp_kses_post($_POST['content'] ?? '');
        
        if (empty($page_url) || empty(trim($content))) {
            wp_send_json_error(['message' => __('Page URL and content are required', 'user-page-notes')]);
        }
        
        $result = $this->database->addNote($user_id, $page_url, $page_title, $content);
        
        if (is_wp_error($result)) {
            wp_send_json_error(['message' => $result->get_error_message()]);
        }
        
        $note = $this->database->getNote($result, $user_id);
        
        wp_send_json_success([
            'message' => __('Note added successfully', 'user-page-notes'),
            'note' => $this->formatNote($note),
            'note_id' => $result
        ]);
    }
    
    public function updateNote(): void {
        $user_id = $this->verifyRequest();
        
        $note_id = absint($_POST['note_id'] ?? 0);
        $content = wp_kses_post($_POST['content'] ?? '');
        
        if ($note_id <= 0 || empty(trim($content))) {
            wp_send_json_error(['message' => __('Note ID and content are required', 'user-page-notes')]);
        }
        
        // Verify note belongs to user
        $existing_note = $this->database->getNote($note_id, $user_id);
        if (!$existing_note) {
            wp_send_json_error(['message' => __('Note not found', 'user-page-notes')]);
        }
        
        $success = $this->database->updateNote($note_id, $user_id, $content);
        
        if (!$success) {
            wp_send_json_error(['message' => __('Failed to update note', 'user-page-notes')]);
        }
        
        $note = $this->database->getNote($note_id, $user_id);
        
        wp_send_json_success([
            'message' => __('Note updated successfully', 'user-page-notes'),
            'note' => $this->formatNote($note)
        ]);
    }
    
    public function deleteNote(): void {
        $user_id = $this->verifyRequest();
        
        $note_id = absint($_POST['note_id'] ?? 0);
        
        if ($note_id <= 0) {
            wp_send_json_error(['message' => __('Valid note ID is required', 'user-page-notes')]);
        }
        
        // Verify note belongs to user
        $existing_note = $this->database->getNote($note_id, $user_id);
        if (!$existing_note) {
            wp_send_json_error(['message' => __('Note not found', 'user-page-notes')]);
        }
        
        $success = $this->database->deleteNote($note_id, $user_id);
        
        if (!$success) {
            wp_send_json_error(['message' => __('Failed to delete note', 'user-page-notes')]);
        }
        
        wp_send_json_success([
            'message' => __('Note deleted successfully', 'user-page-notes'),
            'deleted_id' => $note_id
        ]);
    }
    
    public function getNotes(): void {
        $user_id = $this->verifyRequest();
        
        $page_url = filter_var($_POST['page_url'] ?? '', FILTER_SANITIZE_URL);
        
        if (empty($page_url)) {
            wp_send_json_error(['message' => __('Page URL is required', 'user-page-notes')]);
        }
        
        $notes = $this->database->getUserNotesForPage($user_id, $page_url);
        $formatted_notes = array_map($this->formatNote(...), $notes);
        
        wp_send_json_success([
            'notes' => $formatted_notes,
            'count' => count($formatted_notes),
            'page_url' => $page_url
        ]);
    }
    
    private function formatNote(array $note): array {
        return [
            'id' => $note['id'],
            'note_content' => $note['note_content'],
            'created_at' => $note['created_at'],
            'updated_at' => $note['updated_at']
        ];
    }
} 