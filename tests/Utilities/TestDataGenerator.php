<?php

namespace Wink\ViewGenerator\Tests\Utilities;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\File;

class TestDataGenerator
{
    /**
     * Generate sample model data for testing
     */
    public static function generateModelData(string $table = 'users', array $options = []): array
    {
        $defaults = [
            'table' => $table,
            'model_name' => ucfirst(str_replace('_', '', $table)),
            'columns' => static::generateSampleColumns($table),
            'relationships' => static::generateSampleRelationships($table),
            'indexes' => [],
            'primary_key' => 'id',
            'timestamps' => true,
            'soft_deletes' => false,
        ];

        return array_merge($defaults, $options);
    }

    /**
     * Generate sample columns for different table types
     */
    public static function generateSampleColumns(string $table): array
    {
        $commonColumns = [
            'id' => ['name' => 'id', 'type' => 'integer', 'nullable' => false, 'default' => null],
            'created_at' => ['name' => 'created_at', 'type' => 'timestamp', 'nullable' => true, 'default' => null],
            'updated_at' => ['name' => 'updated_at', 'type' => 'timestamp', 'nullable' => true, 'default' => null],
        ];

        $specificColumns = [];

        switch ($table) {
            case 'users':
                $specificColumns = [
                    'name' => ['name' => 'name', 'type' => 'string', 'nullable' => false, 'default' => null],
                    'email' => ['name' => 'email', 'type' => 'string', 'nullable' => false, 'default' => null],
                    'password' => ['name' => 'password', 'type' => 'string', 'nullable' => false, 'default' => null],
                    'birth_date' => ['name' => 'birth_date', 'type' => 'date', 'nullable' => true, 'default' => null],
                    'is_admin' => ['name' => 'is_admin', 'type' => 'boolean', 'nullable' => false, 'default' => false],
                    'status' => ['name' => 'status', 'type' => 'string', 'nullable' => false, 'default' => 'active'],
                    'avatar' => ['name' => 'avatar', 'type' => 'string', 'nullable' => true, 'default' => null],
                ];
                break;

            case 'posts':
                $specificColumns = [
                    'title' => ['name' => 'title', 'type' => 'string', 'nullable' => false, 'default' => null],
                    'slug' => ['name' => 'slug', 'type' => 'string', 'nullable' => false, 'default' => null],
                    'content' => ['name' => 'content', 'type' => 'text', 'nullable' => false, 'default' => null],
                    'excerpt' => ['name' => 'excerpt', 'type' => 'text', 'nullable' => true, 'default' => null],
                    'status' => ['name' => 'status', 'type' => 'string', 'nullable' => false, 'default' => 'draft'],
                    'featured' => ['name' => 'featured', 'type' => 'boolean', 'nullable' => false, 'default' => false],
                    'published_at' => ['name' => 'published_at', 'type' => 'datetime', 'nullable' => true, 'default' => null],
                    'user_id' => ['name' => 'user_id', 'type' => 'integer', 'nullable' => false, 'default' => null],
                ];
                break;

            case 'categories':
                $specificColumns = [
                    'name' => ['name' => 'name', 'type' => 'string', 'nullable' => false, 'default' => null],
                    'slug' => ['name' => 'slug', 'type' => 'string', 'nullable' => false, 'default' => null],
                    'description' => ['name' => 'description', 'type' => 'text', 'nullable' => true, 'default' => null],
                    'color' => ['name' => 'color', 'type' => 'string', 'nullable' => false, 'default' => '#000000'],
                    'is_active' => ['name' => 'is_active', 'type' => 'boolean', 'nullable' => false, 'default' => true],
                    'sort_order' => ['name' => 'sort_order', 'type' => 'integer', 'nullable' => false, 'default' => 0],
                    'parent_id' => ['name' => 'parent_id', 'type' => 'integer', 'nullable' => true, 'default' => null],
                ];
                break;

            default:
                $specificColumns = [
                    'name' => ['name' => 'name', 'type' => 'string', 'nullable' => false, 'default' => null],
                    'description' => ['name' => 'description', 'type' => 'text', 'nullable' => true, 'default' => null],
                    'is_active' => ['name' => 'is_active', 'type' => 'boolean', 'nullable' => false, 'default' => true],
                ];
                break;
        }

        return array_merge($commonColumns, $specificColumns);
    }

