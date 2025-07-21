<?php

namespace Wink\ViewGenerator\Tests\Unit\Analyzers;

use Wink\ViewGenerator\Tests\TestCase;
use Wink\ViewGenerator\Analyzers\ControllerAnalyzer;

class ControllerAnalyzerTest extends TestCase
{
    protected ControllerAnalyzer $analyzer;

    protected function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function it_can_be_instantiated_without_controller_class()
    {
        $analyzer = new ControllerAnalyzer();
        $this->assertInstanceOf(ControllerAnalyzer::class, $analyzer);
    }

    /** @test */
    public function it_can_be_instantiated_with_controller_class()
    {
        $analyzer = new ControllerAnalyzer('App\\Http\\Controllers\\UserController');
        $this->assertInstanceOf(ControllerAnalyzer::class, $analyzer);
    }

    /** @test */
    public function it_can_analyze_controller_structure()
    {
        $analyzer = new ControllerAnalyzer('App\\Http\\Controllers\\UserController');
        $result = $analyzer->analyze();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('class', $result);
        $this->assertArrayHasKey('methods', $result);
        $this->assertArrayHasKey('validation_rules', $result);
        $this->assertArrayHasKey('middleware', $result);
    }

    /** @test */
    public function it_returns_correct_controller_class()
    {
        $controllerClass = 'App\\Http\\Controllers\\UserController';
        $analyzer = new ControllerAnalyzer($controllerClass);
        $result = $analyzer->analyze();

        $this->assertEquals($controllerClass, $result['class']);
    }

    /** @test */
    public function it_returns_default_crud_methods()
    {
        $analyzer = new ControllerAnalyzer('App\\Http\\Controllers\\UserController');
        $result = $analyzer->analyze();

        $expectedMethods = ['index', 'show', 'create', 'store', 'edit', 'update', 'destroy'];
        
        $this->assertIsArray($result['methods']);
        $this->assertEquals($expectedMethods, $result['methods']);
    }

    /** @test */
    public function it_returns_empty_validation_rules_by_default()
    {
        $analyzer = new ControllerAnalyzer('App\\Http\\Controllers\\UserController');
        $result = $analyzer->analyze();

        $this->assertIsArray($result['validation_rules']);
        $this->assertEmpty($result['validation_rules']);
    }

    /** @test */
    public function it_returns_empty_middleware_by_default()
    {
        $analyzer = new ControllerAnalyzer('App\\Http\\Controllers\\UserController');
        $result = $analyzer->analyze();

        $this->assertIsArray($result['middleware']);
        $this->assertEmpty($result['middleware']);
    }

    /** @test */
    public function it_handles_null_controller_class()
    {
        $analyzer = new ControllerAnalyzer();
        $result = $analyzer->analyze();

        $this->assertNull($result['class']);
        $this->assertIsArray($result['methods']);
        $this->assertIsArray($result['validation_rules']);
        $this->assertIsArray($result['middleware']);
    }

    /** @test */
    public function it_handles_empty_controller_class()
    {
        $analyzer = new ControllerAnalyzer('');
        $result = $analyzer->analyze();

        $this->assertEquals('', $result['class']);
        $this->assertIsArray($result['methods']);
        $this->assertIsArray($result['validation_rules']);
        $this->assertIsArray($result['middleware']);
    }

    /** @test */
    public function it_provides_consistent_analysis_structure()
    {
        $testCases = [
            'App\\Http\\Controllers\\UserController',
            'App\\Http\\Controllers\\PostController',
            'App\\Http\\Controllers\\AdminController',
        ];

        foreach ($testCases as $controllerClass) {
            $analyzer = new ControllerAnalyzer($controllerClass);
            $result = $analyzer->analyze();

            $this->assertArrayHasKey('class', $result);
            $this->assertArrayHasKey('methods', $result);
            $this->assertArrayHasKey('validation_rules', $result);
            $this->assertArrayHasKey('middleware', $result);

            $this->assertEquals($controllerClass, $result['class']);
            $this->assertIsArray($result['methods']);
            $this->assertIsArray($result['validation_rules']);
            $this->assertIsArray($result['middleware']);
        }
    }

    /** @test */
    public function it_returns_same_methods_regardless_of_controller_class()
    {
        $analyzer1 = new ControllerAnalyzer('App\\Http\\Controllers\\UserController');
        $analyzer2 = new ControllerAnalyzer('App\\Http\\Controllers\\PostController');
        
        $result1 = $analyzer1->analyze();
        $result2 = $analyzer2->analyze();

        $this->assertEquals($result1['methods'], $result2['methods']);
    }

    /** @test */
    public function it_can_be_called_multiple_times()
    {
        $analyzer = new ControllerAnalyzer('App\\Http\\Controllers\\UserController');
        
        $result1 = $analyzer->analyze();
        $result2 = $analyzer->analyze();

        $this->assertEquals($result1, $result2);
    }

    /** @test */
    public function it_handles_various_controller_naming_conventions()
    {
        $testCases = [
            'UserController',
            'App\\Http\\Controllers\\UserController',
            'App\\Controllers\\UserController',
            'Controllers\\UserController',
            'UserApiController',
            'Admin\\UserController',
        ];

        foreach ($testCases as $controllerClass) {
            $analyzer = new ControllerAnalyzer($controllerClass);
            $result = $analyzer->analyze();

            $this->assertEquals($controllerClass, $result['class']);
            $this->assertIsArray($result['methods']);
            $this->assertNotEmpty($result['methods']);
        }
    }
}