/**
 * User Page Notes Frontend JavaScript
 * Handles all frontend interactions for the notes system
 * Pure vanilla JavaScript implementation
 */

(function() {
    'use strict';

    class UserPageNotes {
        constructor() {
            this.config = window.upnAjax;
            this.currentEditingId = null;
            this.notes = [];
            this.isLoading = false;
            
            this.elements = {};
            this.init();
        }

        init() {
            this.cacheElements();
            this.bindEvents();
            
            // Don't load notes if we're in test mode (no real AJAX endpoint)
            if (this.config.ajaxUrl !== '/wp-admin/admin-ajax.php') {
                this.loadNotes();
            } else {
                this.showEmptyState();
            }
        }

        cacheElements() {
            this.elements = {
                container: document.getElementById('upn-notes-container'),
                toggleBtn: document.getElementById('upn-toggle-btn'),
                panel: document.getElementById('upn-notes-panel'),
                closePanel: document.getElementById('upn-close-panel'),
                addNoteBtn: document.getElementById('upn-add-note-btn'),
                notesList: document.getElementById('upn-notes-list'),
                loading: document.getElementById('upn-loading'),
                emptyState: document.getElementById('upn-empty-state'),
                modal: document.getElementById('upn-note-modal'),
                modalTitle: document.getElementById('upn-modal-title'),
                modalClose: document.getElementById('upn-modal-close'),
                noteForm: document.getElementById('upn-note-form'),
                noteContent: document.getElementById('upn-note-content'),
                saveBtn: document.getElementById('upn-save-btn'),
                cancelBtn: document.getElementById('upn-cancel-btn'),
                message: document.getElementById('upn-message'),
                messageText: document.querySelector('#upn-message .upn-message-text'),
                messageClose: document.querySelector('#upn-message .upn-message-close'),
                notesCount: document.getElementById('upn-notes-count')
            };
        }

        bindEvents() {
            // Toggle panel
            if (this.elements.toggleBtn) {
                this.elements.toggleBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    this.togglePanel();
                });
            }

            // Close panel
            if (this.elements.closePanel) {
                this.elements.closePanel.addEventListener('click', (e) => {
                    e.preventDefault();
                    this.closePanel();
                });
            }

            // Add note button
            if (this.elements.addNoteBtn) {
                this.elements.addNoteBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    this.openModal();
                });
            }

            // Modal events
            if (this.elements.modalClose) {
                this.elements.modalClose.addEventListener('click', (e) => {
                    e.preventDefault();
                    this.closeModal();
                });
            }

            if (this.elements.cancelBtn) {
                this.elements.cancelBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    this.closeModal();
                });
            }

            // Form submission
            if (this.elements.noteForm) {
                this.elements.noteForm.addEventListener('submit', (e) => {
                    e.preventDefault();
                    this.saveNote();
                });
            }

            // Message close
            if (this.elements.messageClose) {
                this.elements.messageClose.addEventListener('click', (e) => {
                    e.preventDefault();
                    this.hideMessage();
                });
            }

            // Click outside modal to close
            if (this.elements.modal) {
                this.elements.modal.addEventListener('click', (e) => {
                    if (e.target === this.elements.modal) {
                        this.closeModal();
                    }
                });
            }

            // Keyboard events
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') {
                    if (this.elements.modal && this.elements.modal.classList.contains('active')) {
                        this.closeModal();
                    } else if (this.elements.panel && this.elements.panel.classList.contains('active')) {
                        this.closePanel();
                    }
                }
            });

            // Note actions (delegated events)
            if (this.elements.notesList) {
                this.elements.notesList.addEventListener('click', (e) => {
                    if (e.target.closest('.upn-note-edit')) {
                        e.preventDefault();
                        const noteElement = e.target.closest('.upn-note');
                        if (noteElement) {
                            const noteId = noteElement.getAttribute('data-note-id');
                            this.editNote(noteId);
                        }
                    } else if (e.target.closest('.upn-note-delete')) {
                        e.preventDefault();
                        const noteElement = e.target.closest('.upn-note');
                        if (noteElement) {
                            const noteId = noteElement.getAttribute('data-note-id');
                            this.deleteNote(noteId);
                        }
                    }
                });
            }
        }

        togglePanel() {
            const isActive = this.elements.panel.classList.contains('active');
            
            if (isActive) {
                this.closePanel();
            } else {
                this.openPanel();
            }
        }

        openPanel() {
            this.elements.panel.classList.add('active');
            this.elements.toggleBtn.classList.add('active');
            
            // Load notes if not already loaded or if panel is empty
            if (this.notes.length === 0 && !this.isLoading) {
                // Only load if we have a real AJAX endpoint
                if (this.config.ajaxUrl !== '/wp-admin/admin-ajax.php') {
                    this.loadNotes();
                }
            }
        }

        closePanel() {
            this.elements.panel.classList.remove('active');
            this.elements.toggleBtn.classList.remove('active');
        }

        openModal(editMode = false, noteData = null) {
            this.currentEditingId = editMode && noteData ? noteData.id : null;
            
            // Set modal title
            const title = editMode ? this.config.strings.editNote : this.config.strings.addNote;
            this.elements.modalTitle.textContent = title;
            
            // Set save button text
            this.elements.saveBtn.textContent = this.config.strings.saveNote;
            
            // Populate form if editing
            if (editMode && noteData) {
                this.elements.noteContent.value = noteData.content;
            } else {
                this.elements.noteForm.reset();
            }
            
            this.elements.modal.classList.add('active');
            this.elements.modal.style.display = 'flex';
            
            // Focus on textarea
            setTimeout(() => {
                this.elements.noteContent.focus();
            }, 100);
        }

        closeModal() {
            this.elements.modal.classList.remove('active');
            this.elements.modal.style.display = 'none';
            this.currentEditingId = null;
            this.elements.noteForm.reset();
        }

        loadNotes() {
            if (this.isLoading) return;
            
            this.isLoading = true;
            this.showLoading();

            const data = new FormData();
            data.append('action', 'upn_get_notes');
            data.append('nonce', this.config.nonce);
            data.append('page_url', this.config.currentUrl);

            fetch(this.config.ajaxUrl, {
                method: 'POST',
                body: data
            })
            .then(response => response.json())
            .then(response => {
                if (response.success) {
                    this.notes = response.data.notes || [];
                    this.renderNotes();
                    this.updateNotesCount();
                } else {
                    this.showMessage(response.data.message || this.config.strings.errorLoading, 'error');
                }
            })
            .catch(() => {
                this.showMessage(this.config.strings.errorLoading, 'error');
            })
            .finally(() => {
                this.isLoading = false;
                this.hideLoading();
            });
        }

        saveNote() {
            const content = this.elements.noteContent.value.trim();
            
            if (!content) {
                this.showMessage('Please enter some content for your note.', 'warning');
                return;
            }

            // For test mode, simulate saving
            if (this.config.ajaxUrl === '/wp-admin/admin-ajax.php') {
                const testNote = {
                    id: Date.now(),
                    content: content,
                    formatted_date: 'just now'
                };
                
                this.showMessage('Note saved successfully (test mode)', 'success');
                this.closeModal();
                this.addNoteToList(testNote);
                this.updateNotesCount();
                return;
            }

            const isEdit = this.currentEditingId !== null;
            const action = isEdit ? 'upn_update_note' : 'upn_add_note';
            
            const data = new FormData();
            data.append('action', action);
            data.append('nonce', this.config.nonce);
            data.append('content', content);
            data.append('page_url', this.config.currentUrl);
            data.append('page_title', this.config.currentTitle);

            if (isEdit) {
                data.append('note_id', this.currentEditingId);
            }

            // Disable save button
            this.elements.saveBtn.disabled = true;
            this.elements.saveBtn.textContent = 'Saving...';

            fetch(this.config.ajaxUrl, {
                method: 'POST',
                body: data
            })
            .then(response => response.json())
            .then(response => {
                if (response.success) {
                    this.showMessage(response.data.message, 'success');
                    this.closeModal();
                    
                    if (isEdit) {
                        this.updateNoteInList(response.data.note);
                    } else {
                        this.addNoteToList(response.data.note);
                    }
                    
                    this.updateNotesCount();
                } else {
                    this.showMessage(response.data.message || this.config.strings.errorSaving, 'error');
                }
            })
            .catch(() => {
                this.showMessage(this.config.strings.errorSaving, 'error');
            })
            .finally(() => {
                this.elements.saveBtn.disabled = false;
                this.elements.saveBtn.textContent = this.config.strings.saveNote;
            });
        }

        editNote(noteId) {
            const note = this.notes.find(n => n.id == noteId);
            if (note) {
                this.openModal(true, note);
            }
        }

        deleteNote(noteId) {
            if (!confirm(this.config.strings.confirmDelete)) {
                return;
            }

            // For test mode, simulate deletion
            if (this.config.ajaxUrl === '/wp-admin/admin-ajax.php') {
                this.showMessage('Note deleted successfully (test mode)', 'success');
                this.removeNoteFromList(noteId);
                this.updateNotesCount();
                return;
            }

            const data = new FormData();
            data.append('action', 'upn_delete_note');
            data.append('nonce', this.config.nonce);
            data.append('note_id', noteId);

            fetch(this.config.ajaxUrl, {
                method: 'POST',
                body: data
            })
            .then(response => response.json())
            .then(response => {
                if (response.success) {
                    this.showMessage(response.data.message, 'success');
                    this.removeNoteFromList(noteId);
                    this.updateNotesCount();
                } else {
                    this.showMessage(response.data.message || this.config.strings.errorDeleting, 'error');
                }
            })
            .catch(() => {
                this.showMessage(this.config.strings.errorDeleting, 'error');
            });
        }

        renderNotes() {
            this.elements.notesList.innerHTML = '';
            
            if (this.notes.length === 0) {
                this.showEmptyState();
                return;
            }

            this.hideEmptyState();
            
            const template = document.getElementById('upn-note-template');
            if (!template) {
                return;
            }
            
            this.notes.forEach(note => {
                const noteHtml = this.renderNote(note, template.innerHTML);
                this.elements.notesList.appendChild(noteHtml);
            });
        }

        renderNote(note, template) {
            let html = template;
            
            // Replace placeholders
            html = html.replace(/\{\{id\}\}/g, note.id);
            html = html.replace(/\{\{content\}\}/g, note.content);
            html = html.replace(/\{\{formatted_date\}\}/g, note.formatted_date);
            
            // Create element from HTML string
            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = html;
            const noteElement = tempDiv.firstElementChild;
            
            noteElement.classList.add('upn-fade-in');
            
            return noteElement;
        }

        addNoteToList(note) {
            this.notes.unshift(note);
            
            this.hideEmptyState();
            
            const template = document.getElementById('upn-note-template');
            if (template) {
                const noteHtml = this.renderNote(note, template.innerHTML);
                this.elements.notesList.insertBefore(noteHtml, this.elements.notesList.firstChild);
            }
        }

        updateNoteInList(note) {
            const index = this.notes.findIndex(n => n.id == note.id);
            if (index !== -1) {
                this.notes[index] = note;
                
                const existingNote = this.elements.notesList.querySelector(`[data-note-id="${note.id}"]`);
                if (existingNote) {
                    const template = document.getElementById('upn-note-template');
                    if (template) {
                        const noteHtml = this.renderNote(note, template.innerHTML);
                        existingNote.parentNode.replaceChild(noteHtml, existingNote);
                    }
                }
            }
        }

        removeNoteFromList(noteId) {
            this.notes = this.notes.filter(n => n.id != noteId);
            
            const noteElement = this.elements.notesList.querySelector(`[data-note-id="${noteId}"]`);
            if (noteElement) {
                noteElement.classList.add('upn-fade-out');
                
                setTimeout(() => {
                    if (noteElement.parentNode) {
                        noteElement.parentNode.removeChild(noteElement);
                    }
                    
                    if (this.notes.length === 0) {
                        this.showEmptyState();
                    }
                }, 300);
            }
        }

        updateNotesCount() {
            const count = this.notes.length;
            
            if (count > 0) {
                this.elements.notesCount.textContent = count;
                this.elements.notesCount.style.display = 'inline';
            } else {
                this.elements.notesCount.style.display = 'none';
            }
        }

        showLoading() {
            this.elements.loading.style.display = 'flex';
            this.elements.notesList.style.display = 'none';
            this.elements.emptyState.style.display = 'none';
        }

        hideLoading() {
            this.elements.loading.style.display = 'none';
            this.elements.notesList.style.display = 'block';
        }

        showEmptyState() {
            this.elements.emptyState.style.display = 'flex';
            this.elements.notesList.style.display = 'none';
        }

        hideEmptyState() {
            this.elements.emptyState.style.display = 'none';
            this.elements.notesList.style.display = 'block';
        }

        showMessage(text, type = 'success') {
            this.elements.messageText.textContent = text;
            this.elements.message.className = 'upn-message ' + type + ' active';
            
            // Auto-hide after 5 seconds
            setTimeout(() => {
                this.hideMessage();
            }, 5000);
        }

        hideMessage() {
            this.elements.message.classList.remove('active');
        }

        // Utility methods
        debounce(func, wait, immediate) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    timeout = null;
                    if (!immediate) func(...args);
                };
                const callNow = immediate && !timeout;
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
                if (callNow) func(...args);
            };
        }

        throttle(func, limit) {
            let inThrottle;
            return function(...args) {
                if (!inThrottle) {
                    func.apply(this, args);
                    inThrottle = true;
                    setTimeout(() => inThrottle = false, limit);
                }
            };
        }
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializePlugin);
    } else {
        initializePlugin();
    }

    function initializePlugin() {
        // Only initialize if the upnAjax object exists (user is logged in and plugin is enabled)
        if (typeof window.upnAjax !== 'undefined') {
            try {
                new UserPageNotes();
            } catch (error) {
                // Silent error handling in production
            }
        }
    }

})(); 