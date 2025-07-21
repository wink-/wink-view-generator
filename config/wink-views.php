<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | UI Framework
    |--------------------------------------------------------------------------
    |
    | The default UI framework to use when generating views. Available options:
    | - bootstrap: Bootstrap 5 with Bootstrap Icons
    | - tailwind: Tailwind CSS with Heroicons
    | - custom: Framework-agnostic HTML with semantic classes
    |
    */
    'framework' => env('WINK_VIEWS_FRAMEWORK', 'bootstrap'),

    /*
    |--------------------------------------------------------------------------
    | Layout Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the default layout templates that generated views will extend.
    | These can be overridden per command execution.
    |
    */
    'layout' => [
        'master' => env('WINK_VIEWS_MASTER_LAYOUT', 'layouts.app'),
        'admin' => env('WINK_VIEWS_ADMIN_LAYOUT', 'layouts.admin'),
        'auth' => env('WINK_VIEWS_AUTH_LAYOUT', 'layouts.auth'),
        'dashboard' => env('WINK_VIEWS_DASHBOARD_LAYOUT', 'layouts.dashboard'),
        'error' => env('WINK_VIEWS_ERROR_LAYOUT', 'layouts.error'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Component Configuration
    |--------------------------------------------------------------------------
    |
    | Configure how components are generated and organized.
    |
    */
    'components' => [
        'use_components' => env('WINK_VIEWS_USE_COMPONENTS', true),
        'component_namespace' => env('WINK_VIEWS_COMPONENT_NAMESPACE', 'components'),
        'livewire_integration' => env('WINK_VIEWS_LIVEWIRE', false),
        'alpine_integration' => env('WINK_VIEWS_ALPINE', false),
        'blade_components' => env('WINK_VIEWS_BLADE_COMPONENTS', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Feature Configuration
    |--------------------------------------------------------------------------
    |
    | Enable or disable specific features in generated views.
    |
    */
    'features' => [
        'pagination' => env('WINK_VIEWS_PAGINATION', true),
        'search' => env('WINK_VIEWS_SEARCH', true),
        'filtering' => env('WINK_VIEWS_FILTERING', true),
        'sorting' => env('WINK_VIEWS_SORTING', true),
        'bulk_actions' => env('WINK_VIEWS_BULK_ACTIONS', true),
        'export' => env('WINK_VIEWS_EXPORT', true),
        'ajax_forms' => env('WINK_VIEWS_AJAX_FORMS', true),
        'modal_dialogs' => env('WINK_VIEWS_MODALS', true),
        'breadcrumbs' => env('WINK_VIEWS_BREADCRUMBS', true),
        'flash_messages' => env('WINK_VIEWS_FLASH_MESSAGES', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Styling Configuration
    |--------------------------------------------------------------------------
    |
    | Configure visual aspects and styling options.
    |
    */
    'styling' => [
        'dark_mode' => env('WINK_VIEWS_DARK_MODE', true),
        'animations' => env('WINK_VIEWS_ANIMATIONS', true),
        'icons' => env('WINK_VIEWS_ICONS', 'bootstrap-icons'), // bootstrap-icons|heroicons|feather|lucide
        'color_scheme' => env('WINK_VIEWS_COLOR_SCHEME', 'blue'), // blue|green|purple|red|gray
        'border_radius' => env('WINK_VIEWS_BORDER_RADIUS', 'md'), // none|sm|md|lg|xl
        'shadows' => env('WINK_VIEWS_SHADOWS', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Form Configuration
    |--------------------------------------------------------------------------
    |
    | Configure form generation and validation display options.
    |
    */
    'forms' => [
        'validation_style' => env('WINK_VIEWS_VALIDATION_STYLE', 'inline'), // inline|summary|both
        'rich_text_editor' => env('WINK_VIEWS_RICH_TEXT_EDITOR', 'tinymce'), // tinymce|ckeditor|quill|none
        'date_picker' => env('WINK_VIEWS_DATE_PICKER', 'flatpickr'), // flatpickr|pikaday|none
        'file_upload' => env('WINK_VIEWS_FILE_UPLOAD', 'dropzone'), // dropzone|filepond|none
        'auto_save' => env('WINK_VIEWS_AUTO_SAVE', false),
        'client_validation' => env('WINK_VIEWS_CLIENT_VALIDATION', true),
        'progressive_enhancement' => env('WINK_VIEWS_PROGRESSIVE_ENHANCEMENT', true),
        'csrf_protection' => env('WINK_VIEWS_CSRF_PROTECTION', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Table Configuration
    |--------------------------------------------------------------------------
    |
    | Configure data table generation and display options.
    |
    */
    'tables' => [
        'pagination_size' => env('WINK_VIEWS_PAGINATION_SIZE', 15),
        'responsive_breakpoint' => env('WINK_VIEWS_RESPONSIVE_BREAKPOINT', 'md'),
        'sticky_headers' => env('WINK_VIEWS_STICKY_HEADERS', false),
        'row_actions' => env('WINK_VIEWS_ROW_ACTIONS', 'dropdown'), // buttons|dropdown|both
        'empty_state' => env('WINK_VIEWS_EMPTY_STATE', true),
        'loading_states' => env('WINK_VIEWS_LOADING_STATES', true),
        'column_visibility' => env('WINK_VIEWS_COLUMN_VISIBILITY', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Asset Configuration
    |--------------------------------------------------------------------------
    |
    | Configure how assets are handled and included.
    |
    */
    'assets' => [
        'css_framework' => env('WINK_VIEWS_CSS_FRAMEWORK', 'cdn'), // cdn|local|npm
        'js_framework' => env('WINK_VIEWS_JS_FRAMEWORK', 'cdn'), // cdn|local|npm
        'generate_assets' => env('WINK_VIEWS_GENERATE_ASSETS', true),
        'minify_assets' => env('WINK_VIEWS_MINIFY_ASSETS', false),
        'version_assets' => env('WINK_VIEWS_VERSION_ASSETS', false),
        'combine_assets' => env('WINK_VIEWS_COMBINE_ASSETS', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Accessibility Configuration
    |--------------------------------------------------------------------------
    |
    | Configure accessibility features for generated views.
    |
    */
    'accessibility' => [
        'wcag_compliance' => env('WINK_VIEWS_WCAG_COMPLIANCE', 'AA'), // A|AA|AAA
        'aria_labels' => env('WINK_VIEWS_ARIA_LABELS', true),
        'keyboard_navigation' => env('WINK_VIEWS_KEYBOARD_NAV', true),
        'screen_reader_support' => env('WINK_VIEWS_SCREEN_READER', true),
        'high_contrast_mode' => env('WINK_VIEWS_HIGH_CONTRAST', false),
        'focus_indicators' => env('WINK_VIEWS_FOCUS_INDICATORS', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Performance Configuration
    |--------------------------------------------------------------------------
    |
    | Configure performance optimization features.
    |
    */
    'performance' => [
        'lazy_loading' => env('WINK_VIEWS_LAZY_LOADING', true),
        'image_optimization' => env('WINK_VIEWS_IMAGE_OPTIMIZATION', true),
        'critical_css' => env('WINK_VIEWS_CRITICAL_CSS', false),
        'preload_assets' => env('WINK_VIEWS_PRELOAD_ASSETS', false),
        'progressive_enhancement' => env('WINK_VIEWS_PROGRESSIVE_ENHANCEMENT', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Template Paths
    |--------------------------------------------------------------------------
    |
    | Configure where to find and store template stubs.
    |
    */
    'paths' => [
        'stubs' => env('WINK_VIEWS_STUBS_PATH', resource_path('stubs/wink-views')),
        'views' => env('WINK_VIEWS_OUTPUT_PATH', resource_path('views')),
        'components' => env('WINK_VIEWS_COMPONENTS_PATH', resource_path('views/components')),
        'layouts' => env('WINK_VIEWS_LAYOUTS_PATH', resource_path('views/layouts')),
        'assets' => env('WINK_VIEWS_ASSETS_PATH', public_path('assets')),
    ],

    /*
    |--------------------------------------------------------------------------
    | Field Type Mappings
    |--------------------------------------------------------------------------
    |
    | Configure how database field types map to form input types.
    |
    */
    'field_mappings' => [
        'string' => 'text',
        'text' => 'textarea',
        'integer' => 'number',
        'bigInteger' => 'number',
        'decimal' => 'number',
        'float' => 'number',
        'double' => 'number',
        'boolean' => 'checkbox',
        'date' => 'date',
        'datetime' => 'datetime-local',
        'timestamp' => 'datetime-local',
        'time' => 'time',
        'json' => 'textarea',
        'email' => 'email',
        'password' => 'password',
        'url' => 'url',
        'uuid' => 'text',
        'enum' => 'select',
    ],

    /*
    |--------------------------------------------------------------------------
    | Relationship Mappings
    |--------------------------------------------------------------------------
    |
    | Configure how Eloquent relationships are displayed in forms.
    |
    */
    'relationship_mappings' => [
        'belongsTo' => 'select',
        'hasOne' => 'select',
        'hasMany' => 'multiselect',
        'belongsToMany' => 'multiselect',
        'morphTo' => 'select',
        'morphOne' => 'select',
        'morphMany' => 'multiselect',
    ],

    /*
    |--------------------------------------------------------------------------
    | Validation Rules
    |--------------------------------------------------------------------------
    |
    | Configure how validation rules are displayed in forms.
    |
    */
    'validation' => [
        'show_required_indicators' => env('WINK_VIEWS_SHOW_REQUIRED', true),
        'show_field_hints' => env('WINK_VIEWS_SHOW_HINTS', true),
        'show_character_count' => env('WINK_VIEWS_SHOW_CHAR_COUNT', false),
        'real_time_validation' => env('WINK_VIEWS_REAL_TIME_VALIDATION', false),
        'validation_delay' => env('WINK_VIEWS_VALIDATION_DELAY', 500), // milliseconds
    ],

    /*
    |--------------------------------------------------------------------------
    | SEO Configuration
    |--------------------------------------------------------------------------
    |
    | Configure SEO features for generated views.
    |
    */
    'seo' => [
        'generate_meta_tags' => env('WINK_VIEWS_META_TAGS', true),
        'generate_structured_data' => env('WINK_VIEWS_STRUCTURED_DATA', false),
        'generate_open_graph' => env('WINK_VIEWS_OPEN_GRAPH', false),
        'generate_twitter_cards' => env('WINK_VIEWS_TWITTER_CARDS', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Customization Options
    |--------------------------------------------------------------------------
    |
    | Configure customization and extension options.
    |
    */
    'customization' => [
        'allow_stub_override' => env('WINK_VIEWS_ALLOW_STUB_OVERRIDE', true),
        'custom_helpers' => env('WINK_VIEWS_CUSTOM_HELPERS', true),
        'generate_comments' => env('WINK_VIEWS_GENERATE_COMMENTS', true),
        'preserve_custom_code' => env('WINK_VIEWS_PRESERVE_CUSTOM_CODE', false),
        'backup_existing_files' => env('WINK_VIEWS_BACKUP_FILES', true),
    ],
];