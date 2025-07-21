<?php

namespace Wink\ViewGenerator\Tests\Utilities;

use Mockery;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Schema;

class MockDataProvider
{
    /**
     * Create mock filesystem for testing file operations
     */
    public static function mockFilesystem(): Mockery\MockInterface
    {
        $mock = Mockery::mock(Filesystem::class);
        
        // Default behaviors
        $mock->shouldReceive('exists')->andReturn(true);
        $mock->shouldReceive('get')->andReturn('mock file content');
        $mock->shouldReceive('put')->andReturn(true);
        $mock->shouldReceive('makeDirectory')->andReturn(true);
        $mock->shouldReceive('ensureDirectoryExists')->andReturn(true);
        $mock->shouldReceive('delete')->andReturn(true);
        $mock->shouldReceive('deleteDirectory')->andReturn(true);

        return $mock;
    }

    /**
     * Create mock model analyzer with predefined data
     */
    public static function mockModelAnalyzer(string $table = 'users'): Mockery\MockInterface
    {
        $mock = Mockery::mock(\Wink\ViewGenerator\Analyzers\ModelAnalyzer::class);
        
        $mock->shouldReceive('analyze')->andReturn([
            'table' => $table,
            'model_name' => ucfirst($table),
            'columns' => TestDataGenerator::generateSampleColumns($table),
            'relationships' => TestDataGenerator::generateSampleRelationships($table),
            'indexes' => [],
            'primary_key' => 'id',
            'timestamps' => true,
            'soft_deletes' => false,
        ]);

        return $mock;
    }

    /**
     * Create mock field analyzer with predefined data
     */
    public static function mockFieldAnalyzer(array $columns = []): Mockery\MockInterface
    {
        $mock = Mockery::mock(\Wink\ViewGenerator\Analyzers\FieldAnalyzer::class);
        
        if (empty($columns)) {
            $columns = TestDataGenerator::generateSampleColumns('users');
        }

        $formFields = [];
        $tableFields = [];

        foreach ($columns as $column) {
            if (!in_array($column['name'], ['id', 'created_at', 'updated_at', 'password', 'remember_token'])) {
                $formFields[] = [
                    'name' => $column['name'],
                    'label' => ucwords(str_replace('_', ' ', $column['name'])),
                    'input_type' => TestDataGenerator::mapColumnTypeToInputType($column),
                    'required' => !$column['nullable'] && $column['default'] === null,
                    'validation' => TestDataGenerator::generateValidationRules($column),
                ];
            }

            if (!in_array($column['name'], ['password', 'remember_token'])) {
                $tableFields[] = [
                    'name' => $column['name'],
                    'label' => ucwords(str_replace('_', ' ', $column['name'])),
                    'display_type' => 'text',
                    'sortable' => true,
                    'filterable' => in_array($column['name'], ['status', 'is_active', 'type']),
                ];
            }
        }

        $mock->shouldReceive('analyzeForForms')->andReturn($formFields);
        $mock->shouldReceive('analyzeForTables')->andReturn($tableFields);

        return $mock;
    }

    /**
     * Create mock controller analyzer
     */
    public static function mockControllerAnalyzer(string $controller = 'UserController'): Mockery\MockInterface
    {
        $mock = Mockery::mock(\Wink\ViewGenerator\Analyzers\ControllerAnalyzer::class);
        
        $mock->shouldReceive('analyze')->andReturn([
            'class' => $controller,
            'methods' => ['index', 'show', 'create', 'store', 'edit', 'update', 'destroy'],
            'validation_rules' => [
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users',
            ],
            'middleware' => ['auth', 'verified'],
        ]);

        return $mock;
    }

    /**
     * Create mock route analyzer
     */
    public static function mockRouteAnalyzer(): Mockery\MockInterface
    {
        $mock = Mockery::mock(\Wink\ViewGenerator\Analyzers\RouteAnalyzer::class);
        
        $mock->shouldReceive('analyze')->andReturn([
            'routes' => [
                ['method' => 'GET', 'uri' => 'users', 'name' => 'users.index'],
                ['method' => 'GET', 'uri' => 'users/create', 'name' => 'users.create'],
                ['method' => 'POST', 'uri' => 'users', 'name' => 'users.store'],
                ['method' => 'GET', 'uri' => 'users/{user}', 'name' => 'users.show'],
                ['method' => 'GET', 'uri' => 'users/{user}/edit', 'name' => 'users.edit'],
                ['method' => 'PUT', 'uri' => 'users/{user}', 'name' => 'users.update'],
                ['method' => 'DELETE', 'uri' => 'users/{user}', 'name' => 'users.destroy'],
            ],
            'resource_routes' => ['users'],
            'middleware' => ['web', 'auth'],
        ]);

        return $mock;
    }

    /**
     * Create mock view generator
     */
    public static function mockViewGenerator(bool $success = true): Mockery\MockInterface
    {
        $mock = Mockery::mock(\Wink\ViewGenerator\Generators\AbstractViewGenerator::class);
        
        $result = $success ? [
            ['file' => 'views/users/index.blade.php', 'success' => true],
            ['file' => 'views/users/show.blade.php', 'success' => true],
            ['file' => 'views/users/create.blade.php', 'success' => true],
            ['file' => 'views/users/edit.blade.php', 'success' => true],
        ] : [
            ['file' => 'views/users/index.blade.php', 'success' => false, 'error' => 'Mock error'],
        ];

        $mock->shouldReceive('generate')->andReturn($result);

        return $mock;
    }

