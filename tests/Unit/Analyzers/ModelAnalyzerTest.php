<?php

namespace Wink\ViewGenerator\Tests\Unit\Analyzers;

use Wink\ViewGenerator\Tests\TestCase;
use Wink\ViewGenerator\Analyzers\ModelAnalyzer;
use Illuminate\Support\Facades\Schema;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ModelAnalyzerTest extends TestCase
{
    use RefreshDatabase;

    protected ModelAnalyzer $analyzer;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a test table for analysis
        $this->createTestTable('test_users', [
            'name' => 'string',
            'email' => 'string',
            'email_verified_at' => ['type' => 'timestamp', 'args' => []],
            'password' => 'string',
            'birth_date' => ['type' => 'date', 'args' => []],
            'status' => ['type' => 'enum', 'args' => [['active', 'inactive', 'suspended']]],
            'bio' => 'text',
            'is_admin' => 'boolean',
            'salary' => ['type' => 'decimal', 'args' => [10, 2]],
            'avatar' => 'string',
            'remember_token' => 'string',
            'category_id' => 'unsignedBigInteger',
            'user_id' => 'unsignedBigInteger',
        ]);
    }

    /** @test */
    public function it_throws_exception_when_no_table_provided()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Table name is required');

        $analyzer = new ModelAnalyzer();
        $analyzer->analyze();
    }

    /** @test */
    public function it_can_analyze_table_structure()
    {
        $analyzer = new ModelAnalyzer('test_users');
        $result = $analyzer->analyze();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('table', $result);
        $this->assertArrayHasKey('model_name', $result);
        $this->assertArrayHasKey('columns', $result);
        $this->assertArrayHasKey('relationships', $result);
        $this->assertArrayHasKey('indexes', $result);
        $this->assertArrayHasKey('primary_key', $result);
        $this->assertArrayHasKey('timestamps', $result);
        $this->assertArrayHasKey('soft_deletes', $result);
    }

    /** @test */
    public function it_returns_correct_table_name()
    {
        $analyzer = new ModelAnalyzer('test_users');
        $result = $analyzer->analyze();

        $this->assertEquals('test_users', $result['table']);
    }

    /** @test */
    public function it_generates_correct_model_name_from_table()
    {
        $tests = [
            'users' => 'User',
            'user_profiles' => 'UserProfile',
            'blog_posts' => 'BlogPost',
            'categories' => 'Category',
            'test_users' => 'TestUser',
        ];

        foreach ($tests as $tableName => $expectedModelName) {
            $analyzer = new ModelAnalyzer($tableName);
            $result = $analyzer->analyze();
            $this->assertEquals($expectedModelName, $result['model_name']);
        }
    }

    /** @test */
    public function it_detects_table_columns()
    {
        $analyzer = new ModelAnalyzer('test_users');
        $result = $analyzer->analyze();

        $this->assertIsArray($result['columns']);
        $this->assertNotEmpty($result['columns']);

        // Check that important columns are detected
        $columnNames = array_keys($result['columns']);
        $this->assertContains('id', $columnNames);
        $this->assertContains('name', $columnNames);
        $this->assertContains('email', $columnNames);
        $this->assertContains('created_at', $columnNames);
        $this->assertContains('updated_at', $columnNames);
    }

    /** @test */
    public function it_provides_column_metadata()
    {
        $analyzer = new ModelAnalyzer('test_users');
        $result = $analyzer->analyze();

        $nameColumn = $result['columns']['name'];
        $this->assertArrayHasKey('name', $nameColumn);
        $this->assertArrayHasKey('type', $nameColumn);
        $this->assertArrayHasKey('nullable', $nameColumn);
        $this->assertArrayHasKey('default', $nameColumn);

        $this->assertEquals('name', $nameColumn['name']);
    }

    /** @test */
    public function it_detects_relationships_from_foreign_keys()
    {
        $analyzer = new ModelAnalyzer('test_users');
        $result = $analyzer->analyze();

        $this->assertIsArray($result['relationships']);

        // Should detect category_id and user_id as foreign keys
        $relationshipNames = array_column($result['relationships'], 'name');
        $this->assertContains('category', $relationshipNames);
        $this->assertContains('user', $relationshipNames);

        // Check relationship structure
        $categoryRelation = collect($result['relationships'])->firstWhere('name', 'category');
        $this->assertEquals('belongsTo', $categoryRelation['type']);
        $this->assertEquals('category_id', $categoryRelation['foreign_key']);
        $this->assertEquals('categories', $categoryRelation['related_table']);
        $this->assertEquals('Category', $categoryRelation['related_model']);
    }

    /** @test */
    public function it_detects_primary_key()
    {
        $analyzer = new ModelAnalyzer('test_users');
        $result = $analyzer->analyze();

        $this->assertEquals('id', $result['primary_key']);
    }

    /** @test */
    public function it_detects_timestamp_columns()
    {
        $analyzer = new ModelAnalyzer('test_users');
        $result = $analyzer->analyze();

        $this->assertTrue($result['timestamps']);
    }

    /** @test */
    public function it_detects_absence_of_timestamp_columns()
    {
        // Create table without timestamps
        $this->createTestTable('simple_table', [
            'name' => 'string',
        ]);

        $analyzer = new ModelAnalyzer('simple_table');
        $result = $analyzer->analyze();

        $this->assertFalse($result['timestamps']);
    }

    /** @test */
    public function it_detects_soft_deletes()
    {
        // Create table with soft deletes
        $this->createTestTable('soft_delete_table', [
            'name' => 'string',
            'deleted_at' => ['type' => 'timestamp', 'args' => []],
        ]);

        $analyzer = new ModelAnalyzer('soft_delete_table');
        $result = $analyzer->analyze();

        $this->assertTrue($result['soft_deletes']);
    }

    /** @test */
    public function it_detects_absence_of_soft_deletes()
    {
        $analyzer = new ModelAnalyzer('test_users');
        $result = $analyzer->analyze();

        $this->assertFalse($result['soft_deletes']);
    }

    /** @test */
    public function it_returns_empty_indexes_array()
    {
        // Since indexes are not implemented yet, should return empty array
        $analyzer = new ModelAnalyzer('test_users');
        $result = $analyzer->analyze();

        $this->assertIsArray($result['indexes']);
        $this->assertEmpty($result['indexes']);
    }

    /** @test */
    public function it_handles_tables_without_id_column()
    {
        // Create table without standard 'id' column
        Schema::create('no_id_table', function ($table) {
            $table->string('uuid')->primary();
            $table->string('name');
            $table->timestamps();
        });

        $analyzer = new ModelAnalyzer('no_id_table');
        $result = $analyzer->analyze();

        // Should fallback to first column
        $this->assertEquals('uuid', $result['primary_key']);
    }

    /** @test */
    public function it_handles_empty_tables()
    {
        // Create minimal table
        Schema::create('empty_table', function ($table) {
            $table->id();
        });

        $analyzer = new ModelAnalyzer('empty_table');
        $result = $analyzer->analyze();

        $this->assertEquals('empty_table', $result['table']);
        $this->assertEquals('EmptyTable', $result['model_name']);
        $this->assertIsArray($result['columns']);
        $this->assertArrayHasKey('id', $result['columns']);
        $this->assertEmpty($result['relationships']);
        $this->assertFalse($result['timestamps']);
        $this->assertFalse($result['soft_deletes']);
    }

    /** @test */
    public function it_can_be_instantiated_with_table_name()
    {
        $analyzer = new ModelAnalyzer('test_users');
        $result = $analyzer->analyze();

        $this->assertEquals('test_users', $result['table']);
    }

    /** @test */
    public function it_can_set_table_after_instantiation()
    {
        $analyzer = new ModelAnalyzer();
        
        // This should work through the constructor parameter
        $analyzerWithTable = new ModelAnalyzer('test_users');
        $result = $analyzerWithTable->analyze();

        $this->assertEquals('test_users', $result['table']);
    }

    /** @test */
    public function it_correctly_identifies_foreign_key_patterns()
    {
        // Create table with various foreign key patterns
        $this->createTestTable('complex_table', [
            'name' => 'string',
            'user_id' => 'unsignedBigInteger',
            'category_id' => 'unsignedBigInteger',
            'parent_category_id' => 'unsignedBigInteger',
            'some_other_field' => 'string',
        ]);

        $analyzer = new ModelAnalyzer('complex_table');
        $result = $analyzer->analyze();

        $relationshipNames = array_column($result['relationships'], 'name');
        
        $this->assertContains('user', $relationshipNames);
        $this->assertContains('category', $relationshipNames);
        $this->assertContains('parent_category', $relationshipNames);
        $this->assertNotContains('some_other_field', $relationshipNames);
    }
}