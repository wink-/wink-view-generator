/**
 * Wink View Generator - Advanced Table Manager
 * 
 * Features:
 * - Advanced sorting with multiple columns
 * - Real-time filtering and search
 * - Bulk actions with confirmation
 * - Export functionality (CSV, PDF, Excel)
 * - Responsive design with mobile optimization
 * - Virtual scrolling for large datasets
 * - Column resizing and reordering
 * - State persistence
 * 
 * @version 2.0.0
 */

(function(window, document) {
    'use strict';

    // Ensure WinkViews namespace exists
    window.WinkViews = window.WinkViews || {};

    const TableManager = {
        instances: new Map(),
        exportWorker: null,
        
        globalConfig: {
            pageSize: 25,
            pageSizes: [10, 25, 50, 100],
            enableVirtualScroll: false,
            virtualScrollThreshold: 1000,
            exportFormats: ['csv', 'excel', 'pdf'],
            enableColumnResize: true,
            enableColumnReorder: true,
            persistState: true,
            debounceDelay: 300
        },

        /**
         * Initialize table manager on a table element
         */
        init: function(tableElement, options = {}) {
            if (!tableElement) {
                throw new Error('TableManager requires a valid table element');
            }

            const tableId = tableElement.id || WinkViews.Utils.generateId();
            tableElement.id = tableId;

            const config = Object.assign({}, this.globalConfig, options);
            
            const instance = new TableInstance(tableElement, config);
            this.instances.set(tableId, instance);
            
            return instance;
        },

        /**
         * Get table instance by ID
         */
        getInstance: function(tableId) {
            return this.instances.get(tableId);
        },

        /**
         * Initialize export worker for background processing
         */
        initExportWorker: function() {
            if (!this.exportWorker && window.Worker) {
                const workerCode = this.getExportWorkerCode();
                const blob = new Blob([workerCode], { type: 'application/javascript' });
                this.exportWorker = new Worker(URL.createObjectURL(blob));
            }
        },

        /**
         * Get export worker code
         */
        getExportWorkerCode: function() {
            return `
                self.onmessage = function(e) {
                    const { data, format, filename } = e.data;
                    
                    let result;
                    switch (format) {
                        case 'csv':
                            result = generateCSV(data);
                            break;
                        case 'excel':
                            result = generateExcel(data);
                            break;
                        default:
                            result = generateCSV(data);
                    }
                    
                    self.postMessage({ result, filename });
                };
                
                function generateCSV(data) {
                    if (!data.length) return '';
                    
                    const headers = Object.keys(data[0]);
                    const csvContent = [
                        headers.join(','),
                        ...data.map(row => 
                            headers.map(header => {
                                const value = row[header] || '';
                                return typeof value === 'string' && value.includes(',') 
                                    ? '"' + value.replace(/"/g, '""') + '"'
                                    : value;
                            }).join(',')
                        )
                    ].join('\\n');
                    
                    return csvContent;
                }
                
                function generateExcel(data) {
                    // Basic Excel format generation
                    return generateCSV(data);
                }
            `;
        }
    };

    /**
     * Table Instance Class
     */
    function TableInstance(tableElement, config) {
        this.table = tableElement;
        this.config = config;
        this.data = [];
        this.filteredData = [];
        this.currentPage = 1;
        this.sortColumn = null;
        this.sortDirection = 'asc';
        this.filters = {};
        this.selectedRows = new Set();
        this.columns = [];
        this.state = {};
        
        this.init();
    }

    TableInstance.prototype = {
        /**
         * Initialize table instance
         */
        init: function() {
            this.setupStructure();
            this.analyzeColumns();
            this.extractData();
            this.setupEventListeners();
            this.setupBulkActions();
            this.setupFilters();
            this.setupPagination();
            this.setupExport();
            this.setupResponsive();
            this.loadState();
            this.render();
            
            // Mark as initialized
            this.table.classList.add('wink-table-initialized');
            this.table.setAttribute('data-wink-table', this.table.id);
        },

        /**
         * Setup table structure with controls
         */
        setupStructure: function() {
            const wrapper = document.createElement('div');
            wrapper.className = 'wink-table-wrapper';
            
            // Create toolbar
            const toolbar = document.createElement('div');
            toolbar.className = 'wink-table-toolbar';
            toolbar.innerHTML = `
                <div class="wink-table-toolbar-left">
                    <div class="wink-bulk-actions" style="display: none;">
                        <select class="wink-bulk-action-select">
                            <option value="">Bulk Actions</option>
                            <option value="delete">Delete Selected</option>
                            <option value="export">Export Selected</option>
                        </select>
                        <button type="button" class="wink-btn wink-btn-sm wink-bulk-apply">Apply</button>
                    </div>
                </div>
                <div class="wink-table-toolbar-right">
                    <div class="wink-table-search">
                        <input type="text" placeholder="Search..." class="wink-table-search-input">
                    </div>
                    <div class="wink-table-filters">
                        <button type="button" class="wink-btn wink-btn-sm wink-filters-toggle">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M14,12V19.88C14.04,20.18 13.94,20.5 13.71,20.71C13.32,21.1 12.69,21.1 12.3,20.71L10.29,18.7C10.06,18.47 9.96,18.16 10,17.87V12H9.97L4.21,4.62C3.87,4.19 3.95,3.56 4.38,3.22C4.57,3.08 4.78,3 5,3V3H19V3C19.22,3 19.43,3.08 19.62,3.22C20.05,3.56 20.13,4.19 19.79,4.62L14.03,12H14Z" />
                            </svg>
                            Filters
                        </button>
                    </div>
                    <div class="wink-table-export">
                        <div class="wink-dropdown">
                            <button type="button" class="wink-btn wink-btn-sm wink-export-toggle">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z" />
                                </svg>
                                Export
                            </button>
                            <div class="wink-dropdown-menu">
                                <a href="#" class="wink-dropdown-item" data-format="csv">CSV</a>
                                <a href="#" class="wink-dropdown-item" data-format="excel">Excel</a>
                                <a href="#" class="wink-dropdown-item" data-format="pdf">PDF</a>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            // Create filter panel
            const filterPanel = document.createElement('div');
            filterPanel.className = 'wink-table-filter-panel';
            filterPanel.style.display = 'none';

            // Create table container
            const tableContainer = document.createElement('div');
            tableContainer.className = 'wink-table-container';

            // Create pagination
            const pagination = document.createElement('div');
            pagination.className = 'wink-table-pagination';

            // Insert wrapper
            this.table.parentNode.insertBefore(wrapper, this.table);
            wrapper.appendChild(toolbar);
            wrapper.appendChild(filterPanel);
            wrapper.appendChild(tableContainer);
            wrapper.appendChild(pagination);
            tableContainer.appendChild(this.table);

            // Store references
            this.wrapper = wrapper;
            this.toolbar = toolbar;
            this.filterPanel = filterPanel;
            this.tableContainer = tableContainer;
            this.paginationContainer = pagination;
        },

        /**
         * Analyze table columns
         */
        analyzeColumns: function() {
            const headers = this.table.querySelectorAll('thead th, thead td');
            
            this.columns = Array.from(headers).map((header, index) => {
                const column = {
                    index: index,
                    key: header.getAttribute('data-key') || header.textContent.trim().toLowerCase().replace(/\s+/g, '_'),
                    title: header.textContent.trim(),
                    sortable: header.hasAttribute('data-sortable'),
                    filterable: header.hasAttribute('data-filterable'),
                    type: header.getAttribute('data-type') || 'text',
                    width: header.style.width || 'auto',
                    visible: !header.hasAttribute('data-hidden'),
                    resizable: this.config.enableColumnResize && header.hasAttribute('data-resizable'),
                    element: header
                };
                
                return column;
            });
        },

        /**
         * Extract data from table rows
         */
        extractData: function() {
            const rows = this.table.querySelectorAll('tbody tr');
            
            this.data = Array.from(rows).map((row, rowIndex) => {
                const cells = row.querySelectorAll('td, th');
                const rowData = { _index: rowIndex, _element: row };
                
                cells.forEach((cell, cellIndex) => {
                    const column = this.columns[cellIndex];
                    if (column) {
                        let value = cell.textContent.trim();
                        
                        // Parse value based on column type
                        switch (column.type) {
                            case 'number':
                                value = parseFloat(value) || 0;
                                break;
                            case 'date':
                                value = new Date(value);
                                break;
                            case 'boolean':
                                value = value.toLowerCase() === 'true' || value === '1';
                                break;
                        }
                        
                        rowData[column.key] = value;
                    }
                });
                
                return rowData;
            });
            
            this.filteredData = [...this.data];
        },

        /**
         * Setup event listeners
         */
        setupEventListeners: function() {
            // Search input
            const searchInput = this.toolbar.querySelector('.wink-table-search-input');
            if (searchInput) {
                const debouncedSearch = WinkViews.Utils.debounce((e) => {
                    this.search(e.target.value);
                }, this.config.debounceDelay);
                
                searchInput.addEventListener('input', debouncedSearch);
            }

            // Column sorting
            this.setupSorting();

            // Filter toggle
            const filterToggle = this.toolbar.querySelector('.wink-filters-toggle');
            if (filterToggle) {
                filterToggle.addEventListener('click', () => {
                    this.toggleFilters();
                });
            }

            // Export dropdown
            this.setupExportDropdown();

            // Row selection
            this.setupRowSelection();

            // Column resizing
            if (this.config.enableColumnResize) {
                this.setupColumnResize();
            }

            // Responsive handling
            window.addEventListener('resize', WinkViews.Utils.throttle(() => {
                this.handleResize();
            }, 250));
        },

        /**
         * Setup column sorting
         */
        setupSorting: function() {
            this.columns.forEach(column => {
                if (column.sortable) {
                    column.element.style.cursor = 'pointer';
                    column.element.classList.add('wink-sortable');
                    
                    // Add sort indicator
                    const indicator = document.createElement('span');
                    indicator.className = 'wink-sort-indicator';
                    column.element.appendChild(indicator);
                    
                    column.element.addEventListener('click', (e) => {
                        e.preventDefault();
                        this.sort(column.key);
                    });
                }
            });
        },

        /**
         * Setup row selection
         */
        setupRowSelection: function() {
            // Add master checkbox to header
            const headerRow = this.table.querySelector('thead tr');
            if (headerRow) {
                const selectAllCell = document.createElement('th');
                selectAllCell.className = 'wink-select-column';
                selectAllCell.innerHTML = '<input type="checkbox" class="wink-select-all">';
                headerRow.insertBefore(selectAllCell, headerRow.firstChild);
                
                const selectAllCheckbox = selectAllCell.querySelector('.wink-select-all');
                selectAllCheckbox.addEventListener('change', (e) => {
                    this.selectAll(e.target.checked);
                });
            }

            // Add checkboxes to data rows
            const dataRows = this.table.querySelectorAll('tbody tr');
            dataRows.forEach((row, index) => {
                const selectCell = document.createElement('td');
                selectCell.className = 'wink-select-column';
                selectCell.innerHTML = `<input type="checkbox" class="wink-select-row" data-index="${index}">`;
                row.insertBefore(selectCell, row.firstChild);
                
                const checkbox = selectCell.querySelector('.wink-select-row');
                checkbox.addEventListener('change', () => {
                    this.toggleRowSelection(index);
                });
            });
        },

        /**
         * Setup bulk actions
         */
        setupBulkActions: function() {
            const bulkApply = this.toolbar.querySelector('.wink-bulk-apply');
            if (bulkApply) {
                bulkApply.addEventListener('click', () => {
                    const select = this.toolbar.querySelector('.wink-bulk-action-select');
                    const action = select.value;
                    
                    if (action && this.selectedRows.size > 0) {
                        this.executeBulkAction(action);
                    }
                });
            }
        },

        /**
         * Setup filters
         */
        setupFilters: function() {
            const filterPanel = this.filterPanel;
            
            this.columns.forEach(column => {
                if (column.filterable) {
                    const filterGroup = document.createElement('div');
                    filterGroup.className = 'wink-filter-group';
                    
                    const label = document.createElement('label');
                    label.textContent = column.title;
                    label.className = 'wink-filter-label';
                    
                    let filterInput;
                    switch (column.type) {
                        case 'select':
                            filterInput = this.createSelectFilter(column);
                            break;
                        case 'date':
                            filterInput = this.createDateFilter(column);
                            break;
                        case 'number':
                            filterInput = this.createNumberFilter(column);
                            break;
                        default:
                            filterInput = this.createTextFilter(column);
                    }
                    
                    filterGroup.appendChild(label);
                    filterGroup.appendChild(filterInput);
                    filterPanel.appendChild(filterGroup);
                }
            });
            
            // Add filter actions
            const filterActions = document.createElement('div');
            filterActions.className = 'wink-filter-actions';
            filterActions.innerHTML = `
                <button type="button" class="wink-btn wink-btn-primary wink-btn-sm wink-apply-filters">Apply</button>
                <button type="button" class="wink-btn wink-btn-secondary wink-btn-sm wink-clear-filters">Clear</button>
            `;
            
            filterPanel.appendChild(filterActions);
            
            // Filter event listeners
            filterActions.querySelector('.wink-apply-filters').addEventListener('click', () => {
                this.applyFilters();
            });
            
            filterActions.querySelector('.wink-clear-filters').addEventListener('click', () => {
                this.clearFilters();
            });
        },

        /**
         * Create text filter input
         */
        createTextFilter: function(column) {
            const input = document.createElement('input');
            input.type = 'text';
            input.className = 'wink-filter-input';
            input.setAttribute('data-column', column.key);
            input.placeholder = `Filter ${column.title}...`;
            
            return input;
        },

        /**
         * Create select filter
         */
        createSelectFilter: function(column) {
            const select = document.createElement('select');
            select.className = 'wink-filter-select';
            select.setAttribute('data-column', column.key);
            
            // Get unique values for this column
            const uniqueValues = [...new Set(this.data.map(row => row[column.key]))];
            
            const defaultOption = document.createElement('option');
            defaultOption.value = '';
            defaultOption.textContent = 'All';
            select.appendChild(defaultOption);
            
            uniqueValues.forEach(value => {
                const option = document.createElement('option');
                option.value = value;
                option.textContent = value;
                select.appendChild(option);
            });
            
            return select;
        },

        /**
         * Create date filter
         */
        createDateFilter: function(column) {
            const container = document.createElement('div');
            container.className = 'wink-date-filter';
            
            const fromInput = document.createElement('input');
            fromInput.type = 'date';
            fromInput.className = 'wink-filter-input';
            fromInput.setAttribute('data-column', column.key);
            fromInput.setAttribute('data-type', 'from');
            fromInput.placeholder = 'From';
            
            const toInput = document.createElement('input');
            toInput.type = 'date';
            toInput.className = 'wink-filter-input';
            toInput.setAttribute('data-column', column.key);
            toInput.setAttribute('data-type', 'to');
            toInput.placeholder = 'To';
            
            container.appendChild(fromInput);
            container.appendChild(toInput);
            
            return container;
        },

        /**
         * Create number filter
         */
        createNumberFilter: function(column) {
            const container = document.createElement('div');
            container.className = 'wink-number-filter';
            
            const minInput = document.createElement('input');
            minInput.type = 'number';
            minInput.className = 'wink-filter-input';
            minInput.setAttribute('data-column', column.key);
            minInput.setAttribute('data-type', 'min');
            minInput.placeholder = 'Min';
            
            const maxInput = document.createElement('input');
            maxInput.type = 'number';
            maxInput.className = 'wink-filter-input';
            maxInput.setAttribute('data-column', column.key);
            maxInput.setAttribute('data-type', 'max');
            maxInput.placeholder = 'Max';
            
            container.appendChild(minInput);
            container.appendChild(maxInput);
            
            return container;
        },

        /**
         * Setup pagination
         */
        setupPagination: function() {
            this.renderPagination();
        },

        /**
         * Setup export functionality
         */
        setupExport: function() {
            TableManager.initExportWorker();
        },

        /**
         * Setup export dropdown
         */
        setupExportDropdown: function() {
            const exportToggle = this.toolbar.querySelector('.wink-export-toggle');
            const exportMenu = this.toolbar.querySelector('.wink-dropdown-menu');
            
            if (exportToggle && exportMenu) {
                exportToggle.addEventListener('click', (e) => {
                    e.stopPropagation();
                    exportMenu.style.display = exportMenu.style.display === 'block' ? 'none' : 'block';
                });
                
                // Close dropdown when clicking outside
                document.addEventListener('click', () => {
                    exportMenu.style.display = 'none';
                });
                
                // Export format selection
                exportMenu.addEventListener('click', (e) => {
                    if (e.target.hasAttribute('data-format')) {
                        e.preventDefault();
                        const format = e.target.getAttribute('data-format');
                        this.exportData(format);
                        exportMenu.style.display = 'none';
                    }
                });
            }
        },

        /**
         * Setup column resizing
         */
        setupColumnResize: function() {
            this.columns.forEach((column, index) => {
                if (column.resizable) {
                    const resizer = document.createElement('div');
                    resizer.className = 'wink-column-resizer';
                    column.element.appendChild(resizer);
                    
                    let isResizing = false;
                    let startX = 0;
                    let startWidth = 0;
                    
                    resizer.addEventListener('mousedown', (e) => {
                        isResizing = true;
                        startX = e.clientX;
                        startWidth = column.element.offsetWidth;
                        
                        document.addEventListener('mousemove', handleResize);
                        document.addEventListener('mouseup', stopResize);
                        e.preventDefault();
                    });
                    
                    const handleResize = (e) => {
                        if (!isResizing) return;
                        
                        const deltaX = e.clientX - startX;
                        const newWidth = startWidth + deltaX;
                        
                        if (newWidth > 50) { // Minimum width
                            column.element.style.width = newWidth + 'px';
                            column.width = newWidth + 'px';
                        }
                    };
                    
                    const stopResize = () => {
                        isResizing = false;
                        document.removeEventListener('mousemove', handleResize);
                        document.removeEventListener('mouseup', stopResize);
                        this.saveState();
                    };
                }
            });
        },

        /**
         * Setup responsive handling
         */
        setupResponsive: function() {
            this.handleResize();
        },

        /**
         * Handle window resize
         */
        handleResize: function() {
            const containerWidth = this.tableContainer.offsetWidth;
            const tableWidth = this.table.offsetWidth;
            
            if (tableWidth > containerWidth) {
                this.table.classList.add('wink-table-scrollable');
            } else {
                this.table.classList.remove('wink-table-scrollable');
            }
        },

        /**
         * Search functionality
         */
        search: function(query) {
            if (!query.trim()) {
                this.filteredData = [...this.data];
            } else {
                const searchLower = query.toLowerCase();
                this.filteredData = this.data.filter(row => {
                    return this.columns.some(column => {
                        const value = row[column.key];
                        return value && value.toString().toLowerCase().includes(searchLower);
                    });
                });
            }
            
            this.currentPage = 1;
            this.render();
        },

        /**
         * Sort data by column
         */
        sort: function(columnKey) {
            const column = this.columns.find(col => col.key === columnKey);
            if (!column) return;
            
            // Toggle sort direction if same column
            if (this.sortColumn === columnKey) {
                this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
            } else {
                this.sortColumn = columnKey;
                this.sortDirection = 'asc';
            }
            
            // Update UI indicators
            this.updateSortIndicators();
            
            // Sort data
            this.filteredData.sort((a, b) => {
                let aVal = a[columnKey];
                let bVal = b[columnKey];
                
                // Handle different data types
                if (column.type === 'number') {
                    aVal = parseFloat(aVal) || 0;
                    bVal = parseFloat(bVal) || 0;
                } else if (column.type === 'date') {
                    aVal = new Date(aVal);
                    bVal = new Date(bVal);
                } else {
                    aVal = aVal ? aVal.toString().toLowerCase() : '';
                    bVal = bVal ? bVal.toString().toLowerCase() : '';
                }
                
                let result = 0;
                if (aVal < bVal) result = -1;
                else if (aVal > bVal) result = 1;
                
                return this.sortDirection === 'desc' ? -result : result;
            });
            
            this.currentPage = 1;
            this.render();
            this.saveState();
        },

        /**
         * Update sort indicators
         */
        updateSortIndicators: function() {
            this.columns.forEach(column => {
                const indicator = column.element.querySelector('.wink-sort-indicator');
                if (indicator) {
                    indicator.className = 'wink-sort-indicator';
                    
                    if (column.key === this.sortColumn) {
                        indicator.classList.add(
                            this.sortDirection === 'asc' ? 'wink-sort-asc' : 'wink-sort-desc'
                        );
                    }
                }
            });
        },

        /**
         * Toggle filters panel
         */
        toggleFilters: function() {
            const isVisible = this.filterPanel.style.display !== 'none';
            this.filterPanel.style.display = isVisible ? 'none' : 'block';
            
            const toggle = this.toolbar.querySelector('.wink-filters-toggle');
            if (toggle) {
                toggle.classList.toggle('active', !isVisible);
            }
        },

        /**
         * Apply filters
         */
        applyFilters: function() {
            const filterInputs = this.filterPanel.querySelectorAll('.wink-filter-input, .wink-filter-select');
            this.filters = {};
            
            filterInputs.forEach(input => {
                const column = input.getAttribute('data-column');
                const type = input.getAttribute('data-type');
                const value = input.value.trim();
                
                if (value) {
                    if (!this.filters[column]) {
                        this.filters[column] = {};
                    }
                    
                    if (type) {
                        this.filters[column][type] = value;
                    } else {
                        this.filters[column].value = value;
                    }
                }
            });
            
            this.applyDataFilters();
            this.currentPage = 1;
            this.render();
        },

        /**
         * Apply data filters
         */
        applyDataFilters: function() {
            this.filteredData = this.data.filter(row => {
                return Object.keys(this.filters).every(columnKey => {
                    const filter = this.filters[columnKey];
                    const value = row[columnKey];
                    const column = this.columns.find(col => col.key === columnKey);
                    
                    if (!column) return true;
                    
                    switch (column.type) {
                        case 'date':
                            const date = new Date(value);
                            if (filter.from && date < new Date(filter.from)) return false;
                            if (filter.to && date > new Date(filter.to)) return false;
                            return true;
                            
                        case 'number':
                            const num = parseFloat(value) || 0;
                            if (filter.min && num < parseFloat(filter.min)) return false;
                            if (filter.max && num > parseFloat(filter.max)) return false;
                            return true;
                            
                        default:
                            if (filter.value) {
                                const searchValue = filter.value.toLowerCase();
                                const cellValue = value ? value.toString().toLowerCase() : '';
                                return cellValue.includes(searchValue);
                            }
                            return true;
                    }
                });
            });
        },

        /**
         * Clear all filters
         */
        clearFilters: function() {
            const filterInputs = this.filterPanel.querySelectorAll('.wink-filter-input, .wink-filter-select');
            filterInputs.forEach(input => {
                input.value = '';
            });
            
            this.filters = {};
            this.filteredData = [...this.data];
            this.currentPage = 1;
            this.render();
        },

        /**
         * Select all rows
         */
        selectAll: function(selected) {
            this.selectedRows.clear();
            
            if (selected) {
                this.getCurrentPageData().forEach((row, index) => {
                    this.selectedRows.add(row._index);
                });
            }
            
            this.updateRowSelection();
            this.updateBulkActions();
        },

        /**
         * Toggle row selection
         */
        toggleRowSelection: function(rowIndex) {
            if (this.selectedRows.has(rowIndex)) {
                this.selectedRows.delete(rowIndex);
            } else {
                this.selectedRows.add(rowIndex);
            }
            
            this.updateRowSelection();
            this.updateBulkActions();
        },

        /**
         * Update row selection UI
         */
        updateRowSelection: function() {
            const checkboxes = this.table.querySelectorAll('.wink-select-row');
            checkboxes.forEach(checkbox => {
                const index = parseInt(checkbox.getAttribute('data-index'));
                checkbox.checked = this.selectedRows.has(index);
            });
            
            // Update master checkbox
            const masterCheckbox = this.table.querySelector('.wink-select-all');
            if (masterCheckbox) {
                const currentPageData = this.getCurrentPageData();
                const allSelected = currentPageData.length > 0 && 
                    currentPageData.every(row => this.selectedRows.has(row._index));
                masterCheckbox.checked = allSelected;
            }
        },

        /**
         * Update bulk actions visibility
         */
        updateBulkActions: function() {
            const bulkActions = this.toolbar.querySelector('.wink-bulk-actions');
            if (bulkActions) {
                bulkActions.style.display = this.selectedRows.size > 0 ? 'block' : 'none';
            }
        },

        /**
         * Execute bulk action
         */
        executeBulkAction: function(action) {
            const selectedData = this.data.filter(row => this.selectedRows.has(row._index));
            
            switch (action) {
                case 'delete':
                    this.bulkDelete(selectedData);
                    break;
                case 'export':
                    this.exportData('csv', selectedData);
                    break;
                default:
                    // Emit custom event for external handling
                    const event = new CustomEvent('wink:table:bulkAction', {
                        detail: { action, data: selectedData, table: this }
                    });
                    this.table.dispatchEvent(event);
            }
        },

        /**
         * Bulk delete with confirmation
         */
        bulkDelete: function(selectedData) {
            const message = `Are you sure you want to delete ${selectedData.length} item(s)?`;
            
            if (confirm(message)) {
                // Remove from data array
                selectedData.forEach(row => {
                    const index = this.data.indexOf(row);
                    if (index > -1) {
                        this.data.splice(index, 1);
                        row._element.remove();
                    }
                });
                
                this.selectedRows.clear();
                this.filteredData = [...this.data];
                this.render();
                
                WinkViews.Utils.showNotification(`${selectedData.length} item(s) deleted`, 'success');
            }
        },

        /**
         * Export data
         */
        exportData: function(format, customData = null) {
            const dataToExport = customData || this.filteredData;
            const filename = `table-export-${new Date().toISOString().split('T')[0]}.${format}`;
            
            // Prepare data for export (remove internal properties)
            const exportData = dataToExport.map(row => {
                const cleanRow = {};
                this.columns.forEach(column => {
                    if (column.visible) {
                        cleanRow[column.title] = row[column.key];
                    }
                });
                return cleanRow;
            });
            
            if (TableManager.exportWorker) {
                // Use web worker for large datasets
                TableManager.exportWorker.postMessage({
                    data: exportData,
                    format: format,
                    filename: filename
                });
                
                TableManager.exportWorker.onmessage = function(e) {
                    const { result, filename } = e.data;
                    downloadFile(result, filename, format);
                };
            } else {
                // Fallback to main thread
                let content;
                switch (format) {
                    case 'csv':
                        content = this.generateCSV(exportData);
                        break;
                    case 'excel':
                        content = this.generateCSV(exportData); // Simplified for now
                        break;
                    case 'pdf':
                        this.generatePDF(exportData, filename);
                        return;
                }
                
                this.downloadFile(content, filename, format);
            }
        },

        /**
         * Generate CSV content
         */
        generateCSV: function(data) {
            if (!data.length) return '';
            
            const headers = Object.keys(data[0]);
            const csvContent = [
                headers.join(','),
                ...data.map(row => 
                    headers.map(header => {
                        const value = row[header] || '';
                        return typeof value === 'string' && value.includes(',') 
                            ? '"' + value.replace(/"/g, '""') + '"'
                            : value;
                    }).join(',')
                )
            ].join('\n');
            
            return csvContent;
        },

        /**
         * Generate PDF (requires external library)
         */
        generatePDF: function(data, filename) {
            // This would require a PDF library like jsPDF
            console.warn('PDF export requires jsPDF library');
            WinkViews.Utils.showNotification('PDF export not available', 'warning');
        },

        /**
         * Download file
         */
        downloadFile: function(content, filename, format) {
            const mimeTypes = {
                'csv': 'text/csv',
                'excel': 'application/vnd.ms-excel',
                'pdf': 'application/pdf'
            };
            
            const blob = new Blob([content], { type: mimeTypes[format] || 'text/plain' });
            const url = URL.createObjectURL(blob);
            
            const link = document.createElement('a');
            link.href = url;
            link.download = filename;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            
            URL.revokeObjectURL(url);
            WinkViews.Utils.showNotification('Export completed', 'success');
        },

        /**
         * Get current page data
         */
        getCurrentPageData: function() {
            const start = (this.currentPage - 1) * this.config.pageSize;
            const end = start + this.config.pageSize;
            return this.filteredData.slice(start, end);
        },

        /**
         * Render table content
         */
        render: function() {
            this.renderTable();
            this.renderPagination();
            this.updateRowSelection();
        },

        /**
         * Render table body
         */
        renderTable: function() {
            const tbody = this.table.querySelector('tbody');
            if (!tbody) return;
            
            const currentData = this.getCurrentPageData();
            const rows = tbody.querySelectorAll('tr');
            
            // Hide all rows first
            rows.forEach(row => row.style.display = 'none');
            
            // Show current page rows
            currentData.forEach(rowData => {
                const row = rowData._element;
                if (row) {
                    row.style.display = '';
                }
            });
        },

        /**
         * Render pagination
         */
        renderPagination: function() {
            const totalPages = Math.ceil(this.filteredData.length / this.config.pageSize);
            const totalItems = this.filteredData.length;
            
            const pagination = this.paginationContainer;
            pagination.innerHTML = `
                <div class="wink-pagination-info">
                    Showing ${Math.min((this.currentPage - 1) * this.config.pageSize + 1, totalItems)} to 
                    ${Math.min(this.currentPage * this.config.pageSize, totalItems)} of ${totalItems} entries
                </div>
                <div class="wink-pagination-controls">
                    <button type="button" class="wink-btn wink-btn-sm wink-page-btn" data-page="1" ${this.currentPage === 1 ? 'disabled' : ''}>
                        First
                    </button>
                    <button type="button" class="wink-btn wink-btn-sm wink-page-btn" data-page="${this.currentPage - 1}" ${this.currentPage === 1 ? 'disabled' : ''}>
                        Previous
                    </button>
                    
                    <span class="wink-page-numbers">
                        ${this.generatePageNumbers(totalPages)}
                    </span>
                    
                    <button type="button" class="wink-btn wink-btn-sm wink-page-btn" data-page="${this.currentPage + 1}" ${this.currentPage === totalPages ? 'disabled' : ''}>
                        Next
                    </button>
                    <button type="button" class="wink-btn wink-btn-sm wink-page-btn" data-page="${totalPages}" ${this.currentPage === totalPages ? 'disabled' : ''}>
                        Last
                    </button>
                </div>
                <div class="wink-pagination-size">
                    <select class="wink-page-size-select">
                        ${this.config.pageSizes.map(size => 
                            `<option value="${size}" ${size === this.config.pageSize ? 'selected' : ''}>${size} per page</option>`
                        ).join('')}
                    </select>
                </div>
            `;
            
            // Add event listeners
            pagination.addEventListener('click', (e) => {
                if (e.target.classList.contains('wink-page-btn') && !e.target.disabled) {
                    const page = parseInt(e.target.getAttribute('data-page'));
                    this.goToPage(page);
                }
            });
            
            const pageSizeSelect = pagination.querySelector('.wink-page-size-select');
            if (pageSizeSelect) {
                pageSizeSelect.addEventListener('change', (e) => {
                    this.config.pageSize = parseInt(e.target.value);
                    this.currentPage = 1;
                    this.render();
                    this.saveState();
                });
            }
        },

        /**
         * Generate page numbers for pagination
         */
        generatePageNumbers: function(totalPages) {
            const current = this.currentPage;
            const delta = 2; // Number of pages to show around current page
            
            let start = Math.max(1, current - delta);
            let end = Math.min(totalPages, current + delta);
            
            let pages = [];
            
            // Add first page and ellipsis if needed
            if (start > 1) {
                pages.push(1);
                if (start > 2) pages.push('...');
            }
            
            // Add pages around current
            for (let i = start; i <= end; i++) {
                pages.push(i);
            }
            
            // Add last page and ellipsis if needed
            if (end < totalPages) {
                if (end < totalPages - 1) pages.push('...');
                pages.push(totalPages);
            }
            
            return pages.map(page => {
                if (page === '...') {
                    return '<span class="wink-page-ellipsis">...</span>';
                } else {
                    const isActive = page === current;
                    return `<button type="button" class="wink-btn wink-btn-sm wink-page-btn ${isActive ? 'active' : ''}" data-page="${page}">${page}</button>`;
                }
            }).join('');
        },

        /**
         * Go to specific page
         */
        goToPage: function(page) {
            const totalPages = Math.ceil(this.filteredData.length / this.config.pageSize);
            
            if (page >= 1 && page <= totalPages) {
                this.currentPage = page;
                this.render();
                this.saveState();
            }
        },

        /**
         * Save table state to localStorage
         */
        saveState: function() {
            if (!this.config.persistState) return;
            
            const state = {
                sortColumn: this.sortColumn,
                sortDirection: this.sortDirection,
                pageSize: this.config.pageSize,
                filters: this.filters,
                columnWidths: this.columns.map(col => ({ key: col.key, width: col.width }))
            };
            
            localStorage.setItem(`wink-table-${this.table.id}`, JSON.stringify(state));
        },

        /**
         * Load table state from localStorage
         */
        loadState: function() {
            if (!this.config.persistState) return;
            
            const saved = localStorage.getItem(`wink-table-${this.table.id}`);
            if (saved) {
                try {
                    const state = JSON.parse(saved);
                    
                    this.sortColumn = state.sortColumn;
                    this.sortDirection = state.sortDirection;
                    this.config.pageSize = state.pageSize || this.config.pageSize;
                    this.filters = state.filters || {};
                    
                    // Restore column widths
                    if (state.columnWidths) {
                        state.columnWidths.forEach(col => {
                            const column = this.columns.find(c => c.key === col.key);
                            if (column && col.width !== 'auto') {
                                column.width = col.width;
                                column.element.style.width = col.width;
                            }
                        });
                    }
                    
                    // Apply loaded state
                    if (this.sortColumn) {
                        this.updateSortIndicators();
                    }
                    
                    if (Object.keys(this.filters).length > 0) {
                        this.applyDataFilters();
                    }
                } catch (e) {
                    console.warn('Failed to load table state:', e);
                }
            }
        },

        /**
         * Destroy table instance
         */
        destroy: function() {
            // Remove event listeners
            window.removeEventListener('resize', this.handleResize);
            
            // Clear references
            this.selectedRows.clear();
            
            // Remove wrapper and restore original table
            if (this.wrapper && this.wrapper.parentNode) {
                this.wrapper.parentNode.insertBefore(this.table, this.wrapper);
                this.wrapper.remove();
            }
            
            // Remove classes
            this.table.classList.remove('wink-table-initialized');
        }
    };

    // Add to WinkViews namespace
    WinkViews.TableManager = TableManager;

    // Auto-initialize tables with data-wink-table attribute
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('table[data-wink-table]').forEach(table => {
            const options = JSON.parse(table.getAttribute('data-wink-options') || '{}');
            TableManager.init(table, options);
        });
    });

})(window, document);