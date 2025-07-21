<?php

namespace Wink\ViewGenerator\Config;

class ViewConfig
{
    /**
     * Default configuration values
     */
    public const DEFAULT_CONFIG = [
        'framework' => 'bootstrap',
        'layout' => [
            'master' => 'layouts.app',
            'admin' => 'layouts.admin',
            'auth' => 'layouts.auth',
        ],
        'components' => [
            'use_components' => true,
            'component_namespace' => 'components',
            'livewire_integration' => false,
        ],
        'features' => [
            'pagination' => true,
            'search' => true,
            'filtering' => true,
            'sorting' => true,
            'bulk_actions' => true,
            'export' => true,
            'ajax_forms' => true,
        ],
        'styling' => [
            'dark_mode' => true,
            'animations' => true,
            'icons' => 'bootstrap-icons',
        ],
        'forms' => [
            'validation_style' => 'inline',
            'rich_text_editor' => 'tinymce',
            'date_picker' => 'flatpickr',
            'file_upload' => 'dropzone',
        ],
        'accessibility' => [
            'enabled' => true,
            'wcag_level' => 'AA',
            'aria_labels' => true,
            'keyboard_navigation' => true,
            'screen_reader_support' => true,
        ],
        'performance' => [
            'lazy_loading' => true,
            'asset_minification' => true,
            'cdn_assets' => false,
            'cache_templates' => true,
        ],
        'seo' => [
            'meta_tags' => true,
            'structured_data' => true,
            'open_graph' => true,
            'twitter_cards' => true,
        ],
    ];

    /**
     * Supported UI frameworks
     */
    public const SUPPORTED_FRAMEWORKS = [
        'bootstrap' => [
            'name' => 'Bootstrap 5',
            'version' => '5.3.0',
            'css_cdn' => 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css',
            'js_cdn' => 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js',
            'icons' => 'bootstrap-icons',
            'components' => ['accordion', 'alert', 'badge', 'breadcrumb', 'button', 'card', 'carousel', 'dropdown', 'modal', 'navbar', 'pagination', 'progress', 'spinner', 'tab', 'toast', 'tooltip'],
        ],
        'tailwind' => [
            'name' => 'Tailwind CSS',
            'version' => '3.3.0',
            'css_cdn' => 'https://cdn.jsdelivr.net/npm/tailwindcss@3.3.0/dist/tailwind.min.css',
            'js_cdn' => null,
            'icons' => 'heroicons',
            'components' => ['alert', 'badge', 'breadcrumb', 'button', 'card', 'dropdown', 'modal', 'navbar', 'pagination', 'tab'],
        ],
        'custom' => [
            'name' => 'Custom Framework',
            'version' => '1.0.0',
            'css_cdn' => null,
            'js_cdn' => null,
            'icons' => 'feather',
            'components' => ['alert', 'badge', 'breadcrumb', 'button', 'card', 'modal', 'navbar', 'pagination'],
        ],
    ];

    /**
     * Icon sets configuration
     */
    public const ICON_SETS = [
        'bootstrap-icons' => [
            'name' => 'Bootstrap Icons',
            'version' => '1.10.0',
            'cdn' => 'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css',
            'prefix' => 'bi bi-',
            'common_icons' => [
                'home' => 'house',
                'user' => 'person',
                'edit' => 'pencil',
                'delete' => 'trash',
                'view' => 'eye',
                'add' => 'plus',
                'search' => 'search',
                'filter' => 'funnel',
                'sort' => 'arrow-up-down',
                'download' => 'download',
                'upload' => 'upload',
                'save' => 'check',
                'cancel' => 'x',
                'menu' => 'list',
                'close' => 'x-lg',
            ],
        ],
        'heroicons' => [
            'name' => 'Heroicons',
            'version' => '2.0.0',
            'cdn' => null,
            'prefix' => '',
            'common_icons' => [
                'home' => 'home',
                'user' => 'user',
                'edit' => 'pencil-square',
                'delete' => 'trash',
                'view' => 'eye',
                'add' => 'plus',
                'search' => 'magnifying-glass',
                'filter' => 'funnel',
                'sort' => 'arrows-up-down',
                'download' => 'arrow-down-tray',
                'upload' => 'arrow-up-tray',
                'save' => 'check',
                'cancel' => 'x-mark',
                'menu' => 'bars-3',
                'close' => 'x-mark',
            ],
        ],
        'feather' => [
            'name' => 'Feather Icons',
            'version' => '4.29.0',
            'cdn' => 'https://cdn.jsdelivr.net/npm/feather-icons@4.29.0/dist/feather.min.css',
            'prefix' => 'feather feather-',
            'common_icons' => [
                'home' => 'home',
                'user' => 'user',
                'edit' => 'edit',
                'delete' => 'trash-2',
                'view' => 'eye',
                'add' => 'plus',
                'search' => 'search',
                'filter' => 'filter',
                'sort' => 'arrow-up-down',
                'download' => 'download',
                'upload' => 'upload',
                'save' => 'check',
                'cancel' => 'x',
                'menu' => 'menu',
                'close' => 'x',
            ],
        ],
    ];