    /**
     * Create mock CRUD view generator
     */
    public static function mockCrudViewGenerator(bool $success = true): Mockery\MockInterface
    {
        $mock = Mockery::mock(\Wink\ViewGenerator\Generators\CrudViewGenerator::class);
        
        $result = $success ? [
            ['file' => 'views/users/index.blade.php', 'success' => true],
            ['file' => 'views/users/show.blade.php', 'success' => true],
            ['file' => 'views/users/create.blade.php', 'success' => true],
            ['file' => 'views/users/edit.blade.php', 'success' => true],
            ['file' => 'views/users/_form.blade.php', 'success' => true],
        ] : [
            ['file' => 'CRUD views for users', 'success' => false, 'error' => 'Mock generation error'],
        ];

        $mock->shouldReceive('generate')->andReturn($result);

        return $mock;
    }

    /**
     * Create mock component generator
     */
    public static function mockComponentGenerator(): Mockery\MockInterface
    {
        $mock = Mockery::mock(\Wink\ViewGenerator\Generators\ComponentGenerator::class);
        
        $mock->shouldReceive('generate')->andReturn([
            ['file' => 'views/components/form-input.blade.php', 'success' => true],
        ]);

        $mock->shouldReceive('generateType')->andReturn([
            ['file' => 'views/components/form-input.blade.php', 'success' => true],
            ['file' => 'views/components/form-select.blade.php', 'success' => true],
            ['file' => 'views/components/form-textarea.blade.php', 'success' => true],
        ]);

        return $mock;
    }

    /**
     * Mock database schema for testing
     */
    public static function mockDatabaseSchema(array $tables = ['users']): void
    {
        foreach ($tables as $table) {
            $columns = array_keys(TestDataGenerator::generateSampleColumns($table));
            
            Schema::shouldReceive('hasTable')
                ->with($table)
                ->andReturn(true);
                
            Schema::shouldReceive('getColumnListing')
                ->with($table)
                ->andReturn($columns);
                
            foreach ($columns as $column) {
                Schema::shouldReceive('getColumnType')
                    ->with($table, $column)
                    ->andReturn('string');
            }
        }
    }

    /**
     * Create sample validation errors for testing
     */
    public static function generateValidationErrors(): array
    {
        return [
            'name' => ['The name field is required.'],
            'email' => ['The email field must be a valid email address.'],
            'password' => ['The password field must be at least 8 characters.'],
        ];
    }

    /**
     * Create sample request data for testing
     */
    public static function generateRequestData(string $table = 'users'): array
    {
        $data = [];

        switch ($table) {
            case 'users':
                $data = [
                    'name' => 'John Doe',
                    'email' => 'john@example.com',
                    'password' => 'password123',
                    'birth_date' => '1990-01-01',
                    'is_admin' => false,
                    'status' => 'active',
                ];
                break;

            case 'posts':
                $data = [
                    'title' => 'Sample Post Title',
                    'slug' => 'sample-post-title',
                    'content' => 'This is the content of the sample post.',
                    'excerpt' => 'This is a sample excerpt.',
                    'status' => 'published',
                    'featured' => true,
                    'user_id' => 1,
                ];
                break;

            case 'categories':
                $data = [
                    'name' => 'Technology',
                    'slug' => 'technology',
                    'description' => 'Posts about technology and programming.',
                    'color' => '#007bff',
                    'is_active' => true,
                    'sort_order' => 1,
                ];
                break;
        }

        return $data;
    }

    /**
     * Create mock Eloquent model instance
     */
    public static function mockModel(string $table = 'users', array $attributes = []): Mockery\MockInterface
    {
        $mock = Mockery::mock('Illuminate\Database\Eloquent\Model');
        
        $defaultAttributes = static::generateRequestData($table);
        $attributes = array_merge($defaultAttributes, $attributes);
        
        foreach ($attributes as $key => $value) {
            $mock->shouldReceive('getAttribute')
                ->with($key)
                ->andReturn($value);
            
            $mock->{$key} = $value;
        }

        $mock->shouldReceive('getTable')->andReturn($table);
        $mock->shouldReceive('getKeyName')->andReturn('id');
        $mock->shouldReceive('getKey')->andReturn(1);
        $mock->shouldReceive('exists')->andReturn(true);
        $mock->shouldReceive('toArray')->andReturn($attributes);

        return $mock;
    }

    /**
     * Create mock collection of models
     */
    public static function mockCollection(string $table = 'users', int $count = 3): Mockery\MockInterface
    {
        $mock = Mockery::mock('Illuminate\Database\Eloquent\Collection');
        
        $items = [];
        for ($i = 1; $i <= $count; $i++) {
            $items[] = static::mockModel($table, ['id' => $i]);
        }
        
        $mock->shouldReceive('all')->andReturn($items);
        $mock->shouldReceive('count')->andReturn($count);
        $mock->shouldReceive('isEmpty')->andReturn($count === 0);
        $mock->shouldReceive('isNotEmpty')->andReturn($count > 0);
        
        return $mock;
    }

    /**
     * Reset all mocks
     */
    public static function resetMocks(): void
    {
        Mockery::close();
    }
}