<?php

namespace Wink\ViewGenerator\Tests\Unit\Analyzers;

use Wink\ViewGenerator\Tests\TestCase;
use Wink\ViewGenerator\Analyzers\FieldAnalyzer;

class FieldAnalyzerTest extends TestCase
{
    protected FieldAnalyzer $analyzer;
    protected array $sampleColumns;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sampleColumns = [
            [
                'name' => 'id',
                'type' => 'integer',
                'nullable' => false,
                'default' => null,
            ],
            [
                'name' => 'name',
                'type' => 'string',
                'nullable' => false,
                'default' => null,
            ],
            [
                'name' => 'email',
                'type' => 'string',
                'nullable' => false,
                'default' => null,
            ],
            [
                'name' => 'password',
                'type' => 'string',
                'nullable' => false,
                'default' => null,
            ],
            [
                'name' => 'birth_date',
                'type' => 'date',
                'nullable' => true,
                'default' => null,
            ],
            [
                'name' => 'bio',
                'type' => 'text',
                'nullable' => true,
                'default' => null,
            ],
            [
                'name' => 'is_admin',
                'type' => 'boolean',
                'nullable' => false,
                'default' => false,
            ],
            [
                'name' => 'salary',
                'type' => 'decimal',
                'nullable' => true,
                'default' => null,
            ],
            [
                'name' => 'avatar',
                'type' => 'string',
                'nullable' => true,
                'default' => null,
            ],
            [
                'name' => 'status',
                'type' => 'string',
                'nullable' => false,
                'default' => 'active',
            ],
            [
                'name' => 'created_at',
                'type' => 'timestamp',
                'nullable' => true,
                'default' => null,
            ],
            [
                'name' => 'updated_at',
                'type' => 'timestamp',
                'nullable' => true,
                'default' => null,
            ],
            [
                'name' => 'remember_token',
                'type' => 'string',
                'nullable' => true,
                'default' => null,
            ],
        ];