    /**
     * Rich text editor configurations
     */
    public const RICH_TEXT_EDITORS = [
        'tinymce' => [
            'name' => 'TinyMCE',
            'version' => '6.0.0',
            'cdn' => 'https://cdn.jsdelivr.net/npm/tinymce@6.0.0/tinymce.min.js',
            'config' => [
                'height' => 300,
                'menubar' => false,
                'plugins' => ['advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview', 'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen', 'insertdatetime', 'media', 'table', 'help', 'wordcount'],
                'toolbar' => 'undo redo | blocks | bold italic backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | help',
            ],
        ],
        'ckeditor' => [
            'name' => 'CKEditor 5',
            'version' => '5.0.0',
            'cdn' => 'https://cdn.ckeditor.com/ckeditor5/5.0.0/classic/ckeditor.js',
            'config' => [
                'height' => 300,
                'toolbar' => ['heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', '|', 'outdent', 'indent', '|', 'blockQuote', 'insertTable', 'undo', 'redo'],
            ],
        ],
        'none' => [
            'name' => 'None',
            'version' => null,
            'cdn' => null,
            'config' => [],
        ],
    ];

    /**
     * Date picker configurations
     */
    public const DATE_PICKERS = [
        'flatpickr' => [
            'name' => 'Flatpickr',
            'version' => '4.6.0',
            'css_cdn' => 'https://cdn.jsdelivr.net/npm/flatpickr@4.6.0/dist/flatpickr.min.css',
            'js_cdn' => 'https://cdn.jsdelivr.net/npm/flatpickr@4.6.0/dist/flatpickr.min.js',
            'config' => [
                'dateFormat' => 'Y-m-d',
                'enableTime' => false,
                'time_24hr' => true,
            ],
        ],
        'pikaday' => [
            'name' => 'Pikaday',
            'version' => '1.8.0',
            'css_cdn' => 'https://cdn.jsdelivr.net/npm/pikaday@1.8.0/css/pikaday.css',
            'js_cdn' => 'https://cdn.jsdelivr.net/npm/pikaday@1.8.0/pikaday.js',
            'config' => [
                'format' => 'YYYY-MM-DD',
                'firstDay' => 1,
            ],
        ],
        'native' => [
            'name' => 'Native HTML5',
            'version' => null,
            'css_cdn' => null,
            'js_cdn' => null,
            'config' => [],
        ],
    ];

    /**
     * File upload configurations
     */
    public const FILE_UPLOADERS = [
        'dropzone' => [
            'name' => 'Dropzone.js',
            'version' => '6.0.0',
            'css_cdn' => 'https://cdn.jsdelivr.net/npm/dropzone@6.0.0/dist/dropzone.css',
            'js_cdn' => 'https://cdn.jsdelivr.net/npm/dropzone@6.0.0/dist/dropzone-min.js',
            'config' => [
                'maxFilesize' => 10,
                'acceptedFiles' => null,
                'addRemoveLinks' => true,
                'dictDefaultMessage' => 'Drop files here or click to upload',
            ],
        ],
        'filepond' => [
            'name' => 'FilePond',
            'version' => '4.30.0',
            'css_cdn' => 'https://cdn.jsdelivr.net/npm/filepond@4.30.0/dist/filepond.css',
            'js_cdn' => 'https://cdn.jsdelivr.net/npm/filepond@4.30.0/dist/filepond.js',
            'config' => [
                'allowMultiple' => false,
                'allowRevert' => true,
                'server' => '/upload',
            ],
        ],
        'native' => [
            'name' => 'Native HTML5',
            'version' => null,
            'css_cdn' => null,
            'js_cdn' => null,
            'config' => [],
        ],
    ];