    /**
     * Generate sample relationships
     */
    public static function generateSampleRelationships(string $table): array
    {
        $relationships = [];

        switch ($table) {
            case 'users':
                $relationships = [
                    ['type' => 'hasMany', 'name' => 'posts', 'foreign_key' => 'user_id', 'related_table' => 'posts', 'related_model' => 'Post'],
                ];
                break;

            case 'posts':
                $relationships = [
                    ['type' => 'belongsTo', 'name' => 'user', 'foreign_key' => 'user_id', 'related_table' => 'users', 'related_model' => 'User'],
                    ['type' => 'belongsToMany', 'name' => 'categories', 'foreign_key' => 'post_id', 'related_table' => 'categories', 'related_model' => 'Category'],
                ];
                break;

            case 'categories':
                $relationships = [
                    ['type' => 'belongsTo', 'name' => 'parent', 'foreign_key' => 'parent_id', 'related_table' => 'categories', 'related_model' => 'Category'],
                    ['type' => 'hasMany', 'name' => 'children', 'foreign_key' => 'parent_id', 'related_table' => 'categories', 'related_model' => 'Category'],
                    ['type' => 'belongsToMany', 'name' => 'posts', 'foreign_key' => 'category_id', 'related_table' => 'posts', 'related_model' => 'Post'],
                ];
                break;
        }

        return $relationships;
    }

    /**
     * Generate sample field data for form analysis
     */
    public static function generateFieldData(string $table = 'users'): array
    {
        $columns = static::generateSampleColumns($table);
        
        return array_map(function ($column) {
            return [
                'name' => $column['name'],
                'label' => ucwords(str_replace('_', ' ', $column['name'])),
                'input_type' => static::mapColumnTypeToInputType($column),
                'required' => !$column['nullable'] && $column['default'] === null,
                'validation' => static::generateValidationRules($column),
            ];
        }, $columns);
    }

    /**
     * Map database column type to form input type
     */
    public static function mapColumnTypeToInputType(array $column): string
    {
        $name = $column['name'];
        $type = $column['type'];

        // Check by name first
        if (str_contains($name, 'email')) return 'email';
        if (str_contains($name, 'password')) return 'password';
        if (str_contains($name, 'description') || str_contains($name, 'content')) return 'textarea';
        if (str_contains($name, 'image') || str_contains($name, 'avatar')) return 'file';

        // Check by type
        switch ($type) {
            case 'boolean': return 'checkbox';
            case 'date': return 'date';
            case 'datetime':
            case 'timestamp': return 'datetime-local';
            case 'integer': return 'number';
            case 'text': return 'textarea';
            default: return 'text';
        }
    }

    /**
     * Generate validation rules for a column
     */
    public static function generateValidationRules(array $column): array
    {
        $rules = [];

        if (!$column['nullable'] && $column['default'] === null) {
            $rules[] = 'required';
        }

        switch ($column['type']) {
            case 'string':
                $rules[] = 'string';
                $rules[] = 'max:255';
                break;
            case 'integer':
                $rules[] = 'integer';
                break;
            case 'boolean':
                $rules[] = 'boolean';
                break;
            case 'date':
            case 'datetime':
            case 'timestamp':
                $rules[] = 'date';
                break;
        }

        if (str_contains($column['name'], 'email')) {
            $rules[] = 'email';
        }

        return $rules;
    }

    /**
     * Generate sample view options
     */
    public static function generateViewOptions(array $overrides = []): array
    {
        $defaults = [
            'framework' => 'bootstrap',
            'components' => true,
            'layouts' => true,
            'force' => false,
            'dry_run' => false,
            'output_path' => 'resources/views',
            'namespace' => 'App\\Models',
        ];

        return array_merge($defaults, $overrides);
    }

