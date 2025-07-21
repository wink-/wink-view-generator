<?php

namespace Wink\ViewGenerator\Tests\Feature\Commands;

use Wink\ViewGenerator\Tests\TestCase;
use Illuminate\Support\Facades\Artisan;

class GenerateComponentsCommandTest extends TestCase
{
    /** @test */
    public function it_can_be_executed()
    {
        $exitCode = Artisan::call('wink:views:components');
        
        $this->assertEquals(0, $exitCode);
    }

    /** @test */
    public function it_accepts_type_argument()
    {
        $types = ['form-inputs', 'data-tables', 'navigation', 'modals'];
        
        foreach ($types as $type) {
            $exitCode = Artisan::call('wink:views:components', ['type' => $type]);
            $this->assertEquals(0, $exitCode, "Failed for type: {$type}");
        }
    }

    /** @test */
    public function it_accepts_framework_option()
    {
        $exitCode = Artisan::call('wink:views:components', [
            'type' => 'form-inputs',
            '--framework' => 'bootstrap'
        ]);
        
        $this->assertEquals(0, $exitCode);
    }

    /** @test */
    public function it_accepts_force_option()
    {
        $exitCode = Artisan::call('wink:views:components', [
            'type' => 'form-inputs',
            '--force' => true
        ]);
        
        $this->assertEquals(0, $exitCode);
    }

    /** @test */
    public function it_works_with_different_frameworks()
    {
        $frameworks = ['bootstrap', 'tailwind', 'custom'];
        
        foreach ($frameworks as $framework) {
            $exitCode = Artisan::call('wink:views:components', [
                'type' => 'form-inputs',
                '--framework' => $framework
            ]);
            
            $this->assertEquals(0, $exitCode, "Failed with framework: {$framework}");
        }
    }

    /** @test */
    public function it_handles_invalid_component_type()
    {
        $exitCode = Artisan::call('wink:views:components', ['type' => 'invalid-type']);
        
        // Should handle gracefully
        $this->assertEquals(0, $exitCode);
    }

    /** @test */
    public function it_provides_help_information()
    {
        $exitCode = Artisan::call('help', ['command_name' => 'wink:views:components']);
        $output = Artisan::output();
        
        $this->assertEquals(0, $exitCode);
        $this->assertStringContainsString('Generate UI components', $output);
    }

    /** @test */
    public function it_can_generate_all_component_types()
    {
        $componentTypes = [
            'form-inputs',
            'data-tables', 
            'navigation',
            'modals',
            'layouts',
            'cards'
        ];
        
        foreach ($componentTypes as $type) {
            $exitCode = Artisan::call('wink:views:components', ['type' => $type]);
            $this->assertEquals(0, $exitCode, "Failed for component type: {$type}");
        }
    }

    /** @test */
    public function it_outputs_descriptive_message()
    {
        Artisan::call('wink:views:components', ['type' => 'form-inputs']);
        $output = Artisan::output();
        
        $this->assertStringContainsString('Wink View Generator', $output);
    }
}