    /**
     * Validation styles
     */
    public const VALIDATION_STYLES = [
        'inline' => 'Show validation errors inline below each field',
        'summary' => 'Show validation errors in a summary at the top of the form',
        'both' => 'Show validation errors both inline and in a summary',
    ];

    /**
     * Field types and their configurations
     */
    public const FIELD_TYPES = [
        'text' => [
            'name' => 'Text Input',
            'html_type' => 'text',
            'validation' => ['string', 'max:255'],
            'attributes' => ['placeholder', 'maxlength', 'readonly', 'disabled'],
        ],
        'email' => [
            'name' => 'Email Input',
            'html_type' => 'email',
            'validation' => ['email', 'max:255'],
            'attributes' => ['placeholder', 'readonly', 'disabled'],
        ],
        'password' => [
            'name' => 'Password Input',
            'html_type' => 'password',
            'validation' => ['string', 'min:8'],
            'attributes' => ['placeholder', 'readonly', 'disabled'],
        ],
        'number' => [
            'name' => 'Number Input',
            'html_type' => 'number',
            'validation' => ['numeric'],
            'attributes' => ['min', 'max', 'step', 'placeholder', 'readonly', 'disabled'],
        ],
        'textarea' => [
            'name' => 'Textarea',
            'html_type' => 'textarea',
            'validation' => ['string'],
            'attributes' => ['rows', 'cols', 'placeholder', 'maxlength', 'readonly', 'disabled'],
        ],
        'select' => [
            'name' => 'Select Dropdown',
            'html_type' => 'select',
            'validation' => ['in:options'],
            'attributes' => ['multiple', 'size', 'disabled'],
        ],
        'checkbox' => [
            'name' => 'Checkbox',
            'html_type' => 'checkbox',
            'validation' => ['boolean'],
            'attributes' => ['checked', 'disabled'],
        ],
        'radio' => [
            'name' => 'Radio Button',
            'html_type' => 'radio',
            'validation' => ['in:options'],
            'attributes' => ['checked', 'disabled'],
        ],
        'file' => [
            'name' => 'File Upload',
            'html_type' => 'file',
            'validation' => ['file', 'max:10240'],
            'attributes' => ['accept', 'multiple', 'disabled'],
        ],
        'date' => [
            'name' => 'Date Input',
            'html_type' => 'date',
            'validation' => ['date'],
            'attributes' => ['min', 'max', 'readonly', 'disabled'],
        ],
        'datetime' => [
            'name' => 'DateTime Input',
            'html_type' => 'datetime-local',
            'validation' => ['date'],
            'attributes' => ['min', 'max', 'readonly', 'disabled'],
        ],
        'time' => [
            'name' => 'Time Input',
            'html_type' => 'time',
            'validation' => ['date_format:H:i'],
            'attributes' => ['min', 'max', 'readonly', 'disabled'],
        ],
        'hidden' => [
            'name' => 'Hidden Input',
            'html_type' => 'hidden',
            'validation' => [],
            'attributes' => [],
        ],
    ];

    /**
     * Layout types and their descriptions
     */
    public const LAYOUT_TYPES = [
        'app' => 'Main application layout with header, footer, and navigation',
        'admin' => 'Admin dashboard layout with sidebar navigation',
        'auth' => 'Authentication layout for login/register pages',
        'guest' => 'Public-facing layout for non-authenticated users',
        'error' => 'Error page layout for 404, 500, etc.',
        'blank' => 'Minimal layout with no header/footer',
        'print' => 'Print-friendly layout without navigation',
        'email' => 'Email template layout',
        'pdf' => 'PDF generation layout',
        'modal' => 'Modal container layout',
        'sidebar' => 'Sidebar navigation layout',
        'navbar' => 'Top navigation bar layout',
    ];

    /**
     * Component types that can be generated
     */
    public const COMPONENT_TYPES = [
        'form' => 'Form components (input, select, textarea, etc.)',
        'navigation' => 'Navigation components (navbar, breadcrumb, pagination)',
        'display' => 'Display components (table, card, badge, alert)',
        'interactive' => 'Interactive components (modal, dropdown, tabs)',
        'layout' => 'Layout components (header, footer, sidebar)',
        'utility' => 'Utility components (spinner, progress, tooltip)',
    ];

