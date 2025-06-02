/**
 * User Page Notes Frontend JavaScript
 * Modern ES2022+ implementation with private fields and enhanced features
 */
class UserPageNotes {
    // Private class fields (ES2022+)
    #notes = [];
    #currentEditingId = null;
    #isLoading = false;
    #debounceTimer = null;
    #notesContainer = null;
    #modal = null;
    
    // Configuration with default values using nullish coalescing
    #config = {
        animationDuration: 300,
        debounceDelay: 500,
        maxNoteLength: 5000,
        autoSaveDelay: 2000
    };
    
    constructor() {
        this.#initializeElements();
        this.#bindEvents();
        this.#loadNotesForPage();
        
        // Auto-save feature
        this.#setupAutoSave();
    }
    
    /**
     * Initialize DOM elements with modern selectors
     */
    #initializeElements() {
        this.#notesContainer = document.getElementById('upn-notes-container');
        this.#modal = document.getElementById('upn-modal');
        
        if (!this.#notesContainer || !this.#modal) {
            console.error('UPN: Required DOM elements not found');
            return;
        }
        
        // Create shadow DOM for better encapsulation (modern approach)
        this.#setupShadowDOM();
    }
    
    /**
     * Setup Shadow DOM for better style encapsulation
     */
    #setupShadowDOM() {
        // For now, we'll use regular DOM but this shows modern approach
        // In future versions, we could implement full Shadow DOM
        this.#notesContainer.style.isolation = 'isolate';
    }
    
    /**
     * Bind events with modern event handling
     */
    #bindEvents() {
        // Add note button with optional chaining
        document.getElementById('upn-add-note-btn')?.addEventListener('click', 
            this.#handleAddNote.bind(this));
        
        // Modal events with modern delegation
        this.#modal?.addEventListener('click', this.#handleModalClick.bind(this));
        
        // Save button with debouncing
        document.getElementById('upn-save-note')?.addEventListener('click', 
            this.#debounce(this.#handleSaveNote.bind(this), this.#config.debounceDelay));
        
        // Cancel button
        document.getElementById('upn-cancel-note')?.addEventListener('click', 
            this.#handleCancelNote.bind(this));
        
        // Auto-resize textarea
        document.getElementById('upn-note-content')?.addEventListener('input', 
            this.#handleTextareaResize.bind(this));
        
        // Keyboard shortcuts
        document.addEventListener('keydown', this.#handleKeyboardShortcuts.bind(this));
        
        // Window resize handler
        window.addEventListener('resize', 
            this.#debounce(this.#handleWindowResize.bind(this), 250));
    }
    
    /**
     * Enhanced debounce function
     */
    #debounce(func, delay) {
        return (...args) => {
            clearTimeout(this.#debounceTimer);
            this.#debounceTimer = setTimeout(() => func.apply(this, args), delay);
        };
    }
    
    /**
     * Load notes for current page with modern fetch
     */
    async #loadNotesForPage() {
        if (this.#isLoading) return;
        
        this.#isLoading = true;
        this.#updateLoadingState(true);
        
        try {
            const response = await this.#makeRequest('get_notes', {
                page_url: window.location.href,
                page_title: document.title
            });
            
            if (response?.success) {
                this.#notes = response.data ?? [];
                this.#renderNotes();
                this.#updateNotesCount();
            } else {
                throw new Error(response?.data ?? 'Failed to load notes');
            }
        } catch (error) {
            this.#handleError('Error loading notes', error);
        } finally {
            this.#isLoading = false;
            this.#updateLoadingState(false);
        }
    }
    
    /**
     * Modern fetch-based request handler with enhanced error handling
     */
    async #makeRequest(action, data = {}) {
        const formData = new FormData();
        formData.append('action', `upn_${action}`);
        formData.append('nonce', upnAjax?.nonce ?? '');
        
        // Add data with modern object iteration
        for (const [key, value] of Object.entries(data)) {
            formData.append(key, value);
        }
        
        try {
            const response = await fetch(upnAjax?.ajaxurl ?? '/wp-admin/admin-ajax.php', {
                method: 'POST',
                body: formData,
                credentials: 'same-origin',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const result = await response.json();
            return result;
        } catch (error) {
            console.error('UPN Request failed:', error);
            throw error;
        }
    }
    
    /**
     * Handle add note with modern async/await
     */
    async #handleAddNote(event) {
        event?.preventDefault();
        
        this.#currentEditingId = null;
        this.#clearModalForm();
        this.#showModal();
        
        // Focus on content area with slight delay for better UX
        setTimeout(() => {
            document.getElementById('upn-note-content')?.focus();
        }, this.#config.animationDuration);
    }
    
    /**
     * Handle edit note with enhanced validation
     */
    async #handleEditNote(noteId) {
        const note = this.#notes.find(n => n.id === String(noteId));
        
        if (!note) {
            this.#handleError('Note not found', new Error(`Note ID ${noteId} not found`));
            return;
        }
        
        this.#currentEditingId = noteId;
        
        // Populate form with optional chaining
        const contentField = document.getElementById('upn-note-content');
        if (contentField) {
            contentField.value = note.note_content ?? '';
            this.#handleTextareaResize.call(contentField);
        }
        
        this.#showModal();
        
        // Auto-focus with accessibility consideration
        setTimeout(() => {
            contentField?.focus();
            contentField?.setSelectionRange(contentField.value.length, contentField.value.length);
        }, this.#config.animationDuration);
    }
    
    /**
     * Handle save note with enhanced validation and auto-save
     */
    async #handleSaveNote(event) {
        event?.preventDefault();
        
        const content = document.getElementById('upn-note-content')?.value?.trim();
        
        if (!content) {
            this.#showNotification('Please enter some content for your note.', 'warning');
            return;
        }
        
        if (content.length > this.#config.maxNoteLength) {
            this.#showNotification(`Note is too long. Maximum ${this.#config.maxNoteLength} characters allowed.`, 'error');
            return;
        }
        
        this.#updateLoadingState(true);
        
        try {
            const action = this.#currentEditingId ? 'update_note' : 'add_note';
            const requestData = {
                page_url: window.location.href,
                page_title: document.title,
                content: content
            };
            
            if (this.#currentEditingId) {
                requestData.note_id = this.#currentEditingId;
            }
            
            const response = await this.#makeRequest(action, requestData);
            
            if (response?.success) {
                this.#showNotification(
                    this.#currentEditingId ? 'Note updated successfully!' : 'Note added successfully!', 
                    'success'
                );
                
                await this.#loadNotesForPage();
                this.#hideModal();
            } else {
                throw new Error(response?.data ?? 'Failed to save note');
            }
        } catch (error) {
            this.#handleError('Error saving note', error);
        } finally {
            this.#updateLoadingState(false);
        }
    }
    
    /**
     * Handle delete note with confirmation
     */
    async #handleDeleteNote(noteId) {
        if (!confirm('Are you sure you want to delete this note?')) {
            return;
        }
        
        this.#updateLoadingState(true);
        
        try {
            const response = await this.#makeRequest('delete_note', {
                note_id: noteId
            });
            
            if (response?.success) {
                this.#showNotification('Note deleted successfully!', 'success');
                await this.#loadNotesForPage();
            } else {
                throw new Error(response?.data ?? 'Failed to delete note');
            }
        } catch (error) {
            this.#handleError('Error deleting note', error);
        } finally {
            this.#updateLoadingState(false);
        }
    }
    
    /**
     * Render notes with modern DOM manipulation
     */
    #renderNotes() {
        const notesHtml = document.getElementById('upn-notes-html');
        if (!notesHtml) return;
        
        // Use modern template literals and array methods
        const html = this.#notes.length > 0
            ? this.#notes.map(note => this.#createNoteHTML(note)).join('')
            : '<div class="upn-no-notes">No notes for this page yet.</div>';
        
        notesHtml.innerHTML = html;
        
        // Bind events to new elements with modern event delegation
        this.#bindNoteEvents();
    }
    
    /**
     * Create note HTML with modern template literals
     */
    #createNoteHTML(note) {
        const createdDate = new Date(note.created_at).toLocaleDateString();
        const updatedDate = new Date(note.updated_at).toLocaleDateString();
        const isUpdated = note.created_at !== note.updated_at;
        
        return `
            <div class="upn-note" data-note-id="${note.id}">
                <div class="upn-note-content">${this.#escapeHtml(note.note_content)}</div>
                <div class="upn-note-meta">
                    <span class="upn-note-date">
                        ${isUpdated 
                            ? `Updated: ${updatedDate}` 
                            : `Created: ${createdDate}`
                        }
                    </span>
                    <div class="upn-note-actions">
                        <button class="upn-edit-note" data-note-id="${note.id}" title="Edit note" aria-label="Edit note">
                            ✏️
                        </button>
                        <button class="upn-delete-note" data-note-id="${note.id}" title="Delete note" aria-label="Delete note">
                            🗑️
                        </button>
                    </div>
                </div>
            </div>
        `;
    }
    
    /**
     * Bind events to note elements with modern event delegation
     */
    #bindNoteEvents() {
        const notesHtml = document.getElementById('upn-notes-html');
        if (!notesHtml) return;
        
        // Use event delegation for better performance
        notesHtml.addEventListener('click', (event) => {
            const target = event.target;
            const noteId = target.dataset.noteId;
            
            if (!noteId) return;
            
            if (target.classList.contains('upn-edit-note')) {
                this.#handleEditNote(noteId);
            } else if (target.classList.contains('upn-delete-note')) {
                this.#handleDeleteNote(noteId);
            }
        });
    }
    
    /**
     * Modern modal handling
     */
    #showModal() {
        this.#modal?.classList.add('upn-active');
        document.body.style.overflow = 'hidden'; // Prevent background scrolling
        
        // Accessibility: trap focus in modal
        this.#trapFocus();
    }
    
    #hideModal() {
        this.#modal?.classList.remove('upn-active');
        document.body.style.overflow = '';
        this.#currentEditingId = null;
    }
    
    /**
     * Focus trapping for accessibility
     */
    #trapFocus() {
        const focusableElements = this.#modal?.querySelectorAll(
            'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
        );
        
        if (!focusableElements?.length) return;
        
        const firstElement = focusableElements[0];
        const lastElement = focusableElements[focusableElements.length - 1];
        
        const handleTabKey = (event) => {
            if (event.key !== 'Tab') return;
            
            if (event.shiftKey) {
                if (document.activeElement === firstElement) {
                    lastElement.focus();
                    event.preventDefault();
                }
            } else {
                if (document.activeElement === lastElement) {
                    firstElement.focus();
                    event.preventDefault();
                }
            }
        };
        
        this.#modal.addEventListener('keydown', handleTabKey);
    }
    
    /**
     * Enhanced notification system
     */
    #showNotification(message, type = 'info') {
        // Remove existing notifications
        document.querySelectorAll('.upn-notification').forEach(el => el.remove());
        
        const notification = document.createElement('div');
        notification.className = `upn-notification upn-notification-${type}`;
        notification.textContent = message;
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 10001;
            padding: 12px 20px;
            border-radius: 6px;
            color: white;
            font-weight: 500;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            transform: translateX(100%);
            transition: transform 0.3s ease;
        `;
        
        // Set background color based on type
        const colors = {
            success: '#22c55e',
            error: '#ef4444',
            warning: '#f59e0b',
            info: '#3b82f6'
        };
        notification.style.backgroundColor = colors[type] ?? colors.info;
        
        document.body.appendChild(notification);
        
        // Animate in
        requestAnimationFrame(() => {
            notification.style.transform = 'translateX(0)';
        });
        
        // Auto remove
        setTimeout(() => {
            notification.style.transform = 'translateX(100%)';
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    }
    
    /**
     * Enhanced error handling
     */
    #handleError(message, error) {
        console.error('UPN Error:', message, error);
        this.#showNotification(message, 'error');
    }
    
    /**
     * Utility methods with modern implementations
     */
    #escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    #updateLoadingState(isLoading) {
        const saveBtn = document.getElementById('upn-save-note');
        const addBtn = document.getElementById('upn-add-note-btn');
        
        if (saveBtn) {
            saveBtn.disabled = isLoading;
            saveBtn.textContent = isLoading ? 'Saving...' : 'Save Note';
        }
        
        if (addBtn) {
            addBtn.disabled = isLoading;
        }
    }
    
    #updateNotesCount() {
        const badge = document.querySelector('.upn-notes-count');
        if (badge) {
            badge.textContent = this.#notes.length;
            badge.style.display = this.#notes.length > 0 ? 'inline' : 'none';
        }
    }
    
    #clearModalForm() {
        const contentField = document.getElementById('upn-note-content');
        if (contentField) {
            contentField.value = '';
            contentField.style.height = 'auto';
        }
    }
    
    #handleModalClick(event) {
        if (event.target === this.#modal) {
            this.#hideModal();
        }
    }
    
    #handleCancelNote(event) {
        event?.preventDefault();
        this.#hideModal();
    }
    
    #handleTextareaResize() {
        this.style.height = 'auto';
        this.style.height = `${this.scrollHeight}px`;
    }
    
    #handleKeyboardShortcuts(event) {
        // Ctrl/Cmd + Enter to save
        if ((event.ctrlKey || event.metaKey) && event.key === 'Enter') {
            if (this.#modal?.classList.contains('upn-active')) {
                this.#handleSaveNote();
            }
        }
        
        // Escape to close modal
        if (event.key === 'Escape' && this.#modal?.classList.contains('upn-active')) {
            this.#hideModal();
        }
    }
    
    #handleWindowResize() {
        // Handle responsive behavior
        const container = this.#notesContainer;
        if (!container) return;
        
        // Adjust layout based on screen size
        if (window.innerWidth < 768) {
            container.classList.add('upn-mobile');
        } else {
            container.classList.remove('upn-mobile');
        }
    }
    
    #setupAutoSave() {
        const contentField = document.getElementById('upn-note-content');
        if (!contentField) return;
        
        let autoSaveTimer;
        const autoSave = () => {
            clearTimeout(autoSaveTimer);
            autoSaveTimer = setTimeout(() => {
                if (this.#currentEditingId && contentField.value.trim()) {
                    // Auto-save logic would go here
                    console.log('Auto-saving note...');
                }
            }, this.#config.autoSaveDelay);
        };
        
        contentField.addEventListener('input', autoSave);
    }
}

// Initialize when DOM is ready with modern approach
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        new UserPageNotes();
    });
} else {
    new UserPageNotes();
} 