        $this->analyzer = new FieldAnalyzer($this->sampleColumns);
    }

    /** @test */
    public function it_can_analyze_fields_for_forms()
    {
        $result = $this->analyzer->analyzeForForms();

        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
        
        // Check that system columns are excluded
        $fieldNames = array_column($result, 'name');
        $this->assertNotContains('id', $fieldNames);
        $this->assertNotContains('created_at', $fieldNames);
        $this->assertNotContains('updated_at', $fieldNames);
        $this->assertNotContains('password', $fieldNames);
        $this->assertNotContains('remember_token', $fieldNames);
        
        // Check that regular fields are included
        $this->assertContains('name', $fieldNames);
        $this->assertContains('email', $fieldNames);
        $this->assertContains('bio', $fieldNames);
    }

    /** @test */
    public function it_can_analyze_fields_for_tables()
    {
        $result = $this->analyzer->analyzeForTables();

        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
        
        // Check that hidden columns are excluded
        $fieldNames = array_column($result, 'name');
        $this->assertNotContains('password', $fieldNames);
        $this->assertNotContains('remember_token', $fieldNames);
        
        // Check that display fields are included
        $this->assertContains('name', $fieldNames);
        $this->assertContains('email', $fieldNames);
        $this->assertContains('status', $fieldNames);
        $this->assertContains('is_admin', $fieldNames);
    }

    /** @test */
    public function it_generates_correct_form_field_structure()
    {
        $result = $this->analyzer->analyzeForForms();
        $nameField = collect($result)->firstWhere('name', 'name');

        $this->assertArrayHasKey('name', $nameField);
        $this->assertArrayHasKey('label', $nameField);
        $this->assertArrayHasKey('input_type', $nameField);
        $this->assertArrayHasKey('required', $nameField);
        $this->assertArrayHasKey('validation', $nameField);

        $this->assertEquals('name', $nameField['name']);
        $this->assertEquals('Name', $nameField['label']);
        $this->assertEquals('text', $nameField['input_type']);
        $this->assertTrue($nameField['required']);
        $this->assertIsArray($nameField['validation']);
    }

    /** @test */
    public function it_generates_correct_table_field_structure()
    {
        $result = $this->analyzer->analyzeForTables();
        $nameField = collect($result)->firstWhere('name', 'name');

        $this->assertArrayHasKey('name', $nameField);
        $this->assertArrayHasKey('label', $nameField);
        $this->assertArrayHasKey('display_type', $nameField);
        $this->assertArrayHasKey('sortable', $nameField);
        $this->assertArrayHasKey('filterable', $nameField);

        $this->assertEquals('name', $nameField['name']);
        $this->assertEquals('Name', $nameField['label']);
        $this->assertEquals('text', $nameField['display_type']);
        $this->assertTrue($nameField['sortable']);
    }

    /** @test */
    public function it_generates_proper_labels_from_column_names()
    {
        $testCases = [
            'name' => 'Name',
            'first_name' => 'First Name',
            'last_name' => 'Last Name',
            'email_address' => 'Email Address',
            'birth_date' => 'Birth Date',
            'is_admin' => 'Is Admin',
            'user_profile_id' => 'User Profile Id',
        ];

        foreach ($testCases as $columnName => $expectedLabel) {
            $columns = [['name' => $columnName, 'type' => 'string', 'nullable' => true]];
            $analyzer = new FieldAnalyzer($columns);
            $result = $analyzer->analyzeForForms();
            
            $this->assertEquals($expectedLabel, $result[0]['label']);
        }
    }

    /** @test */
    public function it_determines_correct_input_types_by_name()
    {
        $testCases = [
            'email' => 'email',
            'user_email' => 'email',
            'password' => 'password',
            'confirm_password' => 'password',
            'phone' => 'tel',
            'phone_number' => 'tel',
            'website_url' => 'url',
            'home_url' => 'url',
            'description' => 'textarea',
            'bio_content' => 'textarea',
            'avatar' => 'file',
            'profile_image' => 'file',
            'profile_photo' => 'file',
        ];

        foreach ($testCases as $columnName => $expectedType) {
            $columns = [['name' => $columnName, 'type' => 'string', 'nullable' => true]];
            $analyzer = new FieldAnalyzer($columns);
            $result = $analyzer->analyzeForForms();
            
            $this->assertEquals($expectedType, $result[0]['input_type'], "Failed for column: {$columnName}");
        }
    }

    /** @test */
    public function it_determines_correct_input_types_by_database_type()
    {
        $testCases = [
            'boolean' => 'checkbox',
            'date' => 'date',
            'datetime' => 'datetime-local',
            'timestamp' => 'datetime-local',
            'time' => 'time',
            'integer' => 'number',
            'bigint' => 'number',
            'smallint' => 'number',
            'decimal' => 'number',
            'float' => 'number',
            'double' => 'number',
            'text' => 'textarea',
            'longtext' => 'textarea',
            'string' => 'text',
        ];

        foreach ($testCases as $dbType => $expectedInputType) {
            $columns = [['name' => 'test_field', 'type' => $dbType, 'nullable' => true]];
            $analyzer = new FieldAnalyzer($columns);
            $result = $analyzer->analyzeForForms();
            
            $this->assertEquals($expectedInputType, $result[0]['input_type'], "Failed for type: {$dbType}");
        }
    }

    /** @test */
    public function it_determines_correct_display_types_for_tables()
    {
        $testCases = [
            ['name' => 'status', 'type' => 'string', 'expected' => 'badge'],
            ['name' => 'is_active', 'type' => 'boolean', 'expected' => 'boolean'],
            ['name' => 'enabled', 'type' => 'boolean', 'expected' => 'boolean'],
            ['name' => 'avatar', 'type' => 'string', 'expected' => 'image'],
            ['name' => 'profile_image', 'type' => 'string', 'expected' => 'image'],
            ['name' => 'email', 'type' => 'string', 'expected' => 'email'],
            ['name' => 'website_url', 'type' => 'string', 'expected' => 'link'],
            ['name' => 'home_url', 'type' => 'string', 'expected' => 'link'],
            ['name' => 'created_at', 'type' => 'timestamp', 'expected' => 'date'],
            ['name' => 'birth_date', 'type' => 'date', 'expected' => 'date'],
            ['name' => 'price', 'type' => 'decimal', 'expected' => 'currency'],
            ['name' => 'amount', 'type' => 'float', 'expected' => 'currency'],
            ['name' => 'regular_field', 'type' => 'string', 'expected' => 'text'],
        ];

        foreach ($testCases as $case) {
            $columns = [$case];
            $analyzer = new FieldAnalyzer($columns);
            $result = $analyzer->analyzeForTables();
            
            if (!empty($result)) {
                $this->assertEquals($case['expected'], $result[0]['display_type'], 
                    "Failed for {$case['name']} with type {$case['type']}");
            }
        }
    }

    /** @test */
    public function it_correctly_identifies_required_fields()
    {
        $requiredField = ['name' => 'name', 'type' => 'string', 'nullable' => false, 'default' => null];
        $optionalField = ['name' => 'bio', 'type' => 'text', 'nullable' => true, 'default' => null];
        $defaultField = ['name' => 'status', 'type' => 'string', 'nullable' => false, 'default' => 'active'];

        $analyzer = new FieldAnalyzer([$requiredField, $optionalField, $defaultField]);
        $result = $analyzer->analyzeForForms();

        $nameField = collect($result)->firstWhere('name', 'name');
        $bioField = collect($result)->firstWhere('name', 'bio');
        $statusField = collect($result)->firstWhere('name', 'status');

        $this->assertTrue($nameField['required']);
        $this->assertFalse($bioField['required']);
        $this->assertFalse($statusField['required']); // Has default value
    }

    /** @test */
    public function it_correctly_identifies_sortable_fields()
    {
        $sortableField = ['name' => 'name', 'type' => 'string'];
        $nonSortableField = ['name' => 'bio', 'type' => 'text'];
        $jsonField = ['name' => 'metadata', 'type' => 'json'];

        $analyzer = new FieldAnalyzer([$sortableField, $nonSortableField, $jsonField]);
        $result = $analyzer->analyzeForTables();

        $nameField = collect($result)->firstWhere('name', 'name');
        $bioField = collect($result)->firstWhere('name', 'bio');
        $metadataField = collect($result)->firstWhere('name', 'metadata');

        $this->assertTrue($nameField['sortable']);
        $this->assertFalse($bioField['sortable']);
        $this->assertFalse($metadataField['sortable']);
    }

    /** @test */
    public function it_correctly_identifies_filterable_fields()
    {
        $statusField = ['name' => 'status', 'type' => 'string'];
        $categoryField = ['name' => 'category_type', 'type' => 'string'];
        $booleanField = ['name' => 'is_active', 'type' => 'boolean'];
        $regularField = ['name' => 'name', 'type' => 'string'];

        $analyzer = new FieldAnalyzer([$statusField, $categoryField, $booleanField, $regularField]);
        $result = $analyzer->analyzeForTables();

        $statusResult = collect($result)->firstWhere('name', 'status');
        $categoryResult = collect($result)->firstWhere('name', 'category_type');
        $booleanResult = collect($result)->firstWhere('name', 'is_active');
        $regularResult = collect($result)->firstWhere('name', 'name');

        $this->assertTrue($statusResult['filterable']);
        $this->assertTrue($categoryResult['filterable']);
        $this->assertTrue($booleanResult['filterable']);
        $this->assertFalse($regularResult['filterable']);
    }

    /** @test */
    public function it_generates_proper_validation_rules()
    {
        $result = $this->analyzer->analyzeForForms();

        // Test string field validation
        $nameField = collect($result)->firstWhere('name', 'name');
        $this->assertContains('required', $nameField['validation']);
        $this->assertContains('string', $nameField['validation']);
        $this->assertContains('max:255', $nameField['validation']);

        // Test email field validation
        $emailField = collect($result)->firstWhere('name', 'email');
        $this->assertContains('email', $emailField['validation']);

        // Test boolean field validation
        $adminField = collect($result)->firstWhere('name', 'is_admin');
        $this->assertContains('boolean', $adminField['validation']);

        // Test decimal field validation
        $salaryField = collect($result)->firstWhere('name', 'salary');
        $this->assertContains('numeric', $salaryField['validation']);

        // Test date field validation
        $dateField = collect($result)->firstWhere('name', 'birth_date');
        $this->assertContains('date', $dateField['validation']);
    }

    /** @test */
    public function it_handles_empty_columns_array()
    {
        $analyzer = new FieldAnalyzer([]);
        
        $formResult = $analyzer->analyzeForForms();
        $tableResult = $analyzer->analyzeForTables();

        $this->assertIsArray($formResult);
        $this->assertEmpty($formResult);
        $this->assertIsArray($tableResult);
        $this->assertEmpty($tableResult);
    }

    /** @test */
    public function it_excludes_system_columns_from_forms()
    {
        $systemColumns = ['id', 'created_at', 'updated_at', 'deleted_at', 'password', 'remember_token'];
        
        $result = $this->analyzer->analyzeForForms();
        $fieldNames = array_column($result, 'name');

        foreach ($systemColumns as $systemColumn) {
            $this->assertNotContains($systemColumn, $fieldNames);
        }
    }

    /** @test */
    public function it_excludes_hidden_columns_from_tables()
    {
        $hiddenColumns = ['password', 'remember_token', 'email_verified_at'];
        
        $result = $this->analyzer->analyzeForTables();
        $fieldNames = array_column($result, 'name');

        foreach ($hiddenColumns as $hiddenColumn) {
            $this->assertNotContains($hiddenColumn, $fieldNames);
        }
    }

    /** @test */
    public function it_can_be_instantiated_with_empty_constructor()
    {
        $analyzer = new FieldAnalyzer();
        
        $formResult = $analyzer->analyzeForForms();
        $tableResult = $analyzer->analyzeForTables();

        $this->assertIsArray($formResult);
        $this->assertEmpty($formResult);
        $this->assertIsArray($tableResult);
        $this->assertEmpty($tableResult);
    }
}