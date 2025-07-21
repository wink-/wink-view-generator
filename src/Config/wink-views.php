<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default UI Framework
    |--------------------------------------------------------------------------
    |
    | This value determines the default UI framework used when generating
    | views. Supported frameworks: "bootstrap", "tailwind", "custom"
    |
    */
    'framework' => env('WINK_VIEWS_FRAMEWORK', 'bootstrap'),

    /*
    |--------------------------------------------------------------------------
    | Layout Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the default layout templates to extend when generating views.
    | These can be overridden per command using the --layout option.
    |
    */
    'layout' => [
        'master' => 'layouts.app',
        'admin' => 'layouts.admin',
        'auth' => 'layouts.auth',
        'guest' => 'layouts.guest',
        'email' => 'layouts.email',
    ],

    /*
    |--------------------------------------------------------------------------
    | Component Configuration
    |--------------------------------------------------------------------------
    |
    | Configure how Blade components are generated and organized.
    |
    */
    'components' => [
        'use_components' => true,
        'component_namespace' => 'components',
        'livewire_integration' => false,
        'alpine_integration' => false,
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
        'pagination' => true,
        'search' => true,
        'filtering' => true,
        'sorting' => true,
        'bulk_actions' => false,
        'export' => false,
        'ajax_forms' => true,
        'responsive_tables' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Styling Configuration
    |--------------------------------------------------------------------------
    |
    | Configure visual aspects and theming options.
    |
    */
    'styling' => [
        'dark_mode' => true,
        'animations' => true,
        'icons' => 'bootstrap-icons', // bootstrap-icons|heroicons|feather|custom
        'color_scheme' => 'blue', // primary color theme
    ],

    /*
    |--------------------------------------------------------------------------
    | Form Configuration
    |--------------------------------------------------------------------------
    |
    | Configure form generation settings and validation display.
    |
    */
    'forms' => [
        'validation_style' => 'inline', // inline|summary|both
        'rich_text_editor' => 'tinymce', // tinymce|ckeditor|none
        'date_picker' => 'flatpickr', // flatpickr|native|custom
        'file_upload' => 'dropzone', // dropzone|native|custom
        'auto_focus' => true,
        'remember_form_data' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Table Configuration
    |--------------------------------------------------------------------------
    |
    | Configure data table generation settings.
    |
    */
    'tables' => [
        'default_pagination' => 15,
        'show_pagination_info' => true,
        'sticky_header' => false,
        'row_hover' => true,
        'striped_rows' => false,
        'compact_mode' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Asset Configuration
    |--------------------------------------------------------------------------
    |
    | Configure how assets (CSS/JS) are handled and included.
    |
    */
    'assets' => [
        'generate_css' => true,
        'generate_js' => true,
        'minify_assets' => false,
        'cdn_fallback' => true,
        'version_assets' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Template Paths
    |--------------------------------------------------------------------------
    |
    | Configure custom template paths for different frameworks.
    |
    */
    'template_paths' => [
        'bootstrap' => resource_path('stubs/wink-views/bootstrap'),
        'tailwind' => resource_path('stubs/wink-views/tailwind'),
        'custom' => resource_path('stubs/wink-views/custom'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Code Generation
    |--------------------------------------------------------------------------
    |
    | Configure code generation behavior and formatting.
    |
    */
    'generation' => [
        'add_comments' => true,
        'add_timestamps' => true,
        'format_code' => true,
        'backup_existing' => true,
        'use_short_array_syntax' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Configuration
    |--------------------------------------------------------------------------
    |
    | Configure security-related features in generated views.
    |
    */
    'security' => [
        'csrf_protection' => true,
        'xss_protection' => true,
        'sanitize_output' => true,
        'validate_permissions' => true,
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
        'aria_labels' => true,
        'focus_management' => true,
        'screen_reader_support' => true,
        'keyboard_navigation' => true,
        'color_contrast' => 'AA', // AA|AAA
    ],

    /*
    |--------------------------------------------------------------------------
    | SEO Configuration
    |--------------------------------------------------------------------------
    |
    | Configure SEO-related features in generated views.
    |
    */
    'seo' => [
        'meta_tags' => true,
        'structured_data' => false,
        'open_graph' => false,
        'twitter_cards' => false,
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
        'lazy_loading' => true,
        'image_optimization' => false,
        'cache_templates' => true,
        'preload_assets' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Development Configuration
    |--------------------------------------------------------------------------
    |
    | Configure development and debugging features.
    |
    */
    'development' => [
        'debug_comments' => env('APP_DEBUG', false),
        'generate_tests' => false,
        'generate_documentation' => false,
        'validate_templates' => true,
    ],
];