    /**
     * Create sample stub content
     */
    public static function createSampleStub(string $type = 'crud', string $framework = 'bootstrap'): string
    {
        $stubs = [
            'crud' => [
                'bootstrap' => '@extends(\'layouts.app\')

@section(\'title\', \'{{ title }}\')

@section(\'content\')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <h1>{{ heading }}</h1>
            
            <div class="card">
                <div class="card-body">
                    {{ content }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection',
                'tailwind' => '@extends(\'layouts.app\')

@section(\'title\', \'{{ title }}\')

@section(\'content\')
<div class="container mx-auto px-4">
    <div class="max-w-7xl mx-auto">
        <h1 class="text-3xl font-bold">{{ heading }}</h1>
        
        <div class="bg-white shadow-lg rounded-lg">
            <div class="p-6">
                {{ content }}
            </div>
        </div>
    </div>
</div>
@endsection',
            ],
            'component' => [
                'bootstrap' => '<div class="form-group">
    <label for="{{ field_id }}" class="form-label">{{ label }}</label>
    <input type="{{ input_type }}" 
           id="{{ field_id }}" 
           name="{{ field_name }}" 
           class="form-control @error(\'{{ field_name }}\') is-invalid @enderror"
           value="{{ old(\'{{ field_name }}\', ${{ model_variable }}->{{ field_name }} ?? \'\') }}"
           {{ required ? \'required\' : \'\' }}>
    
    @error(\'{{ field_name }}\')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>',
                'tailwind' => '<div class="mb-4">
    <label for="{{ field_id }}" class="block text-sm font-medium text-gray-700">{{ label }}</label>
    <input type="{{ input_type }}" 
           id="{{ field_id }}" 
           name="{{ field_name }}" 
           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error(\'{{ field_name }}\') border-red-500 @enderror"
           value="{{ old(\'{{ field_name }}\', ${{ model_variable }}->{{ field_name }} ?? \'\') }}"
           {{ required ? \'required\' : \'\' }}>
    
    @error(\'{{ field_name }}\')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>',
            ],
        ];

        return $stubs[$type][$framework] ?? 'Default stub content for {{ type }} with {{ framework }}';
    }

    /**
     * Create complete test dataset
     */
    public static function createCompleteTestDataset(): array
    {
        return [
            'users' => [
                'model_data' => static::generateModelData('users'),
                'field_data' => static::generateFieldData('users'),
                'options' => static::generateViewOptions(),
            ],
            'posts' => [
                'model_data' => static::generateModelData('posts'),
                'field_data' => static::generateFieldData('posts'),
                'options' => static::generateViewOptions(['framework' => 'tailwind']),
            ],
            'categories' => [
                'model_data' => static::generateModelData('categories'),
                'field_data' => static::generateFieldData('categories'),
                'options' => static::generateViewOptions(['framework' => 'custom']),
            ],
        ];
    }

    /**
     * Generate configuration test data
     */
    public static function generateConfigTestData(): array
    {
        return [
            'valid_config' => [
                'framework' => 'bootstrap',
                'layout' => [
                    'master' => 'layouts.app',
                    'admin' => 'layouts.admin',
                ],
                'components' => [
                    'use_components' => true,
                    'component_namespace' => 'components',
                ],
                'features' => [
                    'pagination' => true,
                    'search' => true,
                    'filtering' => true,
                ],
                'styling' => [
                    'dark_mode' => true,
                    'icons' => 'bootstrap-icons',
                ],
                'forms' => [
                    'validation_style' => 'inline',
                    'rich_text_editor' => 'tinymce',
                    'date_picker' => 'flatpickr',
                ],
            ],
            'invalid_config' => [
                'framework' => 'invalid_framework',
                'styling' => ['icons' => 'invalid_icons'],
                'forms' => [
                    'rich_text_editor' => 'invalid_editor',
                    'validation_style' => 'invalid_style',
                ],
            ],
        ];
    }
}