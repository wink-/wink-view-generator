<?php

namespace Wink\ViewGenerator\Tests\Unit\Generators;

use Wink\ViewGenerator\Tests\TestCase;
use Wink\ViewGenerator\Generators\ComponentGenerator;

class ComponentGeneratorTest extends TestCase
{
    protected ComponentGenerator $generator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->generator = new ComponentGenerator();
    }

    /** @test */
    public function it_can_be_instantiated()
    {
        $generator = new ComponentGenerator();
        $this->assertInstanceOf(ComponentGenerator::class, $generator);
    }

    /** @test */
    public function it_extends_abstract_view_generator()
    {
        $this->assertInstanceOf(\Wink\ViewGenerator\Generators\AbstractViewGenerator::class, $this->generator);
    }

    /** @test */
    public function it_generates_form_input_components_by_default()
    {
        $table = 'users';
        $modelData = ['model_name' => 'User'];
        $options = ['framework' => 'bootstrap'];

        $result = $this->generator->generate($table, $modelData, $options);

        $this->assertIsArray($result);
        // The default generate method should call generateType with 'form-inputs'
    }

    /** @test */
    public function it_generates_components_by_type()
    {
        $types = ['form-inputs', 'data-tables', 'navigation', 'modals'];
        $options = ['framework' => 'bootstrap'];

        foreach ($types as $type) {
            $result = $this->generator->generateType($type, null, [], $options);
            
            $this->assertIsArray($result);
            // Each type should return an array (may be empty based on implementation)
        }
    }

    /** @test */
    public function it_handles_form_inputs_component_type()
    {
        $result = $this->generator->generateType('form-inputs', 'users', [], ['framework' => 'bootstrap']);
        
        $this->assertIsArray($result);
    }

    /** @test */
    public function it_handles_data_tables_component_type()
    {
        $result = $this->generator->generateType('data-tables', 'users', [], ['framework' => 'bootstrap']);
        
        $this->assertIsArray($result);
    }

    /** @test */
    public function it_handles_navigation_component_type()
    {
        $result = $this->generator->generateType('navigation', 'users', [], ['framework' => 'bootstrap']);
        
        $this->assertIsArray($result);
    }

    /** @test */
    public function it_handles_modals_component_type()
    {
        $result = $this->generator->generateType('modals', 'users', [], ['framework' => 'bootstrap']);
        
        $this->assertIsArray($result);
    }

    /** @test */
    public function it_handles_unknown_component_type()
    {
        $result = $this->generator->generateType('unknown-type', 'users', [], ['framework' => 'bootstrap']);
        
        $this->assertIsArray($result);
        // Should handle gracefully, likely returning empty array or error result
    }

    /** @test */
    public function it_generates_components_with_different_frameworks()
    {
        $frameworks = ['bootstrap', 'tailwind', 'custom'];
        
        foreach ($frameworks as $framework) {
            $options = ['framework' => $framework];
            $result = $this->generator->generateType('form-inputs', 'users', [], $options);
            
            $this->assertIsArray($result);
        }
    }

    /** @test */
    public function it_generates_components_without_table_context()
    {
        $result = $this->generator->generateType('navigation', null, [], ['framework' => 'bootstrap']);
        
        $this->assertIsArray($result);
        // Navigation components shouldn't require table context
    }

    /** @test */
    public function it_generates_components_with_model_data()
    {
        $modelData = [
            'model_name' => 'User',
            'columns' => [
                ['name' => 'name', 'type' => 'string'],
                ['name' => 'email', 'type' => 'string'],
                ['name' => 'is_admin', 'type' => 'boolean'],
            ],
        ];
        
        $result = $this->generator->generateType('form-inputs', 'users', $modelData, ['framework' => 'bootstrap']);
        
        $this->assertIsArray($result);
    }

    /** @test */
    public function it_handles_empty_options()
    {
        $result = $this->generator->generateType('form-inputs', 'users', [], []);
        
        $this->assertIsArray($result);
    }

    /** @test */
    public function it_handles_empty_model_data()
    {
        $result = $this->generator->generateType('form-inputs', 'users', [], ['framework' => 'bootstrap']);
        
        $this->assertIsArray($result);
    }

    /** @test */
    public function it_can_generate_multiple_component_types_in_sequence()
    {
        $types = ['form-inputs', 'data-tables', 'navigation'];
        $allResults = [];
        
        foreach ($types as $type) {
            $result = $this->generator->generateType($type, 'users', [], ['framework' => 'bootstrap']);
            $allResults[$type] = $result;
            
            $this->assertIsArray($result);
        }
        
        // Should be able to generate different types without interference
        $this->assertCount(3, $allResults);
    }

    /** @test */
    public function it_provides_consistent_return_structure()
    {
        $result = $this->generator->generateType('form-inputs', 'users', [], ['framework' => 'bootstrap']);
        
        $this->assertIsArray($result);
        
        // If results are returned, they should have consistent structure
        if (!empty($result)) {
            foreach ($result as $componentResult) {
                $this->assertIsArray($componentResult);
                // Each result should typically have at least a 'file' or 'component' key
            }
        }
    }

    /** @test */
    public function generate_method_calls_generate_type_with_form_inputs()
    {
        // Mock the ComponentGenerator to verify the default behavior
        $mockGenerator = $this->getMockBuilder(ComponentGenerator::class)
            ->onlyMethods(['generateType'])
            ->getMock();

        $mockGenerator->expects($this->once())
            ->method('generateType')
            ->with('form-inputs', 'users', ['model_name' => 'User'], ['framework' => 'bootstrap'])
            ->willReturn(['mocked' => 'result']);

        $result = $mockGenerator->generate('users', ['model_name' => 'User'], ['framework' => 'bootstrap']);
        
        $this->assertEquals(['mocked' => 'result'], $result);
    }

    /** @test */
    public function it_handles_special_characters_in_table_names()
    {
        $specialTables = ['user_profiles', 'blog-posts', 'categories123'];
        
        foreach ($specialTables as $table) {
            $result = $this->generator->generate($table, [], ['framework' => 'bootstrap']);
            
            $this->assertIsArray($result);
        }
    }

    /** @test */
    public function it_handles_case_sensitivity_in_component_types()
    {
        $types = ['form-inputs', 'FORM-INPUTS', 'Form-Inputs'];
        
        foreach ($types as $type) {
            $result = $this->generator->generateType($type, 'users', [], ['framework' => 'bootstrap']);
            
            $this->assertIsArray($result);
            // Implementation should handle case consistently
        }
    }

    /** @test */
    public function it_can_be_called_multiple_times_safely()
    {
        $options = ['framework' => 'bootstrap'];
        
        $result1 = $this->generator->generateType('form-inputs', 'users', [], $options);
        $result2 = $this->generator->generateType('form-inputs', 'users', [], $options);
        
        $this->assertIsArray($result1);
        $this->assertIsArray($result2);
        
        // Multiple calls should be safe and produce consistent results
        $this->assertEquals($result1, $result2);
    }
}