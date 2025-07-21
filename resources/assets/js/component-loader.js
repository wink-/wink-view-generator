/**
 * Wink View Generator - Dynamic Component Loader
 * 
 * Features:
 * - Lazy loading of components
 * - Intersection Observer for performance
 * - Dynamic script and style loading
 * - Component lifecycle management
 * - Error handling and fallbacks
 * - Event-driven architecture
 * 
 * @version 2.0.0
 */

(function(window, document) {
    'use strict';

    window.WinkViews = window.WinkViews || {};

    const ComponentLoader = {
        instances: new Map(),
        loadedComponents: new Set(),
        loadedScripts: new Set(),
        loadedStyles: new Set(),
        observer: null,
        
        globalConfig: {
            rootMargin: '50px',
            threshold: 0.1,
            enableLazyLoading: true,
            retryAttempts: 3,
            retryDelay: 1000,
            timeout: 10000
        },

        /**
         * Initialize component loader
         */
        init: function(options = {}) {
            this.config = Object.assign({}, this.globalConfig, options);
            
            if (this.config.enableLazyLoading && 'IntersectionObserver' in window) {
                this.setupIntersectionObserver();
            }
            
            this.scanForComponents();
            this.setupEventListeners();
        },

        /**
         * Setup intersection observer for lazy loading
         */
        setupIntersectionObserver: function() {
            this.observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        this.loadComponent(entry.target);
                        this.observer.unobserve(entry.target);
                    }
                });
            }, {
                rootMargin: this.config.rootMargin,
                threshold: this.config.threshold
            });
        },

        /**
         * Setup event listeners
         */
        setupEventListeners: function() {
            // Handle dynamic content additions
            document.addEventListener('wink:content:added', (e) => {
                this.scanForComponents(e.detail.container);
            });
            
            // Handle component reload requests
            document.addEventListener('wink:component:reload', (e) => {
                this.reloadComponent(e.detail.element);
            });
        },

        /**
         * Scan for components to load
         */
        scanForComponents: function(container = document) {
            const components = container.querySelectorAll('[data-wink-component]');
            
            components.forEach(element => {
                if (!element.hasAttribute('data-wink-loaded')) {
                    this.registerComponent(element);
                }
            });
        },

        /**
         * Register a component for loading
         */
        registerComponent: function(element) {
            const componentName = element.getAttribute('data-wink-component');
            const loadType = element.getAttribute('data-wink-load') || 'lazy';
            const id = element.id || WinkViews.Utils.generateId();
            
            element.id = id;
            
            const component = {
                id: id,
                name: componentName,
                element: element,
                loadType: loadType,
                config: this.parseComponentConfig(element),
                state: 'registered'
            };
            
            this.instances.set(id, component);
            
            // Determine loading strategy
            switch (loadType) {
                case 'immediate':
                    this.loadComponent(element);
                    break;
                case 'lazy':
                    if (this.observer) {
                        this.observer.observe(element);
                    } else {
                        // Fallback for browsers without IntersectionObserver
                        this.loadComponent(element);
                    }
                    break;
                case 'manual':
                    // Wait for manual trigger
                    break;
                case 'event':
                    this.setupEventTrigger(component);
                    break;
            }
        },

        /**
         * Parse component configuration
         */
        parseComponentConfig: function(element) {
            const config = {};
            
            // Parse data attributes
            Array.from(element.attributes).forEach(attr => {
                if (attr.name.startsWith('data-wink-config-')) {
                    const key = attr.name.replace('data-wink-config-', '').replace(/-([a-z])/g, (g) => g[1].toUpperCase());
                    config[key] = attr.value;
                }
            });
            
            // Parse JSON config
            const jsonConfig = element.getAttribute('data-wink-config');
            if (jsonConfig) {
                try {
                    Object.assign(config, JSON.parse(jsonConfig));
                } catch (e) {
                    console.warn('Invalid component config JSON:', jsonConfig);
                }
            }
            
            return config;
        },

        /**
         * Setup event trigger for component
         */
        setupEventTrigger: function(component) {
            const triggerEvent = component.config.triggerEvent || 'click';
            const triggerSelector = component.config.triggerSelector;
            
            const handler = (e) => {
                if (!triggerSelector || e.target.matches(triggerSelector)) {
                    this.loadComponent(component.element);
                }
            };
            
            if (triggerSelector) {
                document.addEventListener(triggerEvent, handler);
            } else {
                component.element.addEventListener(triggerEvent, handler);
            }
        },

        /**
         * Load a component
         */
        loadComponent: function(element, attempt = 0) {
            const component = this.instances.get(element.id);
            if (!component || component.state === 'loaded' || component.state === 'loading') {
                return Promise.resolve(component);
            }
            
            component.state = 'loading';
            element.classList.add('wink-component-loading');
            
            // Show loading indicator
            this.showLoadingIndicator(element);
            
            return this.loadComponentAssets(component)
                .then(() => this.initializeComponent(component))
                .then(() => {
                    component.state = 'loaded';
                    element.classList.remove('wink-component-loading');
                    element.classList.add('wink-component-loaded');
                    element.setAttribute('data-wink-loaded', 'true');
                    this.hideLoadingIndicator(element);
                    
                    // Trigger loaded event
                    this.triggerComponentEvent(component, 'loaded');
                    
                    return component;
                })
                .catch(error => {
                    component.state = 'error';
                    element.classList.remove('wink-component-loading');
                    element.classList.add('wink-component-error');
                    this.hideLoadingIndicator(element);
                    
                    console.error(`Failed to load component ${component.name}:`, error);
                    
                    // Retry logic
                    if (attempt < this.config.retryAttempts) {
                        console.log(`Retrying component load (${attempt + 1}/${this.config.retryAttempts})`);
                        
                        return new Promise(resolve => {
                            setTimeout(() => {
                                resolve(this.loadComponent(element, attempt + 1));
                            }, this.config.retryDelay * Math.pow(2, attempt));
                        });
                    } else {
                        this.showErrorIndicator(element, error);
                        this.triggerComponentEvent(component, 'error', { error });
                        throw error;
                    }
                });
        },

        /**
         * Load component assets (scripts and styles)
         */
        loadComponentAssets: function(component) {
            const promises = [];
            
            // Load CSS
            const cssUrl = component.config.css || component.config.style;
            if (cssUrl) {
                promises.push(this.loadStylesheet(cssUrl));
            }
            
            // Load JavaScript
            const jsUrl = component.config.js || component.config.script;
            if (jsUrl) {
                promises.push(this.loadScript(jsUrl));
            }
            
            // Load template
            const templateUrl = component.config.template;
            if (templateUrl) {
                promises.push(this.loadTemplate(templateUrl, component));
            }
            
            return Promise.all(promises);
        },

        /**
         * Load stylesheet
         */
        loadStylesheet: function(url) {
            if (this.loadedStyles.has(url)) {
                return Promise.resolve();
            }
            
            return new Promise((resolve, reject) => {
                const link = document.createElement('link');
                link.rel = 'stylesheet';
                link.href = url;
                
                link.onload = () => {
                    this.loadedStyles.add(url);
                    resolve();
                };
                
                link.onerror = () => {
                    reject(new Error(`Failed to load stylesheet: ${url}`));
                };
                
                document.head.appendChild(link);
                
                // Timeout
                setTimeout(() => {
                    reject(new Error(`Stylesheet load timeout: ${url}`));
                }, this.config.timeout);
            });
        },

        /**
         * Load script
         */
        loadScript: function(url) {
            if (this.loadedScripts.has(url)) {
                return Promise.resolve();
            }
            
            return new Promise((resolve, reject) => {
                const script = document.createElement('script');
                script.src = url;
                script.async = true;
                
                script.onload = () => {
                    this.loadedScripts.add(url);
                    resolve();
                };
                
                script.onerror = () => {
                    reject(new Error(`Failed to load script: ${url}`));
                };
                
                document.head.appendChild(script);
                
                // Timeout
                setTimeout(() => {
                    reject(new Error(`Script load timeout: ${url}`));
                }, this.config.timeout);
            });
        },

        /**
         * Load template
         */
        loadTemplate: function(url, component) {
            return fetch(url)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                    }
                    return response.text();
                })
                .then(html => {
                    component.template = html;
                    return html;
                });
        },

        /**
         * Initialize component
         */
        initializeComponent: function(component) {
            // Insert template if available
            if (component.template) {
                component.element.innerHTML = component.template;
            }
            
            // Initialize component class if available
            const ComponentClass = this.getComponentClass(component.name);
            if (ComponentClass) {
                component.instance = new ComponentClass(component.element, component.config);
            }
            
            // Initialize built-in components
            this.initializeBuiltInComponents(component);
            
            // Custom initialization
            if (component.config.init && typeof window[component.config.init] === 'function') {
                window[component.config.init](component.element, component.config);
            }
            
            return component;
        },

        /**
         * Get component class by name
         */
        getComponentClass: function(name) {
            // Convert kebab-case to PascalCase
            const className = name.replace(/-([a-z])/g, (g) => g[1].toUpperCase())
                                 .replace(/^([a-z])/, (g) => g.toUpperCase());
            
            // Look for component class in various namespaces
            const namespaces = [
                window.WinkViews,
                window.WinkComponents,
                window
            ];
            
            for (const namespace of namespaces) {
                if (namespace && namespace[className]) {
                    return namespace[className];
                }
                if (namespace && namespace[className + 'Component']) {
                    return namespace[className + 'Component'];
                }
            }
            
            return null;
        },

        /**
         * Initialize built-in components
         */
        initializeBuiltInComponents: function(component) {
            switch (component.name) {
                case 'table':
                    if (WinkViews.TableManager) {
                        const table = component.element.querySelector('table');
                        if (table) {
                            WinkViews.TableManager.init(table, component.config);
                        }
                    }
                    break;
                    
                case 'form':
                    if (WinkViews.FormHandler) {
                        const form = component.element.querySelector('form');
                        if (form) {
                            WinkViews.FormHandler.init(form, component.config);
                        }
                    }
                    break;
                    
                case 'search':
                    if (WinkViews.SearchManager) {
                        const input = component.element.querySelector('input[type="search"], input[type="text"]');
                        if (input) {
                            WinkViews.SearchManager.init(input, component.config);
                        }
                    }
                    break;
                    
                case 'modal':
                    if (WinkViews.ModalManager) {
                        const modal = component.element.querySelector('.wink-modal');
                        if (modal) {
                            WinkViews.ModalManager.show(modal, component.config);
                        }
                    }
                    break;
            }
        },

        /**
         * Reload a component
         */
        reloadComponent: function(element) {
            const component = this.instances.get(element.id);
            if (!component) return;
            
            // Destroy existing instance
            if (component.instance && typeof component.instance.destroy === 'function') {
                component.instance.destroy();
            }
            
            // Reset state
            component.state = 'registered';
            element.classList.remove('wink-component-loaded', 'wink-component-error');
            element.removeAttribute('data-wink-loaded');
            
            // Reload
            return this.loadComponent(element);
        },

        /**
         * Manually load a component
         */
        loadComponentManually: function(elementOrId) {
            const element = typeof elementOrId === 'string' 
                ? document.getElementById(elementOrId)
                : elementOrId;
            
            if (!element) {
                throw new Error('Component element not found');
            }
            
            return this.loadComponent(element);
        },

        /**
         * Show loading indicator
         */
        showLoadingIndicator: function(element) {
            const existing = element.querySelector('.wink-component-loader');
            if (existing) return;
            
            const loader = document.createElement('div');
            loader.className = 'wink-component-loader';
            loader.innerHTML = `
                <div class="wink-loader-spinner"></div>
                <div class="wink-loader-text">Loading component...</div>
            `;
            
            element.appendChild(loader);
        },

        /**
         * Hide loading indicator
         */
        hideLoadingIndicator: function(element) {
            const loader = element.querySelector('.wink-component-loader');
            if (loader) {
                loader.remove();
            }
        },

        /**
         * Show error indicator
         */
        showErrorIndicator: function(element, error) {
            const existing = element.querySelector('.wink-component-error-message');
            if (existing) return;
            
            const errorDiv = document.createElement('div');
            errorDiv.className = 'wink-component-error-message';
            errorDiv.innerHTML = `
                <div class="wink-error-icon">⚠️</div>
                <div class="wink-error-text">
                    <div class="wink-error-title">Failed to load component</div>
                    <div class="wink-error-details">${error.message}</div>
                    <button type="button" class="wink-btn wink-btn-sm wink-retry-component">Retry</button>
                </div>
            `;
            
            // Retry button
            errorDiv.querySelector('.wink-retry-component').addEventListener('click', () => {
                errorDiv.remove();
                this.reloadComponent(element);
            });
            
            element.appendChild(errorDiv);
        },

        /**
         * Trigger component event
         */
        triggerComponentEvent: function(component, eventName, detail = {}) {
            const event = new CustomEvent(`wink:component:${eventName}`, {
                detail: Object.assign({ component }, detail)
            });
            component.element.dispatchEvent(event);
        },

        /**
         * Get component instance
         */
        getComponent: function(elementOrId) {
            const element = typeof elementOrId === 'string' 
                ? document.getElementById(elementOrId)
                : elementOrId;
            
            return element ? this.instances.get(element.id) : null;
        },

        /**
         * Destroy component
         */
        destroyComponent: function(elementOrId) {
            const element = typeof elementOrId === 'string' 
                ? document.getElementById(elementOrId)
                : elementOrId;
            
            if (!element) return;
            
            const component = this.instances.get(element.id);
            if (component) {
                // Destroy instance
                if (component.instance && typeof component.instance.destroy === 'function') {
                    component.instance.destroy();
                }
                
                // Remove from observer
                if (this.observer) {
                    this.observer.unobserve(element);
                }
                
                // Clean up
                this.instances.delete(element.id);
                element.classList.remove('wink-component-loaded', 'wink-component-loading', 'wink-component-error');
                element.removeAttribute('data-wink-loaded');
                
                this.triggerComponentEvent(component, 'destroyed');
            }
        },

        /**
         * Get all components
         */
        getAllComponents: function() {
            return Array.from(this.instances.values());
        },

        /**
         * Get components by name
         */
        getComponentsByName: function(name) {
            return this.getAllComponents().filter(component => component.name === name);
        }
    };

    // Add to WinkViews namespace
    WinkViews.ComponentLoader = ComponentLoader;

    // Auto-initialize
    document.addEventListener('DOMContentLoaded', function() {
        ComponentLoader.init();
    });

    // Handle dynamic content
    const originalAppendChild = Element.prototype.appendChild;
    const originalInsertBefore = Element.prototype.insertBefore;
    
    Element.prototype.appendChild = function(child) {
        const result = originalAppendChild.call(this, child);
        if (child.nodeType === Node.ELEMENT_NODE) {
            ComponentLoader.scanForComponents(child);
        }
        return result;
    };
    
    Element.prototype.insertBefore = function(newNode, referenceNode) {
        const result = originalInsertBefore.call(this, newNode, referenceNode);
        if (newNode.nodeType === Node.ELEMENT_NODE) {
            ComponentLoader.scanForComponents(newNode);
        }
        return result;
    };

})(window, document);