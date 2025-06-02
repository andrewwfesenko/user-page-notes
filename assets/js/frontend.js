/**
 * User Page Notes Frontend JavaScript
 * Clean and simple implementation
 */
class UserPageNotes {
    #notes = [];
    #currentEditingId = null;
    #isLoading = false;
    
    constructor() {
        this.init();
    }
    
    init() {
        // Bind all events once
        this.bindEvents();
        // Load initial notes
        this.loadNotes();
    }
    
    bindEvents() {
        // Panel toggle
        document.getElementById('upn-toggle-btn')?.addEventListener('click', () => {
            this.togglePanel();
        });
        
        // Panel close
        document.getElementById('upn-close-panel')?.addEventListener('click', () => {
            this.closePanel();
        });
        
        // Add note
        document.getElementById('upn-add-note-btn')?.addEventListener('click', () => {
            this.showModal();
        });
        
        // Modal events
        document.getElementById('upn-modal-close')?.addEventListener('click', () => {
            this.hideModal();
        });
        
        document.getElementById('upn-cancel-note')?.addEventListener('click', () => {
            this.hideModal();
        });
        
        document.getElementById('upn-save-note')?.addEventListener('click', () => {
            this.saveNote();
        });
        
        // Note events using delegation (only bound once)
        document.getElementById('upn-notes-html')?.addEventListener('click', (event) => {
            const noteId = event.target.dataset.noteId;
            if (!noteId) return;
            
            if (event.target.classList.contains('upn-edit-note')) {
                this.editNote(noteId);
            } else if (event.target.classList.contains('upn-delete-note')) {
                this.deleteNote(noteId);
            }
        });
        
        // Modal backdrop click
        document.getElementById('upn-modal')?.addEventListener('click', (event) => {
            if (event.target.id === 'upn-modal') {
                this.hideModal();
            }
        });
        
        // Keyboard shortcuts
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                const modal = document.getElementById('upn-modal');
                if (modal?.classList.contains('upn-active')) {
                    this.hideModal();
                }
            }
        });
    }
    
    async loadNotes() {
        if (this.#isLoading) return;
        
        this.#isLoading = true;
        this.updateLoadingState(true);
        
        try {
            const response = await this.makeRequest('get_notes', {
                page_url: window.location.href,
                page_title: document.title
            });
            
            if (response?.success) {
                this.#notes = response.data?.notes ?? [];
                this.renderNotes();
                this.updateNotesCount();
            }
        } catch (error) {
            this.showNotification('Error loading notes', 'error');
        } finally {
            this.#isLoading = false;
            this.updateLoadingState(false);
        }
    }
    
    renderNotes() {
        const notesHtml = document.getElementById('upn-notes-html');
        if (!notesHtml) return;
        
        if (this.#notes.length === 0) {
            notesHtml.innerHTML = '<div class="upn-no-notes">No notes for this page yet.</div>';
            return;
        }
        
        const html = this.#notes.map(note => `
            <div class="upn-note" data-note-id="${note.id}">
                <div class="upn-note-content">${this.escapeHtml(note.note_content)}</div>
                <div class="upn-note-meta">
                    <span class="upn-note-date">${new Date(note.updated_at).toLocaleDateString()}</span>
                    <div class="upn-note-actions">
                        <button class="upn-edit-note" data-note-id="${note.id}" title="Edit note">✏️</button>
                        <button class="upn-delete-note" data-note-id="${note.id}" title="Delete note">🗑️</button>
                    </div>
                </div>
            </div>
        `).join('');
        
        notesHtml.innerHTML = html;
    }
    
    showModal(note = null) {
        const modal = document.getElementById('upn-modal');
        const contentField = document.getElementById('upn-note-content');
        const title = document.querySelector('.upn-modal-title');
        
        if (note) {
            this.#currentEditingId = note.id;
            title.textContent = 'Edit Note';
            contentField.value = note.note_content || '';
        } else {
            this.#currentEditingId = null;
            title.textContent = 'Add Note';
            contentField.value = '';
        }
        
        // Auto-resize textarea
        contentField.style.height = 'auto';
        contentField.style.height = contentField.scrollHeight + 'px';
        
        // Simple resize handler
        contentField.oninput = function() {
            this.style.height = 'auto';
            this.style.height = this.scrollHeight + 'px';
        };
        
        modal.classList.add('upn-active', 'active');
        
        setTimeout(() => contentField.focus(), 100);
    }
    
    hideModal() {
        const modal = document.getElementById('upn-modal');
        modal.classList.remove('upn-active', 'active');
        this.#currentEditingId = null;
    }
    
    async saveNote() {
        const content = document.getElementById('upn-note-content').value.trim();
        
        if (!content) {
            this.showNotification('Please enter some content for your note.', 'warning');
            return;
        }
        
        this.updateLoadingState(true);
        
        try {
            const action = this.#currentEditingId ? 'update_note' : 'add_note';
            const data = {
                page_url: window.location.href,
                page_title: document.title,
                content: content
            };
            
            if (this.#currentEditingId) {
                data.note_id = this.#currentEditingId;
            }
            
            const response = await this.makeRequest(action, data);
            
            if (response?.success) {
                this.showNotification(
                    this.#currentEditingId ? 'Note updated!' : 'Note added!', 
                    'success'
                );
                await this.loadNotes();
                this.hideModal();
            } else {
                throw new Error(response?.data?.message ?? 'Failed to save note');
            }
        } catch (error) {
            this.showNotification('Error saving note: ' + error.message, 'error');
        } finally {
            this.updateLoadingState(false);
        }
    }
    
    editNote(noteId) {
        const note = this.#notes.find(n => n.id === String(noteId));
        if (note) {
            this.showModal(note);
        }
    }
    
    async deleteNote(noteId) {
        if (!confirm('Are you sure you want to delete this note?')) {
            return;
        }
        
        try {
            const response = await this.makeRequest('delete_note', { note_id: noteId });
            
            if (response?.success) {
                this.showNotification('Note deleted!', 'success');
                await this.loadNotes();
            } else {
                throw new Error(response?.data?.message ?? 'Failed to delete note');
            }
        } catch (error) {
            this.showNotification('Error deleting note: ' + error.message, 'error');
        }
    }
    
    togglePanel() {
        const panel = document.getElementById('upn-notes-panel');
        const button = document.getElementById('upn-toggle-btn');
        
        if (panel.classList.contains('upn-active')) {
            this.closePanel();
        } else {
            panel.classList.add('upn-active');
            button.classList.add('upn-active');
            this.loadNotes();
        }
    }
    
    closePanel() {
        const panel = document.getElementById('upn-notes-panel');
        const button = document.getElementById('upn-toggle-btn');
        
        panel.classList.remove('upn-active');
        button.classList.remove('upn-active');
    }
    
    async makeRequest(action, data = {}) {
        const formData = new FormData();
        formData.append('action', `upn_${action}`);
        formData.append('nonce', upnAjax?.nonce ?? '');
        
        for (const [key, value] of Object.entries(data)) {
            formData.append(key, value);
        }
        
        const response = await fetch(upnAjax?.ajaxurl ?? '/wp-admin/admin-ajax.php', {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        return await response.json();
    }
    
    showNotification(message, type = 'info') {
        // Remove existing notifications
        document.querySelectorAll('.upn-notification').forEach(el => el.remove());
        
        const notification = document.createElement('div');
        notification.className = `upn-notification upn-notification-${type}`;
        notification.textContent = message;
        
        const colors = {
            success: '#22c55e',
            error: '#ef4444',
            warning: '#f59e0b',
            info: '#3b82f6'
        };
        
        Object.assign(notification.style, {
            position: 'fixed',
            top: '20px',
            right: '20px',
            zIndex: '10001',
            padding: '12px 20px',
            borderRadius: '6px',
            color: 'white',
            fontWeight: '500',
            backgroundColor: colors[type] ?? colors.info,
            transform: 'translateX(100%)',
            transition: 'transform 0.3s ease'
        });
        
        document.body.appendChild(notification);
        
        requestAnimationFrame(() => {
            notification.style.transform = 'translateX(0)';
        });
        
        setTimeout(() => {
            notification.style.transform = 'translateX(100%)';
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    }
    
    updateLoadingState(isLoading) {
        const saveBtn = document.getElementById('upn-save-note');
        if (saveBtn) {
            saveBtn.disabled = isLoading;
            saveBtn.textContent = isLoading ? 'Saving...' : 'Save Note';
        }
    }
    
    updateNotesCount() {
        const badge = document.querySelector('.upn-notes-count');
        if (badge) {
            badge.textContent = this.#notes.length;
            badge.style.display = this.#notes.length > 0 ? 'inline' : 'none';
        }
    }
    
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
}

// Initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => new UserPageNotes());
} else {
    new UserPageNotes();
} 