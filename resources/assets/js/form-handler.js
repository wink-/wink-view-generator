/**
 * Wink View Generator - Advanced Form Handler
 * 
 * Features:
 * - Auto-save functionality with conflict resolution
 * - Advanced file uploads with progress tracking
 * - Real-time validation and feedback
 * - Form state management
 * - Keyboard shortcuts
 * - Touch-optimized mobile experience
 * 
 * @version 2.0.0
 */

(function(window, document) {
    'use strict';

    // Ensure WinkViews namespace exists
    window.WinkViews = window.WinkViews || {};

    const FormHandler = {
        instances: new Map(),
        globalConfig: {
            autoSaveInterval: 30000, // 30 seconds
            maxFileSize: 10 * 1024 * 1024, // 10MB
            allowedFileTypes: ['image/*', '.pdf', '.doc', '.docx', '.txt'],
            validationDebounce: 500,
            maxRetries: 3
        },

        /**
         * Initialize form handler on a form element
         */
        init: function(formElement, options = {}) {
            if (!formElement || !(formElement instanceof HTMLFormElement)) {
                throw new Error('FormHandler requires a valid form element');
            }

            const formId = formElement.id || WinkViews.Utils.generateId();
            formElement.id = formId;

            const config = Object.assign({}, this.globalConfig, options);
            
            const instance = new FormInstance(formElement, config);
            this.instances.set(formId, instance);
            
            return instance;
        },

        /**
         * Get form instance by ID
         */
        getInstance: function(formId) {
            return this.instances.get(formId);
        },

        /**
         * Remove form instance
         */
        destroy: function(formId) {
            const instance = this.instances.get(formId);
            if (instance) {
                instance.destroy();
                this.instances.delete(formId);
            }
        }
    };

    /**
     * Form Instance Class
     */
    function FormInstance(formElement, config) {
        this.form = formElement;
        this.config = config;
        this.originalData = null;
        this.isDirty = false;
        this.isSubmitting = false;
        this.autoSaveTimer = null;
        this.validationErrors = {};
        this.fileUploads = new Map();
        this.shortcuts = new Map();
        
        this.init();
    }

    FormInstance.prototype = {
        /**
         * Initialize the form instance
         */
        init: function() {
            this.captureOriginalData();
            this.setupEventListeners();
            this.setupValidation();
            this.setupAutoSave();
            this.setupFileUploads();
            this.setupKeyboardShortcuts();
            this.setupAccessibility();
            
            // Mark as initialized
            this.form.classList.add('wink-form-initialized');
            this.form.setAttribute('data-wink-form', this.form.id);
        },

        /**
         * Capture original form data for comparison
         */
        captureOriginalData: function() {
            this.originalData = new FormData(this.form);
            this.isDirty = false;
        },

        /**
         * Setup event listeners
         */
        setupEventListeners: function() {
            // Form submission
            this.form.addEventListener('submit', this.handleSubmit.bind(this));
            
            // Input change detection
            this.form.addEventListener('input', this.handleInput.bind(this));
            this.form.addEventListener('change', this.handleChange.bind(this));
            
            // Focus events for better UX
            this.form.addEventListener('focusin', this.handleFocusIn.bind(this));
            this.form.addEventListener('focusout', this.handleFocusOut.bind(this));
            
            // Prevent accidental navigation
            window.addEventListener('beforeunload', this.handleBeforeUnload.bind(this));
        },

        /**
         * Setup real-time validation
         */
        setupValidation: function() {
            const inputs = this.form.querySelectorAll('input, textarea, select');
            
            inputs.forEach(input => {
                const validateField = WinkViews.Utils.debounce(() => {
                    this.validateField(input);
                }, this.config.validationDebounce);
                
                input.addEventListener('input', validateField);
                input.addEventListener('blur', () => this.validateField(input));
            });
        },

        /**
         * Setup auto-save functionality
         */
        setupAutoSave: function() {
            if (!this.config.autoSaveInterval) return;
            
            this.autoSaveTimer = setInterval(() => {
                if (this.isDirty && !this.isSubmitting) {
                    this.autoSave();
                }
            }, this.config.autoSaveInterval);
        },

        /**
         * Setup file upload handling
         */
        setupFileUploads: function() {
            const fileInputs = this.form.querySelectorAll('input[type="file"]');
            
            fileInputs.forEach(input => {
                this.setupFileInput(input);
            });
        },

        /**
         * Setup individual file input
         */
        setupFileInput: function(input) {
            const wrapper = this.createFileUploadWrapper(input);
            input.parentNode.insertBefore(wrapper, input);
            wrapper.appendChild(input);
            
            input.addEventListener('change', (e) => {
                this.handleFileSelection(e, wrapper);
            });
            
            // Drag and drop support
            wrapper.addEventListener('dragover', this.handleDragOver.bind(this));
            wrapper.addEventListener('drop', (e) => this.handleFileDrop(e, input));
        },

        /**
         * Create file upload wrapper with drag/drop zone
         */
        createFileUploadWrapper: function(input) {
            const wrapper = document.createElement('div');
            wrapper.className = 'wink-file-upload-wrapper';
            wrapper.innerHTML = `
                <div class="wink-file-drop-zone">
                    <div class="wink-file-drop-content">
                        <svg class="wink-file-upload-icon" width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z" />
                        </svg>
                        <p class="wink-file-drop-text">Drop files here or click to select</p>
                        <p class="wink-file-drop-hint">Max file size: ${this.formatFileSize(this.config.maxFileSize)}</p>
                    </div>
                    <div class="wink-file-progress" style="display: none;">
                        <div class="wink-file-progress-bar"></div>
                        <span class="wink-file-progress-text">0%</span>
                    </div>
                </div>
                <div class="wink-file-list"></div>
            `;
            
            return wrapper;
        },

        /**
         * Setup keyboard shortcuts
         */
        setupKeyboardShortcuts: function() {
            // Ctrl+S / Cmd+S for save
            this.addShortcut('s', (e) => {
                if (e.ctrlKey || e.metaKey) {
                    e.preventDefault();
                    this.submit();
                }
            });
            
            // Ctrl+Z / Cmd+Z for undo (reset to original)
            this.addShortcut('z', (e) => {
                if (e.ctrlKey || e.metaKey && !e.shiftKey) {
                    e.preventDefault();
                    this.reset();
                }
            });
            
            // ESC to cancel/close
            this.addShortcut('Escape', () => {
                this.cancel();
            });
        },

        /**
         * Add keyboard shortcut
         */
        addShortcut: function(key, handler) {
            this.shortcuts.set(key.toLowerCase(), handler);
            
            this.form.addEventListener('keydown', (e) => {
                const keyHandler = this.shortcuts.get(e.key.toLowerCase());
                if (keyHandler) {
                    keyHandler(e);
                }
            });
        },

        /**
         * Setup accessibility features
         */
        setupAccessibility: function() {
            // Add form role if not present
            if (!this.form.getAttribute('role')) {
                this.form.setAttribute('role', 'form');
            }
            
            // Enhance required field indicators
            this.form.querySelectorAll('[required]').forEach(field => {
                const label = this.form.querySelector(`label[for="${field.id}"]`);
                if (label && !label.textContent.includes('*')) {
                    label.innerHTML += ' <span class="wink-required" aria-label="required">*</span>';
                }
                
                field.setAttribute('aria-required', 'true');
            });
            
            // Add describedby relationships for help text
            this.form.querySelectorAll('.wink-form-help').forEach(help => {
                const fieldId = help.getAttribute('data-field');
                if (fieldId) {
                    const field = this.form.querySelector(`#${fieldId}`);
                    if (field) {
                        const helpId = help.id || `${fieldId}-help`;
                        help.id = helpId;
                        field.setAttribute('aria-describedby', helpId);
                    }
                }
            });
        },

        /**
         * Handle form submission
         */
        handleSubmit: function(e) {
            e.preventDefault();
            this.submit();
        },

        /**
         * Submit the form
         */
        submit: function(options = {}) {
            if (this.isSubmitting) return;
            
            const config = Object.assign({
                showLoading: true,
                validateFirst: true,
                onSuccess: this.config.onSuccess,
                onError: this.config.onError
            }, options);
            
            // Validate form first
            if (config.validateFirst && !this.validateForm()) {
                WinkViews.Utils.showNotification('Please fix validation errors before submitting', 'error');
                return;
            }
            
            this.isSubmitting = true;
            
            if (config.showLoading) {
                this.showLoading();
            }
            
            // Clear auto-save timer
            if (this.autoSaveTimer) {
                clearInterval(this.autoSaveTimer);
            }
            
            // Use the enhanced AJAX submission from core
            WinkViews.Forms.submitAjax(this.form, {
                onSuccess: (data) => {
                    this.isSubmitting = false;
                    this.hideLoading();
                    this.captureOriginalData(); // Reset dirty state
                    
                    if (config.onSuccess) {
                        config.onSuccess(data);
                    }
                },
                onError: (errors, status) => {
                    this.isSubmitting = false;
                    this.hideLoading();
                    this.handleSubmissionErrors(errors, status);
                    
                    // Restart auto-save
                    this.setupAutoSave();
                    
                    if (config.onError) {
                        config.onError(errors, status);
                    }
                }
            });
        },

        /**
         * Auto-save functionality
         */
        autoSave: function() {
            if (!this.isDirty || this.isSubmitting) return;
            
            const formData = new FormData(this.form);
            formData.append('_auto_save', '1');
            
            const autoSaveEndpoint = this.form.getAttribute('data-autosave-url') || 
                                   this.form.action + '?auto_save=1';
            
            fetch(autoSaveEndpoint, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': WinkViews.Utils.getCsrfToken()
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.showAutoSaveSuccess();
                    
                    // Handle version conflicts
                    if (data.version && data.version !== this.getFormVersion()) {
                        this.handleVersionConflict(data);
                    }
                }
            })
            .catch(error => {
                console.warn('Auto-save failed:', error);
            });
        },

        /**
         * Handle input events
         */
        handleInput: function(e) {
            this.markDirty();
            this.clearFieldError(e.target);
        },

        /**
         * Handle change events
         */
        handleChange: function(e) {
            this.markDirty();
            
            // Special handling for select elements
            if (e.target.tagName === 'SELECT') {
                this.validateField(e.target);
            }
        },

        /**
         * Handle focus in events
         */
        handleFocusIn: function(e) {
            const field = e.target;
            if (field.matches('input, textarea, select')) {
                field.parentNode.classList.add('wink-field-focused');
                
                // Show helpful text if available
                const help = field.parentNode.querySelector('.wink-form-help');
                if (help) {
                    help.style.display = 'block';
                }
            }
        },

        /**
         * Handle focus out events
         */
        handleFocusOut: function(e) {
            const field = e.target;
            if (field.matches('input, textarea, select')) {
                field.parentNode.classList.remove('wink-field-focused');
                
                // Hide helpful text
                const help = field.parentNode.querySelector('.wink-form-help');
                if (help && !field.value) {
                    help.style.display = 'none';
                }
            }
        },

        /**
         * Handle file selection
         */
        handleFileSelection: function(e, wrapper) {
            const files = Array.from(e.target.files);
            this.processFiles(files, wrapper);
        },

        /**
         * Handle file drop
         */
        handleFileDrop: function(e, input) {
            e.preventDefault();
            const files = Array.from(e.dataTransfer.files);
            const wrapper = input.closest('.wink-file-upload-wrapper');
            
            // Update the file input
            const dt = new DataTransfer();
            files.forEach(file => dt.items.add(file));
            input.files = dt.files;
            
            this.processFiles(files, wrapper);
            this.markDirty();
        },

        /**
         * Handle drag over
         */
        handleDragOver: function(e) {
            e.preventDefault();
            e.currentTarget.classList.add('wink-file-drag-over');
        },

        /**
         * Process uploaded files
         */
        processFiles: function(files, wrapper) {
            const validFiles = files.filter(file => this.validateFile(file));
            const fileList = wrapper.querySelector('.wink-file-list');
            
            fileList.innerHTML = '';
            
            validFiles.forEach(file => {
                const fileItem = this.createFileItem(file);
                fileList.appendChild(fileItem);
                
                // Start upload if immediate upload is enabled
                if (this.config.immediateUpload) {
                    this.uploadFile(file, fileItem);
                }
            });
            
            wrapper.classList.remove('wink-file-drag-over');
        },

        /**
         * Validate file
         */
        validateFile: function(file) {
            // Check file size
            if (file.size > this.config.maxFileSize) {
                WinkViews.Utils.showNotification(
                    `File "${file.name}" is too large. Maximum size is ${this.formatFileSize(this.config.maxFileSize)}`,
                    'error'
                );
                return false;
            }
            
            // Check file type
            const isValidType = this.config.allowedFileTypes.some(type => {
                if (type.startsWith('.')) {
                    return file.name.toLowerCase().endsWith(type);
                } else if (type.includes('*')) {
                    const mimeType = type.replace('*', '');
                    return file.type.startsWith(mimeType);
                } else {
                    return file.type === type;
                }
            });
            
            if (!isValidType) {
                WinkViews.Utils.showNotification(
                    `File "${file.name}" is not an allowed file type`,
                    'error'
                );
                return false;
            }
            
            return true;
        },

        /**
         * Create file item element
         */
        createFileItem: function(file) {
            const item = document.createElement('div');
            item.className = 'wink-file-item';
            item.innerHTML = `
                <div class="wink-file-info">
                    <span class="wink-file-name">${file.name}</span>
                    <span class="wink-file-size">${this.formatFileSize(file.size)}</span>
                </div>
                <div class="wink-file-actions">
                    <button type="button" class="wink-file-remove" aria-label="Remove file">×</button>
                </div>
                <div class="wink-file-progress" style="display: none;">
                    <div class="wink-file-progress-bar" style="width: 0%"></div>
                </div>
            `;
            
            // Remove file handler
            item.querySelector('.wink-file-remove').addEventListener('click', () => {
                item.remove();
                this.markDirty();
            });
            
            return item;
        },

        /**
         * Upload file with progress tracking
         */
        uploadFile: function(file, fileItem) {
            const formData = new FormData();
            formData.append('file', file);
            formData.append('_token', WinkViews.Utils.getCsrfToken());
            
            const xhr = new XMLHttpRequest();
            const progressBar = fileItem.querySelector('.wink-file-progress-bar');
            const progressContainer = fileItem.querySelector('.wink-file-progress');
            
            progressContainer.style.display = 'block';
            
            xhr.upload.addEventListener('progress', (e) => {
                if (e.lengthComputable) {
                    const percent = (e.loaded / e.total) * 100;
                    progressBar.style.width = percent + '%';
                }
            });
            
            xhr.onload = () => {
                if (xhr.status === 200) {
                    const response = JSON.parse(xhr.responseText);
                    if (response.success) {
                        fileItem.classList.add('wink-file-uploaded');
                        progressContainer.style.display = 'none';
                    }
                }
            };
            
            xhr.onerror = () => {
                fileItem.classList.add('wink-file-error');
                progressContainer.style.display = 'none';
            };
            
            const uploadUrl = this.form.getAttribute('data-upload-url') || '/upload';
            xhr.open('POST', uploadUrl);
            xhr.send(formData);
        },

        /**
         * Validate individual field
         */
        validateField: function(field) {
            const errors = [];
            
            // HTML5 validation
            if (!field.checkValidity()) {
                errors.push(field.validationMessage);
            }
            
            // Custom validation rules
            const customValidation = field.getAttribute('data-validate');
            if (customValidation) {
                const customErrors = this.runCustomValidation(field, customValidation);
                errors.push(...customErrors);
            }
            
            // Update field state
            if (errors.length > 0) {
                this.showFieldError(field, errors[0]);
                this.validationErrors[field.name] = errors;
                return false;
            } else {
                this.clearFieldError(field);
                delete this.validationErrors[field.name];
                return true;
            }
        },

        /**
         * Run custom validation
         */
        runCustomValidation: function(field, rules) {
            const errors = [];
            const rulesArray = rules.split('|');
            
            rulesArray.forEach(rule => {
                const [ruleName, ruleParam] = rule.split(':');
                
                switch (ruleName) {
                    case 'min_length':
                        if (field.value.length < parseInt(ruleParam)) {
                            errors.push(`Minimum length is ${ruleParam} characters`);
                        }
                        break;
                    case 'max_length':
                        if (field.value.length > parseInt(ruleParam)) {
                            errors.push(`Maximum length is ${ruleParam} characters`);
                        }
                        break;
                    case 'confirmed':
                        const confirmField = this.form.querySelector(`[name="${field.name}_confirmation"]`);
                        if (confirmField && field.value !== confirmField.value) {
                            errors.push('Confirmation does not match');
                        }
                        break;
                    case 'unique':
                        // This would typically be handled server-side
                        break;
                }
            });
            
            return errors;
        },

        /**
         * Validate entire form
         */
        validateForm: function() {
            const fields = this.form.querySelectorAll('input, textarea, select');
            let isValid = true;
            
            fields.forEach(field => {
                if (!this.validateField(field)) {
                    isValid = false;
                }
            });
            
            return isValid;
        },

        /**
         * Show field error
         */
        showFieldError: function(field, message) {
            this.clearFieldError(field);
            
            field.classList.add('wink-invalid');
            field.setAttribute('aria-invalid', 'true');
            
            const error = document.createElement('div');
            error.className = 'wink-field-error';
            error.textContent = message;
            error.id = `${field.name}-error`;
            
            field.setAttribute('aria-describedby', error.id);
            field.parentNode.appendChild(error);
        },

        /**
         * Clear field error
         */
        clearFieldError: function(field) {
            field.classList.remove('wink-invalid');
            field.removeAttribute('aria-invalid');
            
            const existingError = field.parentNode.querySelector('.wink-field-error');
            if (existingError) {
                existingError.remove();
            }
        },

        /**
         * Mark form as dirty
         */
        markDirty: function() {
            if (!this.isDirty) {
                this.isDirty = true;
                this.form.classList.add('wink-form-dirty');
                
                // Show unsaved changes indicator
                this.showUnsavedIndicator();
            }
        },

        /**
         * Show loading state
         */
        showLoading: function() {
            this.form.classList.add('wink-form-loading');
            
            const submitButton = this.form.querySelector('[type="submit"]');
            if (submitButton) {
                submitButton.disabled = true;
                submitButton.classList.add('wink-loading');
            }
        },

        /**
         * Hide loading state
         */
        hideLoading: function() {
            this.form.classList.remove('wink-form-loading');
            
            const submitButton = this.form.querySelector('[type="submit"]');
            if (submitButton) {
                submitButton.disabled = false;
                submitButton.classList.remove('wink-loading');
            }
        },

        /**
         * Show unsaved changes indicator
         */
        showUnsavedIndicator: function() {
            let indicator = document.querySelector('.wink-unsaved-indicator');
            if (!indicator) {
                indicator = document.createElement('div');
                indicator.className = 'wink-unsaved-indicator';
                indicator.innerHTML = '● Unsaved changes';
                document.body.appendChild(indicator);
            }
            indicator.style.display = 'block';
        },

        /**
         * Hide unsaved changes indicator
         */
        hideUnsavedIndicator: function() {
            const indicator = document.querySelector('.wink-unsaved-indicator');
            if (indicator) {
                indicator.style.display = 'none';
            }
        },

        /**
         * Show auto-save success
         */
        showAutoSaveSuccess: function() {
            const indicator = document.querySelector('.wink-autosave-indicator') || this.createAutoSaveIndicator();
            indicator.textContent = '✓ Auto-saved';
            indicator.style.display = 'block';
            
            setTimeout(() => {
                indicator.style.display = 'none';
            }, 2000);
        },

        /**
         * Create auto-save indicator
         */
        createAutoSaveIndicator: function() {
            const indicator = document.createElement('div');
            indicator.className = 'wink-autosave-indicator';
            document.body.appendChild(indicator);
            return indicator;
        },

        /**
         * Handle version conflicts
         */
        handleVersionConflict: function(data) {
            const modal = this.createConflictModal(data);
            document.body.appendChild(modal);
            
            if (WinkViews.ModalManager) {
                WinkViews.ModalManager.show(modal);
            } else {
                modal.style.display = 'block';
            }
        },

        /**
         * Create conflict resolution modal
         */
        createConflictModal: function(data) {
            const modal = document.createElement('div');
            modal.className = 'wink-modal wink-conflict-modal';
            modal.innerHTML = `
                <div class="wink-modal-dialog">
                    <div class="wink-modal-header">
                        <h5 class="wink-modal-title">Version Conflict Detected</h5>
                    </div>
                    <div class="wink-modal-body">
                        <p>This form has been modified by another user. How would you like to proceed?</p>
                        <div class="wink-conflict-options">
                            <button type="button" class="wink-btn wink-btn-primary" data-action="overwrite">
                                Keep My Changes
                            </button>
                            <button type="button" class="wink-btn wink-btn-secondary" data-action="reload">
                                Load Latest Version
                            </button>
                            <button type="button" class="wink-btn wink-btn-info" data-action="merge">
                                View Differences
                            </button>
                        </div>
                    </div>
                </div>
            `;
            
            // Handle conflict resolution
            modal.addEventListener('click', (e) => {
                const action = e.target.getAttribute('data-action');
                if (action) {
                    this.resolveConflict(action, data);
                    modal.remove();
                }
            });
            
            return modal;
        },

        /**
         * Resolve version conflict
         */
        resolveConflict: function(action, data) {
            switch (action) {
                case 'overwrite':
                    // Continue with current form data
                    break;
                case 'reload':
                    // Reload form with server data
                    window.location.reload();
                    break;
                case 'merge':
                    // Show diff view (would need additional implementation)
                    this.showDiffView(data);
                    break;
            }
        },

        /**
         * Get form version for conflict detection
         */
        getFormVersion: function() {
            const versionField = this.form.querySelector('input[name="_version"]');
            return versionField ? versionField.value : null;
        },

        /**
         * Handle before unload (prevent accidental navigation)
         */
        handleBeforeUnload: function(e) {
            if (this.isDirty && !this.isSubmitting) {
                e.preventDefault();
                e.returnValue = 'You have unsaved changes. Are you sure you want to leave?';
                return e.returnValue;
            }
        },

        /**
         * Format file size for display
         */
        formatFileSize: function(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        },

        /**
         * Reset form to original state
         */
        reset: function() {
            if (this.originalData) {
                // Reset form fields
                this.form.reset();
                
                // Clear validation errors
                Object.keys(this.validationErrors).forEach(field => {
                    const fieldElement = this.form.querySelector(`[name="${field}"]`);
                    if (fieldElement) {
                        this.clearFieldError(fieldElement);
                    }
                });
                
                this.validationErrors = {};
                this.isDirty = false;
                this.form.classList.remove('wink-form-dirty');
                this.hideUnsavedIndicator();
            }
        },

        /**
         * Cancel form (trigger cancel event)
         */
        cancel: function() {
            const cancelEvent = new CustomEvent('wink:form:cancel', {
                detail: { form: this.form }
            });
            this.form.dispatchEvent(cancelEvent);
        },

        /**
         * Destroy form instance
         */
        destroy: function() {
            // Clear timers
            if (this.autoSaveTimer) {
                clearInterval(this.autoSaveTimer);
            }
            
            // Remove event listeners
            window.removeEventListener('beforeunload', this.handleBeforeUnload);
            
            // Clear references
            this.fileUploads.clear();
            this.shortcuts.clear();
            
            // Remove classes
            this.form.classList.remove('wink-form-initialized', 'wink-form-dirty');
        }
    };

    // Add to WinkViews namespace
    WinkViews.FormHandler = FormHandler;

    // Auto-initialize forms with data-wink-form attribute
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('form[data-wink-form]').forEach(form => {
            const options = JSON.parse(form.getAttribute('data-wink-options') || '{}');
            FormHandler.init(form, options);
        });
    });

})(window, document);