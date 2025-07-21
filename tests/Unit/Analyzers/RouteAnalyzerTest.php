<?php

namespace Wink\ViewGenerator\Tests\Unit\Analyzers;

use Wink\ViewGenerator\Tests\TestCase;
use Wink\ViewGenerator\Analyzers\RouteAnalyzer;

class RouteAnalyzerTest extends TestCase
{
    protected RouteAnalyzer $analyzer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->analyzer = new RouteAnalyzer();
    }

    /** @test */
    public function it_can_be_instantiated()
    {
        $analyzer = new RouteAnalyzer();
        $this->assertInstanceOf(RouteAnalyzer::class, $analyzer);
    }

    /** @test */
    public function it_can_analyze_route_structure()
    {
        $result = $this->analyzer->analyze();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('routes', $result);
        $this->assertArrayHasKey('resource_routes', $result);
        $this->assertArrayHasKey('middleware', $result);
    }

    /** @test */
    public function it_returns_empty_routes_by_default()
    {
        $result = $this->analyzer->analyze();

        $this->assertIsArray($result['routes']);
        $this->assertEmpty($result['routes']);
    }

    /** @test */
    public function it_returns_empty_resource_routes_by_default()
    {
        $result = $this->analyzer->analyze();

        $this->assertIsArray($result['resource_routes']);
        $this->assertEmpty($result['resource_routes']);
    }

    /** @test */
    public function it_returns_empty_middleware_by_default()
    {
        $result = $this->analyzer->analyze();

        $this->assertIsArray($result['middleware']);
        $this->assertEmpty($result['middleware']);
    }

    /** @test */
    public function it_provides_consistent_analysis_structure()
    {
        $result1 = $this->analyzer->analyze();
        $result2 = $this->analyzer->analyze();

        $this->assertEquals($result1, $result2);
        
        // Verify structure consistency
        $this->assertArrayHasKey('routes', $result1);
        $this->assertArrayHasKey('resource_routes', $result1);
        $this->assertArrayHasKey('middleware', $result1);
        
        $this->assertIsArray($result1['routes']);
        $this->assertIsArray($result1['resource_routes']);
        $this->assertIsArray($result1['middleware']);
    }

    /** @test */
    public function it_can_be_called_multiple_times()
    {
        $result1 = $this->analyzer->analyze();
        $result2 = $this->analyzer->analyze();
        $result3 = $this->analyzer->analyze();

        $this->assertEquals($result1, $result2);
        $this->assertEquals($result2, $result3);
    }

    /** @test */
    public function it_returns_proper_data_types()
    {
        $result = $this->analyzer->analyze();

        $this->assertIsArray($result);
        $this->assertIsArray($result['routes']);
        $this->assertIsArray($result['resource_routes']);
        $this->assertIsArray($result['middleware']);
    }

    /** @test */
    public function it_handles_multiple_analyzer_instances()
    {
        $analyzer1 = new RouteAnalyzer();
        $analyzer2 = new RouteAnalyzer();
        
        $result1 = $analyzer1->analyze();
        $result2 = $analyzer2->analyze();

        $this->assertEquals($result1, $result2);
    }

    /** @test */
    public function it_provides_stable_empty_results()
    {
        $expectedStructure = [
            'routes' => [],
            'resource_routes' => [],
            'middleware' => [],
        ];

        $result = $this->analyzer->analyze();
        $this->assertEquals($expectedStructure, $result);
    }

    /** @test */
    public function analysis_result_has_correct_keys()
    {
        $result = $this->analyzer->analyze();
        $expectedKeys = ['routes', 'resource_routes', 'middleware'];

        foreach ($expectedKeys as $key) {
            $this->assertArrayHasKey($key, $result, "Missing key: {$key}");
        }

        // Ensure no extra keys
        $this->assertCount(count($expectedKeys), $result, 'Analysis result has unexpected keys');
    }

    /** @test */
    public function it_maintains_immutable_empty_state()
    {
        $result1 = $this->analyzer->analyze();
        
        // Modify the result (shouldn't affect subsequent calls)
        $result1['routes'] = ['modified'];
        
        $result2 = $this->analyzer->analyze();
        
        // Should still be empty
        $this->assertEmpty($result2['routes']);
        $this->assertEmpty($result2['resource_routes']);
        $this->assertEmpty($result2['middleware']);
    }

    /** @test */
    public function all_result_arrays_are_empty_by_default()
    {
        $result = $this->analyzer->analyze();

        foreach ($result as $key => $value) {
            $this->assertIsArray($value, "Value for key '{$key}' should be an array");
            $this->assertEmpty($value, "Array for key '{$key}' should be empty");
        }
    }
}