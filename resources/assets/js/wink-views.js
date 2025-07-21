/**
 * Wink View Generator - Core JavaScript
 * Framework-agnostic utilities for enhanced user experience
 * 
 * Features:
 * - Progressive enhancement
 * - Framework compatibility (Bootstrap, Tailwind, Custom)
 * - Mobile-optimized interactions
 * - Accessibility support
 * - Error handling and retry mechanisms
 * 
 * @version 2.0.0
 */

(function(window, document) {
    'use strict';

    // Namespace initialization
    window.WinkViews = window.WinkViews || {};

    // Utility functions
    const Utils = {
        /**
         * Debounce function calls
         */
        debounce: function(func, wait, immediate) {
            let timeout;
            return function executedFunction() {
                const context = this;
                const args = arguments;
                const later = function() {
                    timeout = null;
                    if (!immediate) func.apply(context, args);
                };
                const callNow = immediate && !timeout;
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
                if (callNow) func.apply(context, args);
            };
        },

        /**
         * Throttle function calls
         */
        throttle: function(func, limit) {
            let inThrottle;
            return function() {
                const args = arguments;
                const context = this;
                if (!inThrottle) {
                    func.apply(context, args);
                    inThrottle = true;
                    setTimeout(() => inThrottle = false, limit);
                }
            };
        },

        /**
         * Check if element is in viewport
         */
        isInViewport: function(element) {
            const rect = element.getBoundingClientRect();
            return (
                rect.top >= 0 &&
                rect.left >= 0 &&
                rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
                rect.right <= (window.innerWidth || document.documentElement.clientWidth)
            );
        },

        /**
         * Generate unique ID
         */
        generateId: function() {
            return 'wink_' + Math.random().toString(36).substr(2, 9);
        },

        /**
         * Get CSRF token from meta tag or form
         */
        getCsrfToken: function() {
            const metaToken = document.querySelector('meta[name="csrf-token"]');
            if (metaToken) return metaToken.getAttribute('content');
            
            const formToken = document.querySelector('input[name="_token"]');
            if (formToken) return formToken.value;
            
            return null;
        },

        /**
         * Show notification with framework detection
         */
        showNotification: function(message, type = 'info', duration = 5000) {
            // Try Bootstrap toast
            if (typeof bootstrap !== 'undefined' && bootstrap.Toast) {
                this.showBootstrapToast(message, type, duration);
                return;
            }

            // Fallback to custom notification
            this.showCustomNotification(message, type, duration);
        },

        showBootstrapToast: function(message, type, duration) {
            const toastContainer = document.querySelector('.toast-container') || this.createToastContainer();
            const toastEl = document.createElement('div');
            toastEl.className = `toast align-items-center text-white bg-${type === 'error' ? 'danger' : type} border-0`;
            toastEl.setAttribute('role', 'alert');
            toastEl.innerHTML = `
                <div class="d-flex">
                    <div class="toast-body">${message}</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            `;
            toastContainer.appendChild(toastEl);
            const toast = new bootstrap.Toast(toastEl, { delay: duration });
            toast.show();
        },

        showCustomNotification: function(message, type, duration) {
            const notification = document.createElement('div');
            notification.className = `wink-notification wink-notification-${type}`;
            notification.innerHTML = `
                <span class="wink-notification-message">${message}</span>
                <button class="wink-notification-close" type="button">&times;</button>
            `;
            
            document.body.appendChild(notification);
            
            // Auto remove
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, duration);
            
            // Manual close
            notification.querySelector('.wink-notification-close').addEventListener('click', () => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            });
        },

        createToastContainer: function() {
            const container = document.createElement('div');
            container.className = 'toast-container position-fixed top-0 end-0 p-3';
            container.style.zIndex = '1055';
            document.body.appendChild(container);
            return container;
        }
    };

    // Enhanced Forms module
    WinkViews.Forms = {
        retryAttempts: 3,
        retryDelay: 1000,

        /**
         * Submit form via AJAX with retry mechanism
         */
        submitAjax: function(formElement, options = {}) {
            const defaultOptions = {
                method: formElement.method || 'POST',
                onSuccess: (data) => {
                    Utils.showNotification('Operation completed successfully', 'success');
                    if (data.redirect) {
                        window.location.href = data.redirect;
                    } else {
                        window.location.reload();
                    }
                },
                onError: (errors, status) => this.handleErrors(formElement, errors, status),
                loadingClass: 'wink-loading',
                retries: this.retryAttempts,
                beforeSend: () => {},
                afterSend: () => {}
            };
            
            const config = Object.assign(defaultOptions, options);
            
            this._submitWithRetry(formElement, config, 0);
        },

        _submitWithRetry: function(formElement, config, attempt) {
            // Add loading state
            formElement.classList.add(config.loadingClass);
            config.beforeSend();
            
            const formData = new FormData(formElement);
            const csrfToken = Utils.getCsrfToken();
            
            const headers = {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            };
            
            if (csrfToken) {
                headers['X-CSRF-TOKEN'] = csrfToken;
            }
            
            fetch(formElement.action, {
                method: config.method,
                body: formData,
                headers: headers
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                return response.json();
            })
            .then(data => {
                formElement.classList.remove(config.loadingClass);
                config.afterSend();
                
                if (data.success) {
                    config.onSuccess(data);
                } else {
                    config.onError(data.errors || {}, data.status);
                }
            })
            .catch(error => {
                formElement.classList.remove(config.loadingClass);
                config.afterSend();
                
                if (attempt < config.retries - 1) {
                    Utils.showNotification(`Request failed, retrying... (${attempt + 1}/${config.retries})`, 'warning', 2000);
                    setTimeout(() => {
                        this._submitWithRetry(formElement, config, attempt + 1);
                    }, this.retryDelay * Math.pow(2, attempt)); // Exponential backoff
                } else {
                    console.error('Form submission error:', error);
                    Utils.showNotification('Request failed after multiple attempts. Please try again.', 'error');
                    config.onError({}, 500);
                }
            });
        },

        /**
         * Enhanced error handling
         */
        handleErrors: function(formElement, errors, status) {
            this.clearErrors(formElement);
            
            if (status === 422) {
                this.displayValidationErrors(formElement, errors);
            } else if (status === 419) {
                Utils.showNotification('Session expired. Please refresh the page.', 'warning');
            } else if (status >= 500) {
                Utils.showNotification('Server error occurred. Please try again later.', 'error');
            } else {
                Utils.showNotification('An error occurred. Please try again.', 'error');
            }
        },

        /**
         * Display validation errors with framework detection
         */
        displayValidationErrors: function(formElement, errors) {
            Object.keys(errors).forEach(field => {
                const input = formElement.querySelector(`[name="${field}"], [name="${field}[]"]`);
                if (input) {
                    this.markFieldInvalid(input);
                    this.addErrorMessage(input, errors[field][0]);
                }
            });
        },

        markFieldInvalid: function(input) {
            input.classList.add('is-invalid', 'wink-invalid');
            
            // Bootstrap support
            if (input.closest('.form-group') || input.closest('.mb-3')) {
                input.classList.add('is-invalid');
            }
            
            // Tailwind support
            if (input.classList.contains('border-gray-300')) {
                input.classList.remove('border-gray-300');
                input.classList.add('border-red-500');
            }
        },

        addErrorMessage: function(input, message) {
            const errorDiv = document.createElement('div');
            errorDiv.className = 'wink-error-message invalid-feedback text-red-500 text-sm mt-1';
            errorDiv.textContent = message;
            
            // Find appropriate parent for error message
            const parent = input.closest('.form-group') || 
                          input.closest('.mb-3') || 
                          input.closest('.field') || 
                          input.parentNode;
            
            parent.appendChild(errorDiv);
        },

        clearErrors: function(formElement) {
            // Remove error classes
            formElement.querySelectorAll('.is-invalid, .wink-invalid').forEach(el => {
                el.classList.remove('is-invalid', 'wink-invalid', 'border-red-500');
                if (el.classList.contains('border-red-500')) {
                    el.classList.add('border-gray-300');
                }
            });
            
            // Remove error messages
            formElement.querySelectorAll('.wink-error-message, .invalid-feedback').forEach(el => {
                if (el.classList.contains('wink-error-message') || el.textContent.trim()) {
                    el.remove();
                }
            });
        },

        /**
         * Real-time validation
         */
        enableRealTimeValidation: function(formElement) {
            const inputs = formElement.querySelectorAll('input, textarea, select');
            
            inputs.forEach(input => {
                const debouncedValidate = Utils.debounce(() => {
                    this.validateField(input);
                }, 500);
                
                input.addEventListener('blur', () => this.validateField(input));
                input.addEventListener('input', debouncedValidate);
            });
        },

        validateField: function(input) {
            // Clear existing errors for this field
            const existingError = input.parentNode.querySelector('.wink-error-message');
            if (existingError) existingError.remove();
            
            input.classList.remove('is-invalid', 'wink-invalid', 'border-red-500');
            
            // Basic HTML5 validation
            if (!input.checkValidity()) {
                this.markFieldInvalid(input);
                this.addErrorMessage(input, input.validationMessage);
                return false;
            }
            
            // Custom validation rules can be added here
            return true;
        }
    };

    // Enhanced Tables module (moved to table-manager.js for better organization)
    WinkViews.Tables = {
        /**
         * Basic table initialization for backward compatibility
         */
        initSortable: function(tableSelector = '.wink-sortable-table') {
            if (WinkViews.TableManager) {
                return WinkViews.TableManager.initSortable(tableSelector);
            }
            
            // Fallback implementation
            document.querySelectorAll(tableSelector).forEach(table => {
                const headers = table.querySelectorAll('th[data-sortable]');
                
                headers.forEach(header => {
                    header.style.cursor = 'pointer';
                    header.addEventListener('click', () => {
                        const field = header.dataset.sortable;
                        const currentDirection = new URLSearchParams(window.location.search).get('direction');
                        const newDirection = currentDirection === 'asc' ? 'desc' : 'asc';
                        
                        const url = new URL(window.location);
                        url.searchParams.set('sort', field);
                        url.searchParams.set('direction', newDirection);
                        
                        window.location.href = url.toString();
                    });
                });
            });
        },

        initBulkActions: function() {
            if (WinkViews.TableManager) {
                return WinkViews.TableManager.initBulkActions();
            }
            
            // Fallback implementation
            const selectAll = document.querySelector('.bulk-select-all');
            const checkboxes = document.querySelectorAll('.bulk-select-item');
            const bulkActions = document.querySelector('.bulk-actions');
            
            if (selectAll) {
                selectAll.addEventListener('change', function() {
                    checkboxes.forEach(checkbox => {
                        checkbox.checked = this.checked;
                    });
                    toggleBulkActions();
                });
            }
            
            checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', toggleBulkActions);
            });
            
            function toggleBulkActions() {
                const selected = document.querySelectorAll('.bulk-select-item:checked');
                if (bulkActions) {
                    bulkActions.style.display = selected.length > 0 ? 'block' : 'none';
                }
            }
        }
    };

    // Accessibility helpers
    WinkViews.A11y = {
        /**
         * Enhance keyboard navigation
         */
        enhanceKeyboardNavigation: function() {
            // Add keyboard support for clickable elements
            document.querySelectorAll('[data-clickable]').forEach(element => {
                if (!element.hasAttribute('tabindex')) {
                    element.setAttribute('tabindex', '0');
                }
                
                element.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        this.click();
                    }
                });
            });
        },

        /**
         * Announce dynamic content changes to screen readers
         */
        announceChange: function(message) {
            const announcer = document.getElementById('wink-announcer') || this.createAnnouncer();
            announcer.textContent = message;
        },

        createAnnouncer: function() {
            const announcer = document.createElement('div');
            announcer.id = 'wink-announcer';
            announcer.setAttribute('aria-live', 'polite');
            announcer.setAttribute('aria-atomic', 'true');
            announcer.style.cssText = 'position:absolute;left:-10000px;width:1px;height:1px;overflow:hidden;';
            document.body.appendChild(announcer);
            return announcer;
        },

        /**
         * Focus management for dynamic content
         */
        manageFocus: function(element) {
            if (element && typeof element.focus === 'function') {
                element.focus();
                element.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }
    };

    // Touch/mobile enhancements
    WinkViews.Touch = {
        /**
         * Add touch-friendly interactions
         */
        enhanceTouch: function() {
            // Add touch feedback to buttons
            document.querySelectorAll('.wink-btn, button, [role="button"]').forEach(button => {
                button.addEventListener('touchstart', function() {
                    this.classList.add('wink-touch-active');
                });
                
                button.addEventListener('touchend', function() {
                    setTimeout(() => {
                        this.classList.remove('wink-touch-active');
                    }, 150);
                });
            });

            // Improve touch scrolling for tables
            document.querySelectorAll('.wink-table-responsive').forEach(container => {
                container.style.webkitOverflowScrolling = 'touch';
            });
        },

        /**
         * Handle swipe gestures for mobile tables
         */
        initSwipeGestures: function() {
            let startX, startY, currentX, currentY;
            
            document.querySelectorAll('.wink-table-responsive').forEach(container => {
                container.addEventListener('touchstart', function(e) {
                    startX = e.touches[0].clientX;
                    startY = e.touches[0].clientY;
                });
                
                container.addEventListener('touchmove', function(e) {
                    if (!startX || !startY) return;
                    
                    currentX = e.touches[0].clientX;
                    currentY = e.touches[0].clientY;
                    
                    const deltaX = Math.abs(currentX - startX);
                    const deltaY = Math.abs(currentY - startY);
                    
                    // Prevent vertical scroll when swiping horizontally
                    if (deltaX > deltaY) {
                        e.preventDefault();
                    }
                });
            });
        }
    };

    // Performance monitoring
    WinkViews.Performance = {
        marks: {},
        
        /**
         * Start performance measurement
         */
        mark: function(name) {
            this.marks[name] = performance.now();
        },
        
        /**
         * End performance measurement and log
         */
        measure: function(name) {
            if (this.marks[name]) {
                const duration = performance.now() - this.marks[name];
                console.debug(`WinkViews Performance - ${name}: ${duration.toFixed(2)}ms`);
                delete this.marks[name];
                return duration;
            }
        }
    };

    // Expose utilities globally
    WinkViews.Utils = Utils;

    // Auto-initialization
    function initializeWinkViews() {
        WinkViews.Performance.mark('initialization');
        
        // Initialize core features
        WinkViews.Tables.initSortable();
        WinkViews.Tables.initBulkActions();
        WinkViews.A11y.enhanceKeyboardNavigation();
        WinkViews.Touch.enhanceTouch();
        WinkViews.Touch.initSwipeGestures();
        
        // Auto-submit AJAX forms
        document.querySelectorAll('.wink-ajax-form').forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                WinkViews.Forms.submitAjax(this);
            });
        });
        
        // Enable real-time validation on forms with the class
        document.querySelectorAll('.wink-realtime-validation').forEach(form => {
            WinkViews.Forms.enableRealTimeValidation(form);
        });
        
        WinkViews.Performance.measure('initialization');
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializeWinkViews);
    } else {
        initializeWinkViews();
    }

    // Re-initialize when new content is loaded dynamically
    WinkViews.reinitialize = initializeWinkViews;

})(window, document);