    /**
     * Get configuration value with fallback to default
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $keys = explode('.', $key);
        $config = config('wink-views', self::DEFAULT_CONFIG);
        
        foreach ($keys as $segment) {
            if (is_array($config) && array_key_exists($segment, $config)) {
                $config = $config[$segment];
            } else {
                return $default;
            }
        }
        
        return $config;
    }

    /**
     * Get framework configuration
     */
    public static function getFrameworkConfig(string $framework): ?array
    {
        return self::SUPPORTED_FRAMEWORKS[$framework] ?? null;
    }

    /**
     * Get icon set configuration
     */
    public static function getIconSetConfig(string $iconSet): ?array
    {
        return self::ICON_SETS[$iconSet] ?? null;
    }

    /**
     * Get rich text editor configuration
     */
    public static function getRichTextEditorConfig(string $editor): ?array
    {
        return self::RICH_TEXT_EDITORS[$editor] ?? null;
    }

    /**
     * Get date picker configuration
     */
    public static function getDatePickerConfig(string $picker): ?array
    {
        return self::DATE_PICKERS[$picker] ?? null;
    }

    /**
     * Get file uploader configuration
     */
    public static function getFileUploaderConfig(string $uploader): ?array
    {
        return self::FILE_UPLOADERS[$uploader] ?? null;
    }

    /**
     * Get field type configuration
     */
    public static function getFieldTypeConfig(string $fieldType): ?array
    {
        return self::FIELD_TYPES[$fieldType] ?? null;
    }

    /**
     * Check if a framework is supported
     */
    public static function isFrameworkSupported(string $framework): bool
    {
        return array_key_exists($framework, self::SUPPORTED_FRAMEWORKS);
    }

    /**
     * Get all supported frameworks
     */
    public static function getSupportedFrameworks(): array
    {
        return array_keys(self::SUPPORTED_FRAMEWORKS);
    }

    /**
     * Get all supported icon sets
     */
    public static function getSupportedIconSets(): array
    {
        return array_keys(self::ICON_SETS);
    }

    /**
     * Get all supported rich text editors
     */
    public static function getSupportedRichTextEditors(): array
    {
        return array_keys(self::RICH_TEXT_EDITORS);
    }

    /**
     * Get all supported date pickers
     */
    public static function getSupportedDatePickers(): array
    {
        return array_keys(self::DATE_PICKERS);
    }

    /**
     * Get all supported file uploaders
     */
    public static function getSupportedFileUploaders(): array
    {
        return array_keys(self::FILE_UPLOADERS);
    }

    /**
     * Validate configuration
     */
    public static function validateConfig(array $config): array
    {
        $errors = [];
        
        // Validate framework
        if (isset($config['framework']) && !self::isFrameworkSupported($config['framework'])) {
            $errors[] = "Unsupported framework: {$config['framework']}";
        }
        
        // Validate icon set
        if (isset($config['styling']['icons']) && !array_key_exists($config['styling']['icons'], self::ICON_SETS)) {
            $errors[] = "Unsupported icon set: {$config['styling']['icons']}";
        }
        
        // Validate rich text editor
        if (isset($config['forms']['rich_text_editor']) && !array_key_exists($config['forms']['rich_text_editor'], self::RICH_TEXT_EDITORS)) {
            $errors[] = "Unsupported rich text editor: {$config['forms']['rich_text_editor']}";
        }
        
        // Validate date picker
        if (isset($config['forms']['date_picker']) && !array_key_exists($config['forms']['date_picker'], self::DATE_PICKERS)) {
            $errors[] = "Unsupported date picker: {$config['forms']['date_picker']}";
        }
        
        // Validate file uploader
        if (isset($config['forms']['file_upload']) && !array_key_exists($config['forms']['file_upload'], self::FILE_UPLOADERS)) {
            $errors[] = "Unsupported file uploader: {$config['forms']['file_upload']}";
        }
        
        // Validate validation style
        if (isset($config['forms']['validation_style']) && !array_key_exists($config['forms']['validation_style'], self::VALIDATION_STYLES)) {
            $errors[] = "Unsupported validation style: {$config['forms']['validation_style']}";
        }
        
        return $errors;
    }
}