<?php

namespace Wink\ViewGenerator\Tests\Feature\Commands;

use Wink\ViewGenerator\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;

class GenerateAllCommandTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test tables for comprehensive generation
        $this->createTestTable('test_users', [
            'name' => 'string',
            'email' => 'string',
            'password' => 'string',
        ]);
        
        $this->createTestTable('test_posts', [
            'title' => 'string',
            'content' => 'text',
            'user_id' => 'unsignedBigInteger',
        ]);
    }

    /** @test */
    public function it_can_be_executed()
    {
        $exitCode = Artisan::call('wink:views:all');
        
        $this->assertEquals(0, $exitCode);
    }

    /** @test */
    public function it_accepts_tables_option()
    {
        $exitCode = Artisan::call('wink:views:all', [
            '--tables' => 'test_users,test_posts'
        ]);
        
        $this->assertEquals(0, $exitCode);
    }

    /** @test */
    public function it_accepts_framework_option()
    {
        $exitCode = Artisan::call('wink:views:all', [
            '--framework' => 'bootstrap'
        ]);
        
        $this->assertEquals(0, $exitCode);
    }

    /** @test */
    public function it_accepts_components_option()
    {
        $exitCode = Artisan::call('wink:views:all', [
            '--components' => true
        ]);
        
        $this->assertEquals(0, $exitCode);
    }

    /** @test */
    public function it_accepts_layouts_option()
    {
        $exitCode = Artisan::call('wink:views:all', [
            '--layouts' => true
        ]);
        
        $this->assertEquals(0, $exitCode);
    }

    /** @test */
    public function it_accepts_force_option()
    {
        $exitCode = Artisan::call('wink:views:all', [
            '--force' => true
        ]);
        
        $this->assertEquals(0, $exitCode);
    }

    /** @test */
    public function it_accepts_all_options_together()
    {
        $exitCode = Artisan::call('wink:views:all', [
            '--tables' => 'test_users',
            '--framework' => 'tailwind',
            '--components' => true,
            '--layouts' => true,
            '--force' => true
        ]);
        
        $this->assertEquals(0, $exitCode);
    }

    /** @test */
    public function it_works_with_different_frameworks()
    {
        $frameworks = ['bootstrap', 'tailwind', 'custom'];
        
        foreach ($frameworks as $framework) {
            $exitCode = Artisan::call('wink:views:all', [
                '--framework' => $framework
            ]);
            
            $this->assertEquals(0, $exitCode, "Failed with framework: {$framework}");
        }
    }

    /** @test */
    public function it_handles_single_table()
    {
        $exitCode = Artisan::call('wink:views:all', [
            '--tables' => 'test_users'
        ]);
        
        $this->assertEquals(0, $exitCode);
    }

    /** @test */
    public function it_handles_multiple_tables()
    {
        $exitCode = Artisan::call('wink:views:all', [
            '--tables' => 'test_users,test_posts'
        ]);
        
        $this->assertEquals(0, $exitCode);
    }

    /** @test */
    public function it_handles_non_existent_tables()
    {
        $exitCode = Artisan::call('wink:views:all', [
            '--tables' => 'non_existent_table'
        ]);
        
        // Should handle gracefully
        $this->assertEquals(0, $exitCode);
    }

    /** @test */
    public function it_provides_help_information()
    {
        $exitCode = Artisan::call('help', ['command_name' => 'wink:views:all']);
        $output = Artisan::output();
        
        $this->assertEquals(0, $exitCode);
        $this->assertStringContainsString('Generate all views', $output);
        $this->assertStringContainsString('tables', $output);
        $this->assertStringContainsString('framework', $output);
        $this->assertStringContainsString('components', $output);
        $this->assertStringContainsString('layouts', $output);
        $this->assertStringContainsString('force', $output);
    }

    /** @test */
    public function it_outputs_descriptive_message()
    {
        Artisan::call('wink:views:all');
        $output = Artisan::output();
        
        $this->assertStringContainsString('Wink View Generator', $output);
    }

    /** @test */
    public function it_can_be_run_without_options()
    {
        $exitCode = Artisan::call('wink:views:all');
        
        $this->assertEquals(0, $exitCode);
    }

    /** @test */
    public function it_handles_empty_tables_option()
    {
        $exitCode = Artisan::call('wink:views:all', [
            '--tables' => ''
        ]);
        
        $this->assertEquals(0, $exitCode);
    }

    /** @test */
    public function it_maintains_command_signature()
    {
        $command = Artisan::resolve('wink:views:all');
        
        $this->assertEquals('wink:views:all', $command->getName());
        
        // Check that options exist
        $definition = $command->getDefinition();
        $this->assertTrue($definition->hasOption('tables'));
        $this->assertTrue($definition->hasOption('framework'));
        $this->assertTrue($definition->hasOption('components'));
        $this->assertTrue($definition->hasOption('layouts'));
        $this->assertTrue($definition->hasOption('force'));
    }

    /** @test */
    public function it_handles_comprehensive_generation_request()
    {
        $exitCode = Artisan::call('wink:views:all', [
            '--tables' => 'test_users,test_posts',
            '--framework' => 'bootstrap',
            '--components' => true,
            '--layouts' => true,
            '--force' => true
        ]);
        
        $this->assertEquals(0, $exitCode);
        
        $output = Artisan::output();
        $this->assertNotEmpty($output);
    }
}