/**
 * Wink View Generator - Advanced Search Manager
 * 
 * Features:
 * - Auto-complete with remote data source
 * - Saved searches and search history
 * - Real-time filtering and highlighting
 * - Advanced search operators
 * - Voice search support
 * - Mobile-optimized interface
 * 
 * @version 2.0.0
 */

(function(window, document) {
    'use strict';

    window.WinkViews = window.WinkViews || {};

    const SearchManager = {
        instances: new Map(),
        globalConfig: {
            minLength: 2,
            delay: 300,
            maxResults: 10,
            enableHistory: true,
            enableSaved: true,
            enableVoice: true,
            highlightMatches: true,
            caseSensitive: false,
            fuzzySearch: false,
            operators: ['AND', 'OR', 'NOT', '"exact"']
        },

        init: function(element, options = {}) {
            const config = Object.assign({}, this.globalConfig, options);
            const instance = new SearchInstance(element, config);
            this.instances.set(element.id || WinkViews.Utils.generateId(), instance);
            return instance;
        }
    };

    function SearchInstance(element, config) {
        this.element = element;
        this.config = config;
        this.results = [];
        this.selectedIndex = -1;
        this.history = this.loadHistory();
        this.savedSearches = this.loadSavedSearches();
        this.isVoiceSupported = 'webkitSpeechRecognition' in window || 'SpeechRecognition' in window;
        
        this.init();
    }

    SearchInstance.prototype = {
        init: function() {
            this.setupStructure();
            this.setupEventListeners();
            this.setupVoiceSearch();
        },

        setupStructure: function() {
            this.wrapper = document.createElement('div');
            this.wrapper.className = 'wink-search-wrapper';
            
            this.element.parentNode.insertBefore(this.wrapper, this.element);
            this.wrapper.appendChild(this.element);
            
            // Add search enhancements
            this.element.classList.add('wink-search-input');
            this.element.setAttribute('autocomplete', 'off');
            this.element.setAttribute('role', 'combobox');
            this.element.setAttribute('aria-expanded', 'false');
            
            // Create results container
            this.resultsContainer = document.createElement('div');
            this.resultsContainer.className = 'wink-search-results';
            this.resultsContainer.setAttribute('role', 'listbox');
            this.wrapper.appendChild(this.resultsContainer);
            
            // Create search controls
            this.createControls();
        },

        createControls: function() {
            const controls = document.createElement('div');
            controls.className = 'wink-search-controls';
            
            let controlsHTML = '';
            
            // Voice search button
            if (this.config.enableVoice && this.isVoiceSupported) {
                controlsHTML += `
                    <button type="button" class="wink-search-voice" aria-label="Voice search">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12,2A3,3 0 0,1 15,5V11A3,3 0 0,1 12,14A3,3 0 0,1 9,11V5A3,3 0 0,1 12,2M19,11C19,14.53 16.39,17.44 13,17.93V21H11V17.93C7.61,17.44 5,14.53 5,11H7A5,5 0 0,0 12,16A5,5 0 0,0 17,11H19Z" />
                        </svg>
                    </button>
                `;
            }
            
            // Saved searches button
            if (this.config.enableSaved) {
                controlsHTML += `
                    <button type="button" class="wink-search-saved" aria-label="Saved searches">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M17,3H7A2,2 0 0,0 5,5V21L12,18L19,21V5C19,3.89 18.1,3 17,3Z" />
                        </svg>
                    </button>
                `;
            }
            
            // Clear button
            controlsHTML += `
                <button type="button" class="wink-search-clear" aria-label="Clear search" style="display: none;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M19,6.41L17.59,5L12,10.59L6.41,5L5,6.41L10.59,12L5,17.59L6.41,19L12,13.41L17.59,19L19,17.59L13.41,12L19,6.41Z" />
                    </svg>
                </button>
            `;
            
            controls.innerHTML = controlsHTML;
            this.wrapper.appendChild(controls);
            
            this.controls = controls;
        },

        setupEventListeners: function() {
            // Input events
            const debouncedSearch = WinkViews.Utils.debounce((e) => {
                this.search(e.target.value);
            }, this.config.delay);
            
            this.element.addEventListener('input', debouncedSearch);
            this.element.addEventListener('focus', () => this.onFocus());
            this.element.addEventListener('blur', () => this.onBlur());
            this.element.addEventListener('keydown', (e) => this.onKeydown(e));
            
            // Control buttons
            const clearBtn = this.controls.querySelector('.wink-search-clear');
            if (clearBtn) {
                clearBtn.addEventListener('click', () => this.clear());
            }
            
            const voiceBtn = this.controls.querySelector('.wink-search-voice');
            if (voiceBtn) {
                voiceBtn.addEventListener('click', () => this.startVoiceSearch());
            }
            
            const savedBtn = this.controls.querySelector('.wink-search-saved');
            if (savedBtn) {
                savedBtn.addEventListener('click', () => this.showSavedSearches());
            }
            
            // Results container
            this.resultsContainer.addEventListener('click', (e) => {
                const item = e.target.closest('.wink-search-item');
                if (item) {
                    this.selectResult(item);
                }
            });
            
            // Document click to close
            document.addEventListener('click', (e) => {
                if (!this.wrapper.contains(e.target)) {
                    this.hideResults();
                }
            });
        },

        setupVoiceSearch: function() {
            if (!this.isVoiceSupported) return;
            
            const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
            this.recognition = new SpeechRecognition();
            this.recognition.continuous = false;
            this.recognition.interimResults = false;
            this.recognition.lang = 'en-US';
            
            this.recognition.onresult = (event) => {
                const transcript = event.results[0][0].transcript;
                this.element.value = transcript;
                this.search(transcript);
                this.stopVoiceSearch();
            };
            
            this.recognition.onerror = () => {
                this.stopVoiceSearch();
            };
            
            this.recognition.onend = () => {
                this.stopVoiceSearch();
            };
        },

        search: function(query) {
            if (query.length < this.config.minLength) {
                this.hideResults();
                this.toggleClearButton(false);
                return;
            }
            
            this.toggleClearButton(true);
            this.currentQuery = query;
            
            // Add to history
            if (this.config.enableHistory) {
                this.addToHistory(query);
            }
            
            // Trigger search
            if (this.config.dataSource) {
                this.searchRemote(query);
            } else if (this.config.data) {
                this.searchLocal(query, this.config.data);
            } else {
                // Emit search event for external handling
                this.triggerSearchEvent(query);
            }
        },

        searchLocal: function(query, data) {
            const normalizedQuery = this.config.caseSensitive ? query : query.toLowerCase();
            
            let results = data.filter(item => {
                const text = this.config.caseSensitive ? item.text : item.text.toLowerCase();
                
                if (this.config.fuzzySearch) {
                    return this.fuzzyMatch(text, normalizedQuery);
                } else {
                    return text.includes(normalizedQuery);
                }
            });
            
            // Limit results
            results = results.slice(0, this.config.maxResults);
            
            this.displayResults(results, query);
        },

        searchRemote: function(query) {
            const url = typeof this.config.dataSource === 'string' 
                ? `${this.config.dataSource}?q=${encodeURIComponent(query)}`
                : this.config.dataSource(query);
            
            this.showLoading();
            
            fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                this.hideLoading();
                this.displayResults(data.results || data, query);
            })
            .catch(error => {
                this.hideLoading();
                console.error('Search error:', error);
            });
        },

        displayResults: function(results, query) {
            this.results = results;
            this.selectedIndex = -1;
            
            if (!results.length) {
                this.showNoResults();
                return;
            }
            
            let html = '';
            results.forEach((item, index) => {
                const text = item.text || item.title || item.name || item;
                const highlightedText = this.config.highlightMatches 
                    ? this.highlightMatch(text, query)
                    : text;
                
                html += `
                    <div class="wink-search-item" role="option" data-index="${index}">
                        <div class="wink-search-item-text">${highlightedText}</div>
                        ${item.description ? `<div class="wink-search-item-desc">${item.description}</div>` : ''}
                    </div>
                `;
            });
            
            this.resultsContainer.innerHTML = html;
            this.showResults();
        },

        highlightMatch: function(text, query) {
            if (!this.config.highlightMatches) return text;
            
            const regex = new RegExp(`(${query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')})`, 'gi');
            return text.replace(regex, '<mark>$1</mark>');
        },

        fuzzyMatch: function(text, query) {
            let textIndex = 0;
            let queryIndex = 0;
            
            while (textIndex < text.length && queryIndex < query.length) {
                if (text[textIndex] === query[queryIndex]) {
                    queryIndex++;
                }
                textIndex++;
            }
            
            return queryIndex === query.length;
        },

        showResults: function() {
            this.resultsContainer.style.display = 'block';
            this.element.setAttribute('aria-expanded', 'true');
        },

        hideResults: function() {
            this.resultsContainer.style.display = 'none';
            this.element.setAttribute('aria-expanded', 'false');
            this.selectedIndex = -1;
        },

        showLoading: function() {
            this.resultsContainer.innerHTML = '<div class="wink-search-loading">Searching...</div>';
            this.showResults();
        },

        hideLoading: function() {
            // Loading state will be replaced by results
        },

        showNoResults: function() {
            this.resultsContainer.innerHTML = '<div class="wink-search-no-results">No results found</div>';
            this.showResults();
        },

        onFocus: function() {
            if (this.results.length > 0) {
                this.showResults();
            }
        },

        onBlur: function() {
            // Delay hiding to allow for result clicks
            setTimeout(() => {
                this.hideResults();
            }, 200);
        },

        onKeydown: function(e) {
            switch (e.key) {
                case 'ArrowDown':
                    e.preventDefault();
                    this.navigateResults(1);
                    break;
                case 'ArrowUp':
                    e.preventDefault();
                    this.navigateResults(-1);
                    break;
                case 'Enter':
                    e.preventDefault();
                    if (this.selectedIndex >= 0) {
                        this.selectResultByIndex(this.selectedIndex);
                    } else {
                        this.triggerSearchEvent(this.element.value);
                    }
                    break;
                case 'Escape':
                    this.hideResults();
                    break;
            }
        },

        navigateResults: function(direction) {
            if (!this.results.length) return;
            
            const newIndex = this.selectedIndex + direction;
            
            if (newIndex >= -1 && newIndex < this.results.length) {
                this.updateSelection(newIndex);
            }
        },

        updateSelection: function(index) {
            // Remove previous selection
            const prevSelected = this.resultsContainer.querySelector('.wink-search-item.selected');
            if (prevSelected) {
                prevSelected.classList.remove('selected');
            }
            
            this.selectedIndex = index;
            
            if (index >= 0) {
                const item = this.resultsContainer.querySelector(`[data-index="${index}"]`);
                if (item) {
                    item.classList.add('selected');
                    item.scrollIntoView({ block: 'nearest' });
                }
            }
        },

        selectResult: function(itemElement) {
            const index = parseInt(itemElement.getAttribute('data-index'));
            this.selectResultByIndex(index);
        },

        selectResultByIndex: function(index) {
            if (index < 0 || index >= this.results.length) return;
            
            const result = this.results[index];
            const value = result.value || result.text || result.title || result.name || result;
            
            this.element.value = value;
            this.hideResults();
            
            // Trigger selection event
            const event = new CustomEvent('wink:search:select', {
                detail: { result, value, instance: this }
            });
            this.element.dispatchEvent(event);
        },

        clear: function() {
            this.element.value = '';
            this.hideResults();
            this.toggleClearButton(false);
            this.element.focus();
            
            // Trigger clear event
            const event = new CustomEvent('wink:search:clear', {
                detail: { instance: this }
            });
            this.element.dispatchEvent(event);
        },

        toggleClearButton: function(show) {
            const clearBtn = this.controls.querySelector('.wink-search-clear');
            if (clearBtn) {
                clearBtn.style.display = show ? 'block' : 'none';
            }
        },

        startVoiceSearch: function() {
            if (!this.recognition) return;
            
            this.recognition.start();
            
            const voiceBtn = this.controls.querySelector('.wink-search-voice');
            if (voiceBtn) {
                voiceBtn.classList.add('wink-voice-active');
            }
            
            // Show voice indicator
            this.showVoiceIndicator();
        },

        stopVoiceSearch: function() {
            const voiceBtn = this.controls.querySelector('.wink-search-voice');
            if (voiceBtn) {
                voiceBtn.classList.remove('wink-voice-active');
            }
            
            this.hideVoiceIndicator();
        },

        showVoiceIndicator: function() {
            if (!this.voiceIndicator) {
                this.voiceIndicator = document.createElement('div');
                this.voiceIndicator.className = 'wink-voice-indicator';
                this.voiceIndicator.textContent = 'Listening...';
                this.wrapper.appendChild(this.voiceIndicator);
            }
            this.voiceIndicator.style.display = 'block';
        },

        hideVoiceIndicator: function() {
            if (this.voiceIndicator) {
                this.voiceIndicator.style.display = 'none';
            }
        },

        showSavedSearches: function() {
            const searches = this.savedSearches;
            if (!searches.length) {
                WinkViews.Utils.showNotification('No saved searches', 'info');
                return;
            }
            
            let html = '<div class="wink-search-saved-list">';
            searches.forEach((search, index) => {
                html += `
                    <div class="wink-search-saved-item" data-query="${search.query}">
                        <span class="wink-search-saved-text">${search.query}</span>
                        <button type="button" class="wink-search-saved-remove" data-index="${index}">×</button>
                    </div>
                `;
            });
            html += '</div>';
            
            this.resultsContainer.innerHTML = html;
            this.showResults();
            
            // Event listeners for saved searches
            this.resultsContainer.addEventListener('click', (e) => {
                if (e.target.classList.contains('wink-search-saved-remove')) {
                    const index = parseInt(e.target.getAttribute('data-index'));
                    this.removeSavedSearch(index);
                } else {
                    const item = e.target.closest('.wink-search-saved-item');
                    if (item) {
                        const query = item.getAttribute('data-query');
                        this.element.value = query;
                        this.search(query);
                    }
                }
            });
        },

        addToHistory: function(query) {
            if (!query.trim() || this.history.includes(query)) return;
            
            this.history.unshift(query);
            if (this.history.length > 20) {
                this.history = this.history.slice(0, 20);
            }
            
            this.saveHistory();
        },

        saveSearch: function(query, name) {
            const search = { query, name: name || query, date: new Date().toISOString() };
            this.savedSearches.push(search);
            this.saveSavedSearches();
        },

        removeSavedSearch: function(index) {
            this.savedSearches.splice(index, 1);
            this.saveSavedSearches();
            this.showSavedSearches();
        },

        loadHistory: function() {
            try {
                const stored = localStorage.getItem('wink-search-history');
                return stored ? JSON.parse(stored) : [];
            } catch (e) {
                return [];
            }
        },

        saveHistory: function() {
            try {
                localStorage.setItem('wink-search-history', JSON.stringify(this.history));
            } catch (e) {
                console.warn('Failed to save search history');
            }
        },

        loadSavedSearches: function() {
            try {
                const stored = localStorage.getItem('wink-saved-searches');
                return stored ? JSON.parse(stored) : [];
            } catch (e) {
                return [];
            }
        },

        saveSavedSearches: function() {
            try {
                localStorage.setItem('wink-saved-searches', JSON.stringify(this.savedSearches));
            } catch (e) {
                console.warn('Failed to save searches');
            }
        },

        triggerSearchEvent: function(query) {
            const event = new CustomEvent('wink:search:query', {
                detail: { query, instance: this }
            });
            this.element.dispatchEvent(event);
        },

        destroy: function() {
            if (this.recognition) {
                this.recognition.stop();
            }
            
            if (this.wrapper && this.wrapper.parentNode) {
                this.wrapper.parentNode.insertBefore(this.element, this.wrapper);
                this.wrapper.remove();
            }
        }
    };

    // Add to WinkViews namespace
    WinkViews.SearchManager = SearchManager;

    // Auto-initialize
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('[data-wink-search]').forEach(element => {
            const options = JSON.parse(element.getAttribute('data-wink-options') || '{}');
            SearchManager.init(element, options);
        });
    });

})(window, document);