<?php

namespace UPN\Frontend;

/**
 * Frontend display handler for User Page Notes
 */
class NotesDisplay {
    
    public function __construct() {
        add_action('wp_footer', [$this, 'renderNotesInterface']);
    }
    
    /**
     * Render the notes interface in the footer
     */
    public function renderNotesInterface() {
        // Only show for logged-in users
        if (!is_user_logged_in()) {
            return;
        }
        
        // Check if plugin is enabled
        if (!get_option('upn_enabled', 1)) {
            return;
        }
        
        // Don't show on admin pages
        if (is_admin()) {
            return;
        }
        
        echo $this->getNotesHtml();
    }
    
    /**
     * Get the HTML for the notes interface
     */
    private function getNotesHtml() {
        ob_start();
        ?>
        <div id="upn-notes-container" class="upn-notes-container">
            <!-- Notes Toggle Button -->
            <button id="upn-toggle-btn" class="upn-toggle-btn" title="<?php esc_attr_e('Toggle Notes', 'user-page-notes'); ?>">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <polyline points="14,2 14,8 20,8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <line x1="16" y1="13" x2="8" y2="13" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <line x1="16" y1="17" x2="8" y2="17" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <polyline points="10,9 9,9 8,9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <span class="upn-badge" id="upn-notes-count" style="display: none;">0</span>
            </button>
            
            <!-- Notes Panel -->
            <div id="upn-notes-panel" class="upn-notes-panel">
                <div class="upn-panel-header">
                    <h3><?php esc_html_e('My Notes', 'user-page-notes'); ?></h3>
                    <button id="upn-add-note-btn" class="upn-add-btn" title="<?php esc_attr_e('Add New Note', 'user-page-notes'); ?>">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <line x1="12" y1="5" x2="12" y2="19" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <line x1="5" y1="12" x2="19" y2="12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                    <button id="upn-close-panel" class="upn-close-btn" title="<?php esc_attr_e('Close', 'user-page-notes'); ?>">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <line x1="18" y1="6" x2="6" y2="18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <line x1="6" y1="6" x2="18" y2="18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                </div>
                
                <div class="upn-panel-content">
                    <div id="upn-loading" class="upn-loading" style="display: none;">
                        <div class="upn-spinner"></div>
                        <p><?php esc_html_e('Loading notes...', 'user-page-notes'); ?></p>
                    </div>
                    
                    <div id="upn-notes-list" class="upn-notes-list">
                        <!-- Notes will be loaded here via AJAX -->
                    </div>
                    
                    <div id="upn-empty-state" class="upn-empty-state" style="display: none;">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <polyline points="14,2 14,8 20,8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <h4><?php esc_html_e('No notes yet', 'user-page-notes'); ?></h4>
                        <p><?php esc_html_e('Click the + button to add your first note to this page.', 'user-page-notes'); ?></p>
                    </div>
                </div>
            </div>
            
            <!-- Success/Error Messages -->
            <div id="upn-message" class="upn-message" style="display: none;">
                <div class="upn-message-content">
                    <span class="upn-message-text"></span>
                    <button class="upn-message-close">&times;</button>
                </div>
            </div>
        </div>
        
        <!-- Note Editor Modal (moved outside of notes container) -->
        <div id="upn-note-modal" class="upn-modal" style="display: none;">
            <div class="upn-modal-content">
                <div class="upn-modal-header">
                    <h4 id="upn-modal-title"><?php esc_html_e('Add Note', 'user-page-notes'); ?></h4>
                    <button id="upn-modal-close" class="upn-close-btn">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <line x1="18" y1="6" x2="6" y2="18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <line x1="6" y1="6" x2="18" y2="18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                </div>
                <div class="upn-modal-body">
                    <form id="upn-note-form">
                        <div class="upn-form-group">
                            <textarea 
                                id="upn-note-content" 
                                name="content" 
                                placeholder="<?php esc_attr_e('Enter your note here...', 'user-page-notes'); ?>"
                                rows="6"
                                required
                            ></textarea>
                        </div>
                        <div class="upn-form-actions">
                            <button type="button" id="upn-cancel-btn" class="upn-btn upn-btn-secondary">
                                <?php esc_html_e('Cancel', 'user-page-notes'); ?>
                            </button>
                            <button type="submit" id="upn-save-btn" class="upn-btn upn-btn-primary">
                                <?php esc_html_e('Save Note', 'user-page-notes'); ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Note Template for JavaScript -->
        <script type="text/template" id="upn-note-template">
            <div class="upn-note" data-note-id="{{id}}">
                <div class="upn-note-content">{{content}}</div>
                <div class="upn-note-meta">
                    <span class="upn-note-date">{{formatted_date}}</span>
                    <div class="upn-note-actions">
                        <button class="upn-note-edit" title="<?php esc_attr_e('Edit', 'user-page-notes'); ?>">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </button>
                        <button class="upn-note-delete" title="<?php esc_attr_e('Delete', 'user-page-notes'); ?>">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <polyline points="3,6 5,6 21,6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2m3 0v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6h14z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <line x1="10" y1="11" x2="10" y2="17" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <line x1="14" y1="11" x2="14" y2="17" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </script>
        <?php
        return ob_get_clean();
    }
} 