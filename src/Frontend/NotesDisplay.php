<?php

namespace UPN\Frontend;

/**
 * Frontend display handler for User Page Notes
 * Clean and simple implementation
 */
class NotesDisplay {
    
    public function __construct() {
        add_action('wp_footer', $this->renderNotesInterface(...));
    }
    
    public function renderNotesInterface(): void {
        // Only show for logged-in users
        if (!is_user_logged_in() || is_admin()) {
            return;
        }
        
        echo $this->getNotesHtml();
    }
    
    private function getNotesHtml(): string {
        ob_start();
        ?>
        <div id="upn-notes-container" class="upn-notes-container">
            <button id="upn-toggle-btn" title="<?php esc_attr_e('My Notes', 'user-page-notes'); ?>">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" 
                          stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <polyline points="14,2 14,8 20,8" 
                              stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <line x1="16" y1="13" x2="8" y2="13" 
                          stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <line x1="16" y1="17" x2="8" y2="17" 
                          stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <span class="upn-notes-count" style="display: none;">0</span>
            </button>
            
            <div id="upn-notes-panel" class="upn-notes-panel">
                <div class="upn-panel-header">
                    <h3 class="upn-panel-title"><?php esc_html_e('My Notes', 'user-page-notes'); ?></h3>
                    <div style="display: flex; gap: 8px;">
                        <button id="upn-add-note-btn" title="<?php esc_attr_e('Add new note', 'user-page-notes'); ?>">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <line x1="12" y1="5" x2="12" y2="19" stroke="currentColor" stroke-width="2"/>
                                <line x1="5" y1="12" x2="19" y2="12" stroke="currentColor" stroke-width="2"/>
                            </svg>
                        </button>
                        <button id="upn-close-panel" title="<?php esc_attr_e('Close notes panel', 'user-page-notes'); ?>">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <line x1="18" y1="6" x2="6" y2="18" stroke="currentColor" stroke-width="2"/>
                                <line x1="6" y1="6" x2="18" y2="18" stroke="currentColor" stroke-width="2"/>
                            </svg>
                        </button>
                    </div>
                </div>
                
                <div id="upn-notes-html">
                    <!-- Notes will be loaded here -->
                </div>
            </div>
        </div>
        
        <div id="upn-modal" class="upn-modal">
            <div class="upn-modal-content">
                <div class="upn-modal-header">
                    <h2 class="upn-modal-title"><?php esc_html_e('Add Note', 'user-page-notes'); ?></h2>
                    <button id="upn-modal-close" title="<?php esc_attr_e('Close', 'user-page-notes'); ?>">×</button>
                </div>
                
                <div class="upn-modal-body">
                    <textarea 
                        id="upn-note-content" 
                        placeholder="<?php esc_attr_e('Enter your note here...', 'user-page-notes'); ?>"
                        rows="6"
                    ></textarea>
                </div>
                
                <div class="upn-form-actions">
                    <button type="button" id="upn-cancel-note" class="upn-btn">
                        <?php esc_html_e('Cancel', 'user-page-notes'); ?>
                    </button>
                    <button type="button" id="upn-save-note" class="upn-btn">
                        <?php esc_html_e('Save Note', 'user-page-notes'); ?>
                    </button>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
} 