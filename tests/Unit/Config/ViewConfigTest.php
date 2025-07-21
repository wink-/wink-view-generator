<?php

namespace Wink\ViewGenerator\Tests\Unit\Config;

use Wink\ViewGenerator\Tests\TestCase;
use Wink\ViewGenerator\Config\ViewConfig;
use Illuminate\Support\Facades\Config;

class ViewConfigTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        // Reset config to default state
        Config::set('wink-views', []);
    }

    /** @test */
    public function it_has_default_configuration_constants()
    {
        $this->assertIsArray(ViewConfig::DEFAULT_CONFIG);
        $this->assertNotEmpty(ViewConfig::DEFAULT_CONFIG);
        
        // Check required keys exist
        $this->assertArrayHasKey('framework', ViewConfig::DEFAULT_CONFIG);
        $this->assertArrayHasKey('layout', ViewConfig::DEFAULT_CONFIG);
        $this->assertArrayHasKey('components', ViewConfig::DEFAULT_CONFIG);
        $this->assertArrayHasKey('features', ViewConfig::DEFAULT_CONFIG);
        $this->assertArrayHasKey('styling', ViewConfig::DEFAULT_CONFIG);
        $this->assertArrayHasKey('forms', ViewConfig::DEFAULT_CONFIG);
        $this->assertArrayHasKey('accessibility', ViewConfig::DEFAULT_CONFIG);
        $this->assertArrayHasKey('performance', ViewConfig::DEFAULT_CONFIG);
    }

    /** @test */
    public function it_has_supported_frameworks_configuration()
    {
        $this->assertIsArray(ViewConfig::SUPPORTED_FRAMEWORKS);
        $this->assertNotEmpty(ViewConfig::SUPPORTED_FRAMEWORKS);
        
        // Check default frameworks exist
        $this->assertArrayHasKey('bootstrap', ViewConfig::SUPPORTED_FRAMEWORKS);
        $this->assertArrayHasKey('tailwind', ViewConfig::SUPPORTED_FRAMEWORKS);
        $this->assertArrayHasKey('custom', ViewConfig::SUPPORTED_FRAMEWORKS);
        
        // Check framework structure
        foreach (ViewConfig::SUPPORTED_FRAMEWORKS as $framework => $config) {
            $this->assertArrayHasKey('name', $config);
            $this->assertArrayHasKey('version', $config);
            $this->assertArrayHasKey('css_cdn', $config);
            $this->assertArrayHasKey('js_cdn', $config);
            $this->assertArrayHasKey('icons', $config);
            $this->assertArrayHasKey('components', $config);
        }
    }

    /** @test */
    public function it_has_icon_sets_configuration()
    {
        $this->assertIsArray(ViewConfig::ICON_SETS);
        $this->assertNotEmpty(ViewConfig::ICON_SETS);
        
        // Check default icon sets
        $this->assertArrayHasKey('bootstrap-icons', ViewConfig::ICON_SETS);
        $this->assertArrayHasKey('heroicons', ViewConfig::ICON_SETS);
        $this->assertArrayHasKey('feather', ViewConfig::ICON_SETS);
        
        // Check icon set structure
        foreach (ViewConfig::ICON_SETS as $iconSet => $config) {
            $this->assertArrayHasKey('name', $config);
            $this->assertArrayHasKey('version', $config);
            $this->assertArrayHasKey('cdn', $config);
            $this->assertArrayHasKey('prefix', $config);
            $this->assertArrayHasKey('common_icons', $config);
        }
    }

    /** @test */
    public function it_has_field_types_configuration()
    {
        $this->assertIsArray(ViewConfig::FIELD_TYPES);
        $this->assertNotEmpty(ViewConfig::FIELD_TYPES);
        
        // Check common field types
        $commonTypes = ['text', 'email', 'password', 'number', 'textarea', 'select', 'checkbox', 'radio', 'file', 'date'];
        foreach ($commonTypes as $type) {
            $this->assertArrayHasKey($type, ViewConfig::FIELD_TYPES);
        }
        
        // Check field type structure
        foreach (ViewConfig::FIELD_TYPES as $type => $config) {
            $this->assertArrayHasKey('name', $config);
            $this->assertArrayHasKey('html_type', $config);
            $this->assertArrayHasKey('validation', $config);
            $this->assertArrayHasKey('attributes', $config);
        }
    }

    /** @test */
    public function it_can_get_configuration_values()
    {
        // Set some config
        Config::set('wink-views.framework', 'tailwind');
        Config::set('wink-views.custom.value', 'test');
        
        $this->assertEquals('tailwind', ViewConfig::get('framework'));
        $this->assertEquals('test', ViewConfig::get('custom.value'));
    }

    /** @test */
    public function it_returns_default_when_config_not_found()
    {
        $this->assertEquals('default', ViewConfig::get('non.existent.key', 'default'));
        $this->assertNull(ViewConfig::get('non.existent.key'));
    }

    /** @test */
    public function it_falls_back_to_default_config()
    {
        // Clear the config
        Config::set('wink-views', null);
        
        $framework = ViewConfig::get('framework');
        $this->assertEquals(ViewConfig::DEFAULT_CONFIG['framework'], $framework);
    }

    /** @test */
    public function it_can_get_framework_configuration()
    {
        $bootstrapConfig = ViewConfig::getFrameworkConfig('bootstrap');
        $this->assertIsArray($bootstrapConfig);
        $this->assertEquals('Bootstrap 5', $bootstrapConfig['name']);
        
        $tailwindConfig = ViewConfig::getFrameworkConfig('tailwind');
        $this->assertIsArray($tailwindConfig);
        $this->assertEquals('Tailwind CSS', $tailwindConfig['name']);
        
        $invalidConfig = ViewConfig::getFrameworkConfig('invalid');
        $this->assertNull($invalidConfig);
    }

    /** @test */
    public function it_can_get_icon_set_configuration()
    {
        $bootstrapIcons = ViewConfig::getIconSetConfig('bootstrap-icons');
        $this->assertIsArray($bootstrapIcons);
        $this->assertEquals('Bootstrap Icons', $bootstrapIcons['name']);
        
        $heroicons = ViewConfig::getIconSetConfig('heroicons');
        $this->assertIsArray($heroicons);
        $this->assertEquals('Heroicons', $heroicons['name']);
        
        $invalidIcons = ViewConfig::getIconSetConfig('invalid');
        $this->assertNull($invalidIcons);
    }

    /** @test */
    public function it_can_get_rich_text_editor_configuration()
    {
        $tinymce = ViewConfig::getRichTextEditorConfig('tinymce');
        $this->assertIsArray($tinymce);
        $this->assertEquals('TinyMCE', $tinymce['name']);
        
        $ckeditor = ViewConfig::getRichTextEditorConfig('ckeditor');
        $this->assertIsArray($ckeditor);
        $this->assertEquals('CKEditor 5', $ckeditor['name']);
        
        $invalid = ViewConfig::getRichTextEditorConfig('invalid');
        $this->assertNull($invalid);
    }

    /** @test */
    public function it_can_get_date_picker_configuration()
    {
        $flatpickr = ViewConfig::getDatePickerConfig('flatpickr');
        $this->assertIsArray($flatpickr);
        $this->assertEquals('Flatpickr', $flatpickr['name']);
        
        $pikaday = ViewConfig::getDatePickerConfig('pikaday');
        $this->assertIsArray($pikaday);
        $this->assertEquals('Pikaday', $pikaday['name']);
        
        $invalid = ViewConfig::getDatePickerConfig('invalid');
        $this->assertNull($invalid);
    }

    /** @test */
    public function it_can_get_file_uploader_configuration()
    {
        $dropzone = ViewConfig::getFileUploaderConfig('dropzone');
        $this->assertIsArray($dropzone);
        $this->assertEquals('Dropzone.js', $dropzone['name']);
        
        $filepond = ViewConfig::getFileUploaderConfig('filepond');
        $this->assertIsArray($filepond);
        $this->assertEquals('FilePond', $filepond['name']);
        
        $invalid = ViewConfig::getFileUploaderConfig('invalid');
        $this->assertNull($invalid);
    }

    /** @test */
    public function it_can_get_field_type_configuration()
    {
        $textConfig = ViewConfig::getFieldTypeConfig('text');
        $this->assertIsArray($textConfig);
        $this->assertEquals('Text Input', $textConfig['name']);
        $this->assertEquals('text', $textConfig['html_type']);
        
        $emailConfig = ViewConfig::getFieldTypeConfig('email');
        $this->assertIsArray($emailConfig);
        $this->assertEquals('Email Input', $emailConfig['name']);
        $this->assertEquals('email', $emailConfig['html_type']);
        
        $invalid = ViewConfig::getFieldTypeConfig('invalid');
        $this->assertNull($invalid);
    }

    /** @test */
    public function it_can_check_if_framework_is_supported()
    {
        $this->assertTrue(ViewConfig::isFrameworkSupported('bootstrap'));
        $this->assertTrue(ViewConfig::isFrameworkSupported('tailwind'));
        $this->assertTrue(ViewConfig::isFrameworkSupported('custom'));
        $this->assertFalse(ViewConfig::isFrameworkSupported('invalid'));
        $this->assertFalse(ViewConfig::isFrameworkSupported(''));
    }

    /** @test */
    public function it_can_get_supported_frameworks_list()
    {
        $frameworks = ViewConfig::getSupportedFrameworks();
        $this->assertIsArray($frameworks);
        $this->assertContains('bootstrap', $frameworks);
        $this->assertContains('tailwind', $frameworks);
        $this->assertContains('custom', $frameworks);
    }

    /** @test */
    public function it_can_get_supported_icon_sets_list()
    {
        $iconSets = ViewConfig::getSupportedIconSets();
        $this->assertIsArray($iconSets);
        $this->assertContains('bootstrap-icons', $iconSets);
        $this->assertContains('heroicons', $iconSets);
        $this->assertContains('feather', $iconSets);
    }

    /** @test */
    public function it_can_get_supported_rich_text_editors_list()
    {
        $editors = ViewConfig::getSupportedRichTextEditors();
        $this->assertIsArray($editors);
        $this->assertContains('tinymce', $editors);
        $this->assertContains('ckeditor', $editors);
        $this->assertContains('none', $editors);
    }

    /** @test */
    public function it_can_get_supported_date_pickers_list()
    {
        $pickers = ViewConfig::getSupportedDatePickers();
        $this->assertIsArray($pickers);
        $this->assertContains('flatpickr', $pickers);
        $this->assertContains('pikaday', $pickers);
        $this->assertContains('native', $pickers);
    }

    /** @test */
    public function it_can_get_supported_file_uploaders_list()
    {
        $uploaders = ViewConfig::getSupportedFileUploaders();
        $this->assertIsArray($uploaders);
        $this->assertContains('dropzone', $uploaders);
        $this->assertContains('filepond', $uploaders);
        $this->assertContains('native', $uploaders);
    }

    /** @test */
    public function it_can_validate_configuration()
    {
        // Valid configuration
        $validConfig = [
            'framework' => 'bootstrap',
            'styling' => ['icons' => 'bootstrap-icons'],
            'forms' => [
                'rich_text_editor' => 'tinymce',
                'date_picker' => 'flatpickr',
                'file_upload' => 'dropzone',
                'validation_style' => 'inline',
            ],
        ];
        
        $errors = ViewConfig::validateConfig($validConfig);
        $this->assertEmpty($errors);
        
        // Invalid configuration
        $invalidConfig = [
            'framework' => 'invalid-framework',
            'styling' => ['icons' => 'invalid-icons'],
            'forms' => [
                'rich_text_editor' => 'invalid-editor',
                'date_picker' => 'invalid-picker',
                'file_upload' => 'invalid-uploader',
                'validation_style' => 'invalid-style',
            ],
        ];
        
        $errors = ViewConfig::validateConfig($invalidConfig);
        $this->assertNotEmpty($errors);
        $this->assertCount(6, $errors);
    }

    /** @test */
    public function it_handles_nested_configuration_keys()
    {
        Config::set('wink-views', [
            'layout' => [
                'master' => 'layouts.custom',
                'admin' => 'layouts.admin',
            ],
            'features' => [
                'pagination' => false,
                'search' => true,
            ],
        ]);
        
        $this->assertEquals('layouts.custom', ViewConfig::get('layout.master'));
        $this->assertEquals('layouts.admin', ViewConfig::get('layout.admin'));
        $this->assertFalse(ViewConfig::get('features.pagination'));
        $this->assertTrue(ViewConfig::get('features.search'));
    }

    /** @test */
    public function it_handles_non_existent_nested_keys()
    {
        Config::set('wink-views', [
            'layout' => [
                'master' => 'layouts.app',
            ],
        ]);
        
        $this->assertNull(ViewConfig::get('layout.non_existent'));
        $this->assertEquals('default', ViewConfig::get('layout.non_existent', 'default'));
        $this->assertNull(ViewConfig::get('non_existent.nested.key'));
    }

    /** @test */
    public function it_has_validation_styles_constants()
    {
        $this->assertIsArray(ViewConfig::VALIDATION_STYLES);
        $this->assertArrayHasKey('inline', ViewConfig::VALIDATION_STYLES);
        $this->assertArrayHasKey('summary', ViewConfig::VALIDATION_STYLES);
        $this->assertArrayHasKey('both', ViewConfig::VALIDATION_STYLES);
    }

    /** @test */
    public function it_has_layout_types_constants()
    {
        $this->assertIsArray(ViewConfig::LAYOUT_TYPES);
        $this->assertNotEmpty(ViewConfig::LAYOUT_TYPES);
        
        $expectedLayouts = ['app', 'admin', 'auth', 'guest', 'error', 'blank'];
        foreach ($expectedLayouts as $layout) {
            $this->assertArrayHasKey($layout, ViewConfig::LAYOUT_TYPES);
        }
    }

    /** @test */
    public function it_has_component_types_constants()
    {
        $this->assertIsArray(ViewConfig::COMPONENT_TYPES);
        $this->assertNotEmpty(ViewConfig::COMPONENT_TYPES);
        
        $expectedComponents = ['form', 'navigation', 'display', 'interactive', 'layout', 'utility'];
        foreach ($expectedComponents as $component) {
            $this->assertArrayHasKey($component, ViewConfig::COMPONENT_TYPES);
        }
    }
}