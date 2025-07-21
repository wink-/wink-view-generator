<?php

namespace Wink\ViewGenerator\Tests\Unit\Generators;

use Wink\ViewGenerator\Tests\TestCase;
use Wink\ViewGenerator\Generators\CrudViewGenerator;

class CrudViewGeneratorTest extends TestCase
{
    protected CrudViewGenerator $generator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->generator = new CrudViewGenerator();
    }

    /** @test */
    public function it_can_be_instantiated()
    {
        $generator = new CrudViewGenerator();
        $this->assertInstanceOf(CrudViewGenerator::class, $generator);
    }

    /** @test */
    public function it_extends_abstract_view_generator()
    {
        $this->assertInstanceOf(\Wink\ViewGenerator\Generators\AbstractViewGenerator::class, $this->generator);
    }

    /** @test */
    public function it_generates_crud_views_successfully()
    {
        $table = 'users';
        $modelData = [
            'model_name' => 'User',
            'columns' => [
                ['name' => 'name', 'type' => 'string'],
                ['name' => 'email', 'type' => 'string'],
            ],
        ];
        $options = [
            'framework' => 'bootstrap',
            'force' => false,
        ];

        $result = $this->generator->generate($table, $modelData, $options);

        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
    }

    /** @test */
    public function it_generates_multiple_crud_views()
    {
        $table = 'posts';
        $modelData = ['model_name' => 'Post'];
        $options = ['framework' => 'bootstrap'];

        $result = $this->generator->generate($table, $modelData, $options);

        // Should generate multiple views
        $this->assertIsArray($result);
        $this->assertGreaterThan(1, count($result));
        
        // Each result should have specific structure
        foreach ($result as $viewResult) {
            $this->assertIsArray($viewResult);
            $this->assertArrayHasKey('file', $viewResult);
            $this->assertArrayHasKey('success', $viewResult);
        }
    }

    /** @test */
    public function it_generates_index_view()
    {
        $table = 'users';
        $modelData = ['model_name' => 'User'];
        $options = ['framework' => 'bootstrap'];

        $result = $this->generator->generate($table, $modelData, $options);

        // Look for index view in results
        $indexView = collect($result)->first(function ($view) use ($table) {
            return str_contains($view['file'], "{$table}/index.blade.php");
        });

        $this->assertNotNull($indexView);
        $this->assertTrue($indexView['success']);
        $this->assertEquals("views/{$table}/index.blade.php", $indexView['file']);
    }

    /** @test */
    public function it_generates_show_view()
    {
        $table = 'users';
        $modelData = ['model_name' => 'User'];
        $options = ['framework' => 'bootstrap'];

        $result = $this->generator->generate($table, $modelData, $options);

        // Look for show view in results
        $showView = collect($result)->first(function ($view) use ($table) {
            return str_contains($view['file'], "{$table}/show.blade.php");
        });

        $this->assertNotNull($showView);
        $this->assertTrue($showView['success']);
        $this->assertEquals("views/{$table}/show.blade.php", $showView['file']);
    }

    /** @test */
    public function it_generates_create_view()
    {
        $table = 'users';
        $modelData = ['model_name' => 'User'];
        $options = ['framework' => 'bootstrap'];

        $result = $this->generator->generate($table, $modelData, $options);

        // Look for create view in results
        $createView = collect($result)->first(function ($view) use ($table) {
            return str_contains($view['file'], "{$table}/create.blade.php");
        });

        $this->assertNotNull($createView);
        $this->assertTrue($createView['success']);
        $this->assertEquals("views/{$table}/create.blade.php", $createView['file']);
    }

    /** @test */
    public function it_generates_edit_view()
    {
        $table = 'users';
        $modelData = ['model_name' => 'User'];
        $options = ['framework' => 'bootstrap'];

        $result = $this->generator->generate($table, $modelData, $options);

        // Look for edit view in results
        $editView = collect($result)->first(function ($view) use ($table) {
            return str_contains($view['file'], "{$table}/edit.blade.php");
        });

        $this->assertNotNull($editView);
        $this->assertTrue($editView['success']);
        $this->assertEquals("views/{$table}/edit.blade.php", $editView['file']);
    }

    /** @test */
    public function it_generates_form_partial()
    {
        $table = 'users';
        $modelData = ['model_name' => 'User'];
        $options = ['framework' => 'bootstrap'];

        $result = $this->generator->generate($table, $modelData, $options);

        // Look for form partial in results
        $formPartial = collect($result)->first(function ($view) use ($table) {
            return str_contains($view['file'], "{$table}/_form.blade.php") || 
                   str_contains($view['file'], "{$table}/form.blade.php");
        });

        $this->assertNotNull($formPartial);
        $this->assertTrue($formPartial['success']);
    }

    /** @test */
    public function it_handles_different_table_names()
    {
        $testTables = ['users', 'blog_posts', 'categories', 'user_profiles'];

        foreach ($testTables as $table) {
            $modelData = ['model_name' => ucfirst($table)];
            $options = ['framework' => 'bootstrap'];

            $result = $this->generator->generate($table, $modelData, $options);

            $this->assertIsArray($result);
            $this->assertNotEmpty($result);

            // Each result should reference the correct table
            foreach ($result as $viewResult) {
                $this->assertStringContainsString($table, $viewResult['file']);
            }
        }
    }

    /** @test */
    public function it_handles_different_frameworks()
    {
        $frameworks = ['bootstrap', 'tailwind', 'custom'];
        $table = 'users';
        $modelData = ['model_name' => 'User'];

        foreach ($frameworks as $framework) {
            $options = ['framework' => $framework];
            $result = $this->generator->generate($table, $modelData, $options);

            $this->assertIsArray($result);
            $this->assertNotEmpty($result);
            
            // All views should be generated successfully
            foreach ($result as $viewResult) {
                $this->assertTrue($viewResult['success']);
            }
        }
    }

    /** @test */
    public function it_handles_empty_model_data()
    {
        $table = 'users';
        $modelData = [];
        $options = ['framework' => 'bootstrap'];

        $result = $this->generator->generate($table, $modelData, $options);

        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
    }

    /** @test */
    public function it_handles_empty_options()
    {
        $table = 'users';
        $modelData = ['model_name' => 'User'];
        $options = [];

        $result = $this->generator->generate($table, $modelData, $options);

        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
    }

    /** @test */
    public function it_generates_consistent_file_paths()
    {
        $table = 'users';
        $modelData = ['model_name' => 'User'];
        $options = ['framework' => 'bootstrap'];

        $result = $this->generator->generate($table, $modelData, $options);

        foreach ($result as $viewResult) {
            // All file paths should start with 'views/'
            $this->assertStringStartsWith('views/', $viewResult['file']);
            
            // All file paths should contain the table name
            $this->assertStringContainsString($table, $viewResult['file']);
            
            // All file paths should end with '.blade.php'
            $this->assertStringEndsWith('.blade.php', $viewResult['file']);
        }
    }

    /** @test */
    public function it_generates_all_expected_crud_views()
    {
        $table = 'users';
        $modelData = ['model_name' => 'User'];
        $options = ['framework' => 'bootstrap'];

        $result = $this->generator->generate($table, $modelData, $options);

        $fileNames = array_column($result, 'file');
        $expectedViews = [
            'index.blade.php',
            'show.blade.php',
            'create.blade.php',
            'edit.blade.php',
        ];

        foreach ($expectedViews as $expectedView) {
            $found = collect($fileNames)->first(function ($fileName) use ($expectedView) {
                return str_contains($fileName, $expectedView);
            });
            
            $this->assertNotNull($found, "Expected view {$expectedView} not found in generated files");
        }
    }

    /** @test */
    public function it_returns_success_status_for_all_views()
    {
        $table = 'users';
        $modelData = ['model_name' => 'User'];
        $options = ['framework' => 'bootstrap'];

        $result = $this->generator->generate($table, $modelData, $options);

        foreach ($result as $viewResult) {
            $this->assertTrue($viewResult['success'], 
                "View generation failed for: " . $viewResult['file']);
        }
    }

    /** @test */
    public function it_handles_complex_model_data()
    {
        $table = 'users';
        $modelData = [
            'model_name' => 'User',
            'table' => 'users',
            'columns' => [
                ['name' => 'id', 'type' => 'integer'],
                ['name' => 'name', 'type' => 'string'],
                ['name' => 'email', 'type' => 'string'],
                ['name' => 'created_at', 'type' => 'timestamp'],
            ],
            'relationships' => [
                ['type' => 'hasMany', 'name' => 'posts'],
            ],
            'primary_key' => 'id',
            'timestamps' => true,
        ];
        $options = ['framework' => 'bootstrap'];

        $result = $this->generator->generate($table, $modelData, $options);

        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
        
        foreach ($result as $viewResult) {
            $this->assertTrue($viewResult['success']);
        }
    }
}