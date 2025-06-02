<?php

namespace UPN\Frontend;

/**
 * Frontend display handler for User Page Notes
 * Modern HTML structure for CSS Grid and ES2022+ JavaScript
 */
class NotesDisplay {
    
    public function __construct() {
        add_action('wp_footer', $this->renderNotesInterface(...));
    }
    
    /**
     * Render the notes interface in the footer
     */
    public function renderNotesInterface(): void {
        // Only show for logged-in users
        if (!is_user_logged_in()) {
            return;
        }
        
        // Check if plugin is enabled
        if (!get_option('upn_enabled', true)) {
            return;
        }
        
        // Don't show on admin pages
        if (is_admin()) {
            return;
        }
        
        echo $this->getNotesHtml();
    }
    
    /**
     * Get the HTML for the notes interface with modern structure
     */
    private function getNotesHtml(): string {
        ob_start();
        ?>
        <div id="upn-notes-container" class="upn-notes-container">
            <!-- Floating Toggle Button -->
            <button id="upn-toggle-btn" class="upn-toggle-button" 
                    title="<?php esc_attr_e('My Notes', 'user-page-notes'); ?>"
                    aria-label="<?php esc_attr_e('Toggle notes panel', 'user-page-notes'); ?>">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" 
                          stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <polyline points="14,2 14,8 20,8" 
                              stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <line x1="16" y1="13" x2="8" y2="13" 
                          stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <line x1="16" y1="17" x2="8" y2="17" 
                          stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <span class="upn-notes-count" id="upn-notes-count" style="display: none;">0</span>
            </button>
            
            <!-- Notes Panel with CSS Grid -->
            <div id="upn-notes-panel" class="upn-notes-panel">
                <div class="upn-panel-header">
                    <h3 class="upn-panel-title"><?php esc_html_e('My Notes', 'user-page-notes'); ?></h3>
                    <button id="upn-close-panel" class="upn-close-panel" 
                            title="<?php esc_attr_e('Close notes panel', 'user-page-notes'); ?>"
                            aria-label="<?php esc_attr_e('Close notes panel', 'user-page-notes'); ?>">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <line x1="18" y1="6" x2="6" y2="18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <line x1="6" y1="6" x2="18" y2="18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                </div>
                
                <div class="upn-panel-content">
                    <div id="upn-notes-html" class="upn-notes-list">
                        <!-- Notes will be dynamically loaded here -->
                    </div>
                </div>
                
                <div class="upn-panel-footer">
                    <button id="upn-add-note-btn" class="upn-add-note-btn" 
                            title="<?php esc_attr_e('Add new note', 'user-page-notes'); ?>"
                            aria-label="<?php esc_attr_e('Add new note', 'user-page-notes'); ?>">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <line x1="12" y1="5" x2="12" y2="19" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <line x1="5" y1="12" x2="19" y2="12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <?php esc_html_e('Add Note', 'user-page-notes'); ?>
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Modern Modal Structure (outside container for proper positioning) -->
        <div id="upn-modal" class="upn-modal" aria-hidden="true" role="dialog" aria-labelledby="upn-modal-title">
            <div class="upn-modal-content" role="document">
                <div class="upn-modal-header">
                    <h2 id="upn-modal-title" class="upn-modal-title">
                        <?php esc_html_e('Add Note', 'user-page-notes'); ?>
                    </h2>
                    <button id="upn-modal-close" class="upn-modal-close" 
                            title="<?php esc_attr_e('Close', 'user-page-notes'); ?>"
                            aria-label="<?php esc_attr_e('Close modal', 'user-page-notes'); ?>">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <line x1="18" y1="6" x2="6" y2="18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <line x1="6" y1="6" x2="18" y2="18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                </div>
                
                <div class="upn-modal-body">
                    <div class="upn-form-group">
                        <label for="upn-note-content" class="upn-sr-only">
                            <?php esc_html_e('Note content', 'user-page-notes'); ?>
                        </label>
                        <textarea 
                            id="upn-note-content" 
                            class="upn-note-textarea"
                            placeholder="<?php esc_attr_e('Enter your note here...', 'user-page-notes'); ?>"
                            rows="6"
                            maxlength="5000"
                            aria-describedby="upn-note-help"
                            required
                        ></textarea>
                        <div id="upn-note-help" class="upn-help-text">
                            <?php esc_html_e('Markdown and basic HTML are supported. Maximum 5000 characters.', 'user-page-notes'); ?>
                        </div>
                    </div>
                </div>
                
                <div class="upn-modal-footer">
                    <button type="button" id="upn-cancel-note" class="upn-btn upn-btn-secondary">
                        <?php esc_html_e('Cancel', 'user-page-notes'); ?>
                    </button>
                    <button type="button" id="upn-save-note" class="upn-btn upn-btn-primary">
                        <?php esc_html_e('Save Note', 'user-page-notes'); ?>
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Screen reader only class for accessibility -->
        <style>
        .upn-sr-only {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            white-space: nowrap;
            border: 0;
        }
        
        .upn-help-text {
            font-size: 0.875rem;
            color: var(--upn-gray-500, #6b7280);
            margin-top: 0.25rem;
        }
        </style>
        <?php
        return ob_get_clean();
    }
} 