/**
 * Wink View Generator - Advanced Modal Manager
 * 
 * Features:
 * - Stacked modal support
 * - AJAX content loading
 * - Form integration
 * - Confirmation dialogs
 * - Accessibility compliant (ARIA, focus management)
 * - Mobile-optimized with touch gestures
 * - Customizable animations
 * - Event-driven architecture
 * 
 * @version 2.0.0
 */

(function(window, document) {
    'use strict';

    // Ensure WinkViews namespace exists
    window.WinkViews = window.WinkViews || {};

    const ModalManager = {
        instances: new Map(),
        stack: [],
        zIndexBase: 1050,
        
        globalConfig: {
            backdrop: true,
            keyboard: true,
            focus: true,
            animation: true,
            animationDuration: 300,
            autoFocus: true,
            restoreFocus: true,
            closeOnEscape: true,
            closeOnBackdrop: true,
            maxHeight: '90vh',
            maxWidth: '90vw'
        },

        /**
         * Create and show a modal
         */
        create: function(options = {}) {
            const config = Object.assign({}, this.globalConfig, options);
            const modal = new ModalInstance(config);
            
            this.instances.set(modal.id, modal);
            return modal;
        },

        /**
         * Show existing modal
         */
        show: function(modalElement, options = {}) {
            let instance = this.getInstanceByElement(modalElement);
            
            if (!instance) {
                const config = Object.assign({}, this.globalConfig, options);
                instance = new ModalInstance(config, modalElement);
                this.instances.set(instance.id, instance);
            }
            
            instance.show();
            return instance;
        },

        /**
         * Hide modal
         */
        hide: function(modalElement) {
            const instance = this.getInstanceByElement(modalElement);
            if (instance) {
                instance.hide();
            }
        },

        /**
         * Get modal instance by element
         */
        getInstanceByElement: function(element) {
            for (const [id, instance] of this.instances) {
                if (instance.element === element) {
                    return instance;
                }
            }
            return null;
        },

        /**
         * Get modal instance by ID
         */
        getInstance: function(id) {
            return this.instances.get(id);
        },

        /**
         * Close all modals
         */
        closeAll: function() {
            this.stack.forEach(instance => {
                instance.hide(false);
            });
            this.stack = [];
        },

        /**
         * Create confirmation dialog
         */
        confirm: function(message, options = {}) {
            return new Promise((resolve) => {
                const config = Object.assign({
                    title: 'Confirm',
                    message: message,
                    type: 'confirm',
                    confirmText: 'OK',
                    cancelText: 'Cancel',
                    confirmClass: 'wink-btn-primary',
                    cancelClass: 'wink-btn-secondary'
                }, options);

                const modal = this.create({
                    title: config.title,
                    content: `<p>${config.message}</p>`,
                    size: 'sm',
                    footer: `
                        <button type="button" class="wink-btn ${config.cancelClass}" data-action="cancel">
                            ${config.cancelText}
                        </button>
                        <button type="button" class="wink-btn ${config.confirmClass}" data-action="confirm">
                            ${config.confirmText}
                        </button>
                    `,
                    onAction: (action) => {
                        modal.hide();
                        resolve(action === 'confirm');
                    }
                });

                modal.show();
            });
        },

        /**
         * Create alert dialog
         */
        alert: function(message, options = {}) {
            return new Promise((resolve) => {
                const config = Object.assign({
                    title: 'Alert',
                    message: message,
                    type: 'alert',
                    okText: 'OK',
                    okClass: 'wink-btn-primary'
                }, options);

                const modal = this.create({
                    title: config.title,
                    content: `<p>${config.message}</p>`,
                    size: 'sm',
                    footer: `
                        <button type="button" class="wink-btn ${config.okClass}" data-action="ok">
                            ${config.okText}
                        </button>
                    `,
                    onAction: () => {
                        modal.hide();
                        resolve();
                    }
                });

                modal.show();
            });
        },

        /**
         * Create prompt dialog
         */
        prompt: function(message, defaultValue = '', options = {}) {
            return new Promise((resolve) => {
                const config = Object.assign({
                    title: 'Input Required',
                    message: message,
                    type: 'prompt',
                    okText: 'OK',
                    cancelText: 'Cancel',
                    okClass: 'wink-btn-primary',
                    cancelClass: 'wink-btn-secondary',
                    inputType: 'text'
                }, options);

                const inputId = 'prompt-input-' + Date.now();
                const modal = this.create({
                    title: config.title,
                    content: `
                        <p>${config.message}</p>
                        <div class="wink-form-group">
                            <input type="${config.inputType}" id="${inputId}" class="wink-form-control" value="${defaultValue}" autofocus>
                        </div>
                    `,
                    size: 'sm',
                    footer: `
                        <button type="button" class="wink-btn ${config.cancelClass}" data-action="cancel">
                            ${config.cancelText}
                        </button>
                        <button type="button" class="wink-btn ${config.okClass}" data-action="ok">
                            ${config.okText}
                        </button>
                    `,
                    onAction: (action) => {
                        let result = null;
                        if (action === 'ok') {
                            const input = modal.element.querySelector('#' + inputId);
                            result = input ? input.value : null;
                        }
                        modal.hide();
                        resolve(result);
                    },
                    onShow: () => {
                        const input = modal.element.querySelector('#' + inputId);
                        if (input) {
                            input.focus();
                            input.select();
                        }
                    }
                });

                modal.show();
            });
        },

        /**
         * Load modal content via AJAX
         */
        loadModal: function(url, options = {}) {
            const config = Object.assign({
                method: 'GET',
                showLoading: true,
                loadingText: 'Loading...'
            }, options);

            const modal = this.create({
                title: config.title || 'Loading...',
                content: config.showLoading ? `<div class="wink-modal-loading">${config.loadingText}</div>` : '',
                size: config.size
            });

            modal.show();

            const headers = {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'text/html'
            };

            if (config.method !== 'GET') {
                headers['X-CSRF-TOKEN'] = WinkViews.Utils.getCsrfToken();
            }

            fetch(url, {
                method: config.method,
                headers: headers,
                body: config.data
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                return response.text();
            })
            .then(html => {
                modal.setContent(html);
                
                if (config.onLoad) {
                    config.onLoad(modal, html);
                }
            })
            .catch(error => {
                console.error('Modal load error:', error);
                modal.setContent(`<div class="wink-error">Failed to load content: ${error.message}</div>`);
                
                if (config.onError) {
                    config.onError(modal, error);
                }
            });

            return modal;
        }
    };

    /**
     * Modal Instance Class
     */
    function ModalInstance(config, existingElement = null) {
        this.id = WinkViews.Utils.generateId();
        this.config = config;
        this.element = existingElement;
        this.backdrop = null;
        this.isShown = false;
        this.isShowing = false;
        this.isHiding = false;
        this.originalFocusElement = null;
        this.scrollbarWidth = 0;
        
        if (!existingElement) {
            this.createElement();
        }
        
        this.setupEventListeners();
    }

    ModalInstance.prototype = {
        /**
         * Create modal element
         */
        createElement: function() {
            this.element = document.createElement('div');
            this.element.className = 'wink-modal';
            this.element.id = this.id;
            this.element.setAttribute('role', 'dialog');
            this.element.setAttribute('aria-modal', 'true');
            this.element.setAttribute('tabindex', '-1');
            
            if (this.config.title) {
                this.element.setAttribute('aria-labelledby', `${this.id}-title`);
            }
            
            const sizeClass = this.config.size ? `wink-modal-${this.config.size}` : '';
            
            this.element.innerHTML = `
                <div class="wink-modal-dialog ${sizeClass}">
                    <div class="wink-modal-content">
                        ${this.config.title ? `
                            <div class="wink-modal-header">
                                <h5 class="wink-modal-title" id="${this.id}-title">${this.config.title}</h5>
                                <button type="button" class="wink-modal-close" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        ` : ''}
                        <div class="wink-modal-body">
                            ${this.config.content || ''}
                        </div>
                        ${this.config.footer ? `
                            <div class="wink-modal-footer">
                                ${this.config.footer}
                            </div>
                        ` : ''}
                    </div>
                </div>
            `;
            
            document.body.appendChild(this.element);
        },

        /**
         * Setup event listeners
         */
        setupEventListeners: function() {
            // Close button
            const closeBtn = this.element.querySelector('.wink-modal-close');
            if (closeBtn) {
                closeBtn.addEventListener('click', () => this.hide());
            }

            // Backdrop click
            if (this.config.closeOnBackdrop) {
                this.element.addEventListener('click', (e) => {
                    if (e.target === this.element) {
                        this.hide();
                    }
                });
            }

            // Keyboard events
            if (this.config.keyboard) {
                this.element.addEventListener('keydown', (e) => {
                    this.handleKeydown(e);
                });
            }

            // Action buttons
            this.element.addEventListener('click', (e) => {
                const action = e.target.getAttribute('data-action');
                if (action && this.config.onAction) {
                    this.config.onAction(action, e);
                }
            });

            // Form submission
            const forms = this.element.querySelectorAll('form');
            forms.forEach(form => {
                form.addEventListener('submit', (e) => {
                    this.handleFormSubmit(e, form);
                });
            });

            // Touch gestures for mobile
            this.setupTouchGestures();
        },

        /**
         * Setup touch gestures
         */
        setupTouchGestures: function() {
            let startY = 0;
            let currentY = 0;
            let isDragging = false;

            const dialog = this.element.querySelector('.wink-modal-dialog');
            
            dialog.addEventListener('touchstart', (e) => {
                startY = e.touches[0].clientY;
                isDragging = true;
            });

            dialog.addEventListener('touchmove', (e) => {
                if (!isDragging) return;
                
                currentY = e.touches[0].clientY;
                const deltaY = currentY - startY;
                
                // Only allow swipe down to close
                if (deltaY > 0) {
                    const opacity = Math.max(0.3, 1 - (deltaY / window.innerHeight));
                    this.element.style.opacity = opacity;
                    
                    const scale = Math.max(0.8, 1 - (deltaY / window.innerHeight * 0.2));
                    dialog.style.transform = `translateY(${deltaY}px) scale(${scale})`;
                }
            });

            dialog.addEventListener('touchend', () => {
                if (!isDragging) return;
                
                const deltaY = currentY - startY;
                
                if (deltaY > window.innerHeight * 0.3) {
                    // Close modal if swiped down significantly
                    this.hide();
                } else {
                    // Snap back to original position
                    this.element.style.opacity = '';
                    dialog.style.transform = '';
                }
                
                isDragging = false;
            });
        },

        /**
         * Handle keyboard events
         */
        handleKeydown: function(e) {
            if (e.key === 'Escape' && this.config.closeOnEscape) {
                e.preventDefault();
                this.hide();
            } else if (e.key === 'Tab') {
                this.handleTabKey(e);
            }
        },

        /**
         * Handle tab key for focus trapping
         */
        handleTabKey: function(e) {
            const focusableElements = this.getFocusableElements();
            const firstElement = focusableElements[0];
            const lastElement = focusableElements[focusableElements.length - 1];

            if (e.shiftKey) {
                // Shift + Tab
                if (document.activeElement === firstElement) {
                    e.preventDefault();
                    lastElement.focus();
                }
            } else {
                // Tab
                if (document.activeElement === lastElement) {
                    e.preventDefault();
                    firstElement.focus();
                }
            }
        },

        /**
         * Get focusable elements within modal
         */
        getFocusableElements: function() {
            const selector = 'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])';
            return Array.from(this.element.querySelectorAll(selector))
                .filter(el => !el.disabled && !el.hidden && el.offsetWidth > 0 && el.offsetHeight > 0);
        },

        /**
         * Handle form submission
         */
        handleFormSubmit: function(e, form) {
            if (form.classList.contains('wink-ajax-form')) {
                e.preventDefault();
                
                if (WinkViews.FormHandler) {
                    const formInstance = WinkViews.FormHandler.getInstance(form.id) || 
                                       WinkViews.FormHandler.init(form);
                    
                    formInstance.submit({
                        onSuccess: (data) => {
                            if (this.config.onFormSuccess) {
                                this.config.onFormSuccess(data, this);
                            } else {
                                this.hide();
                            }
                        },
                        onError: (errors, status) => {
                            if (this.config.onFormError) {
                                this.config.onFormError(errors, status, this);
                            }
                        }
                    });
                } else {
                    // Fallback to basic AJAX
                    WinkViews.Forms.submitAjax(form, {
                        onSuccess: (data) => {
                            if (this.config.onFormSuccess) {
                                this.config.onFormSuccess(data, this);
                            } else {
                                this.hide();
                            }
                        }
                    });
                }
            }
        },

        /**
         * Show modal
         */
        show: function() {
            if (this.isShown || this.isShowing) return;
            
            this.isShowing = true;
            
            // Store original focus
            if (this.config.restoreFocus) {
                this.originalFocusElement = document.activeElement;
            }
            
            // Add to stack
            ModalManager.stack.push(this);
            
            // Calculate z-index
            const zIndex = ModalManager.zIndexBase + (ModalManager.stack.length - 1) * 10;
            
            // Create backdrop
            if (this.config.backdrop) {
                this.createBackdrop(zIndex - 1);
            }
            
            // Set modal z-index
            this.element.style.zIndex = zIndex;
            
            // Prevent body scroll
            this.preventBodyScroll();
            
            // Show modal
            this.element.style.display = 'block';
            this.element.classList.add('wink-modal-showing');
            
            // Trigger show event
            this.triggerEvent('show');
            
            if (this.config.animation) {
                // Animate in
                requestAnimationFrame(() => {
                    this.element.classList.add('wink-modal-show');
                    
                    setTimeout(() => {
                        this.isShowing = false;
                        this.isShown = true;
                        this.element.classList.remove('wink-modal-showing');
                        this.triggerEvent('shown');
                        
                        // Focus management
                        if (this.config.autoFocus) {
                            this.setInitialFocus();
                        }
                        
                        if (this.config.onShow) {
                            this.config.onShow(this);
                        }
                    }, this.config.animationDuration);
                });
            } else {
                this.element.classList.add('wink-modal-show');
                this.isShowing = false;
                this.isShown = true;
                this.triggerEvent('shown');
                
                if (this.config.autoFocus) {
                    this.setInitialFocus();
                }
                
                if (this.config.onShow) {
                    this.config.onShow(this);
                }
            }
        },

        /**
         * Hide modal
         */
        hide: function(restoreFocus = true) {
            if (!this.isShown || this.isHiding) return;
            
            this.isHiding = true;
            this.element.classList.add('wink-modal-hiding');
            
            // Trigger hide event
            this.triggerEvent('hide');
            
            if (this.config.animation) {
                this.element.classList.remove('wink-modal-show');
                
                setTimeout(() => {
                    this.completeHide(restoreFocus);
                }, this.config.animationDuration);
            } else {
                this.element.classList.remove('wink-modal-show');
                this.completeHide(restoreFocus);
            }
        },

        /**
         * Complete hide process
         */
        completeHide: function(restoreFocus) {
            this.element.style.display = 'none';
            this.element.classList.remove('wink-modal-hiding');
            
            // Remove from stack
            const index = ModalManager.stack.indexOf(this);
            if (index > -1) {
                ModalManager.stack.splice(index, 1);
            }
            
            // Remove backdrop
            if (this.backdrop) {
                this.backdrop.remove();
                this.backdrop = null;
            }
            
            // Restore body scroll if no other modals
            if (ModalManager.stack.length === 0) {
                this.restoreBodyScroll();
            }
            
            // Restore focus
            if (restoreFocus && this.config.restoreFocus && this.originalFocusElement) {
                this.originalFocusElement.focus();
            }
            
            this.isHiding = false;
            this.isShown = false;
            
            this.triggerEvent('hidden');
            
            if (this.config.onHide) {
                this.config.onHide(this);
            }
        },

        /**
         * Create backdrop
         */
        createBackdrop: function(zIndex) {
            this.backdrop = document.createElement('div');
            this.backdrop.className = 'wink-modal-backdrop';
            this.backdrop.style.zIndex = zIndex;
            
            if (this.config.closeOnBackdrop) {
                this.backdrop.addEventListener('click', () => this.hide());
            }
            
            document.body.appendChild(this.backdrop);
            
            if (this.config.animation) {
                requestAnimationFrame(() => {
                    this.backdrop.classList.add('wink-modal-backdrop-show');
                });
            } else {
                this.backdrop.classList.add('wink-modal-backdrop-show');
            }
        },

        /**
         * Prevent body scroll
         */
        preventBodyScroll: function() {
            if (ModalManager.stack.length === 1) {
                this.scrollbarWidth = this.getScrollbarWidth();
                document.body.style.overflow = 'hidden';
                document.body.style.paddingRight = this.scrollbarWidth + 'px';
            }
        },

        /**
         * Restore body scroll
         */
        restoreBodyScroll: function() {
            document.body.style.overflow = '';
            document.body.style.paddingRight = '';
        },

        /**
         * Get scrollbar width
         */
        getScrollbarWidth: function() {
            const outer = document.createElement('div');
            outer.style.visibility = 'hidden';
            outer.style.overflow = 'scroll';
            outer.style.msOverflowStyle = 'scrollbar';
            document.body.appendChild(outer);
            
            const inner = document.createElement('div');
            outer.appendChild(inner);
            
            const scrollbarWidth = outer.offsetWidth - inner.offsetWidth;
            
            outer.remove();
            return scrollbarWidth;
        },

        /**
         * Set initial focus
         */
        setInitialFocus: function() {
            const autofocusElement = this.element.querySelector('[autofocus]');
            if (autofocusElement) {
                autofocusElement.focus();
                return;
            }
            
            const focusableElements = this.getFocusableElements();
            if (focusableElements.length > 0) {
                focusableElements[0].focus();
            } else {
                this.element.focus();
            }
        },

        /**
         * Set modal content
         */
        setContent: function(content) {
            const body = this.element.querySelector('.wink-modal-body');
            if (body) {
                body.innerHTML = content;
                
                // Re-setup event listeners for new content
                this.setupEventListeners();
                
                // Initialize any new form handlers
                const forms = body.querySelectorAll('form[data-wink-form]');
                forms.forEach(form => {
                    if (WinkViews.FormHandler) {
                        WinkViews.FormHandler.init(form);
                    }
                });
            }
        },

        /**
         * Set modal title
         */
        setTitle: function(title) {
            const titleElement = this.element.querySelector('.wink-modal-title');
            if (titleElement) {
                titleElement.textContent = title;
            } else if (title) {
                // Create header if it doesn't exist
                const content = this.element.querySelector('.wink-modal-content');
                const header = document.createElement('div');
                header.className = 'wink-modal-header';
                header.innerHTML = `
                    <h5 class="wink-modal-title" id="${this.id}-title">${title}</h5>
                    <button type="button" class="wink-modal-close" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                `;
                content.insertBefore(header, content.firstChild);
                this.element.setAttribute('aria-labelledby', `${this.id}-title`);
            }
        },

        /**
         * Set modal footer
         */
        setFooter: function(footer) {
            let footerElement = this.element.querySelector('.wink-modal-footer');
            
            if (footer) {
                if (!footerElement) {
                    footerElement = document.createElement('div');
                    footerElement.className = 'wink-modal-footer';
                    this.element.querySelector('.wink-modal-content').appendChild(footerElement);
                }
                footerElement.innerHTML = footer;
            } else if (footerElement) {
                footerElement.remove();
            }
        },

        /**
         * Trigger custom event
         */
        triggerEvent: function(eventName) {
            const event = new CustomEvent(`wink:modal:${eventName}`, {
                detail: { modal: this }
            });
            this.element.dispatchEvent(event);
        },

        /**
         * Destroy modal instance
         */
        destroy: function() {
            this.hide(false);
            
            setTimeout(() => {
                if (this.element && this.element.parentNode) {
                    this.element.remove();
                }
                
                ModalManager.instances.delete(this.id);
            }, this.config.animationDuration);
        }
    };

    // Add to WinkViews namespace
    WinkViews.ModalManager = ModalManager;

    // Auto-initialize modals with data-wink-modal attribute
    document.addEventListener('DOMContentLoaded', function() {
        // Modal triggers
        document.addEventListener('click', function(e) {
            const trigger = e.target.closest('[data-wink-modal]');
            if (!trigger) return;
            
            e.preventDefault();
            
            const target = trigger.getAttribute('data-wink-modal');
            const modalElement = document.querySelector(target);
            
            if (modalElement) {
                const options = JSON.parse(trigger.getAttribute('data-wink-options') || '{}');
                ModalManager.show(modalElement, options);
            }
        });

        // AJAX modal triggers
        document.addEventListener('click', function(e) {
            const trigger = e.target.closest('[data-wink-modal-url]');
            if (!trigger) return;
            
            e.preventDefault();
            
            const url = trigger.getAttribute('data-wink-modal-url');
            const options = JSON.parse(trigger.getAttribute('data-wink-options') || '{}');
            
            ModalManager.loadModal(url, options);
        });

        // Quick confirmation triggers
        document.addEventListener('click', function(e) {
            const trigger = e.target.closest('[data-wink-confirm]');
            if (!trigger) return;
            
            const message = trigger.getAttribute('data-wink-confirm');
            const options = JSON.parse(trigger.getAttribute('data-wink-options') || '{}');
            
            ModalManager.confirm(message, options).then(confirmed => {
                if (confirmed) {
                    // If it's a link, follow it
                    if (trigger.tagName === 'A' && trigger.href) {
                        window.location.href = trigger.href;
                    }
                    // If it's a form button, submit the form
                    else if (trigger.form) {
                        trigger.form.submit();
                    }
                    // If it has a custom action
                    else if (options.onConfirm) {
                        options.onConfirm(trigger);
                    }
                }
            });
            
            e.preventDefault();
        });
    });

})(window, document);