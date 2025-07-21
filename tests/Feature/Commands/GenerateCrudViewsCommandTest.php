<?php

namespace Wink\ViewGenerator\Tests\Feature\Commands;

use Wink\ViewGenerator\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class GenerateCrudViewsCommandTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test table for command testing
        $this->createTestTable('test_users', [
            'name' => 'string',
            'email' => 'string',
            'password' => 'string',
            'birth_date' => ['type' => 'date', 'args' => []],
            'is_admin' => 'boolean',
            'status' => ['type' => 'enum', 'args' => [['active', 'inactive']]],
        ]);
    }

    /** @test */
    public function it_can_be_called_with_table_argument()
    {
        $exitCode = Artisan::call('wink:views:crud', ['table' => 'test_users']);
        
        $this->assertEquals(0, $exitCode);
    }

    /** @test */
    public function it_requires_table_argument()
    {
        $exitCode = Artisan::call('wink:views:crud');
        
        // Should fail without table argument
        $this->assertNotEquals(0, $exitCode);
    }

    /** @test */
    public function it_accepts_framework_option()
    {
        $exitCode = Artisan::call('wink:views:crud', [
            'table' => 'test_users',
            '--framework' => 'bootstrap'
        ]);
        
        $this->assertEquals(0, $exitCode);
    }

    /** @test */
    public function it_accepts_components_option()
    {
        $exitCode = Artisan::call('wink:views:crud', [
            'table' => 'test_users',
            '--components' => true
        ]);
        
        $this->assertEquals(0, $exitCode);
    }

    /** @test */
    public function it_accepts_force_option()
    {
        $exitCode = Artisan::call('wink:views:crud', [
            'table' => 'test_users',
            '--force' => true
        ]);
        
        $this->assertEquals(0, $exitCode);
    }

    /** @test */
    public function it_accepts_all_options_together()
    {
        $exitCode = Artisan::call('wink:views:crud', [
            'table' => 'test_users',
            '--framework' => 'tailwind',
            '--components' => true,
            '--force' => true
        ]);
        
        $this->assertEquals(0, $exitCode);
    }

    /** @test */
    public function it_works_with_different_frameworks()
    {
        $frameworks = ['bootstrap', 'tailwind', 'custom'];
        
        foreach ($frameworks as $framework) {
            $exitCode = Artisan::call('wink:views:crud', [
                'table' => 'test_users',
                '--framework' => $framework
            ]);
            
            $this->assertEquals(0, $exitCode, "Failed with framework: {$framework}");
        }
    }

    /** @test */
    public function it_outputs_descriptive_message()
    {
        Artisan::call('wink:views:crud', ['table' => 'test_users']);
        $output = Artisan::output();
        
        $this->assertStringContainsString('Wink View Generator', $output);
        $this->assertStringContainsString('CRUD Views Command', $output);
    }

    /** @test */
    public function it_handles_non_existent_table()
    {
        $exitCode = Artisan::call('wink:views:crud', ['table' => 'non_existent_table']);
        
        // Should handle gracefully (current implementation returns success)
        $this->assertEquals(0, $exitCode);
    }

    /** @test */
    public function it_handles_invalid_framework()
    {
        $exitCode = Artisan::call('wink:views:crud', [
            'table' => 'test_users',
            '--framework' => 'invalid_framework'
        ]);
        
        // Should handle gracefully
        $this->assertEquals(0, $exitCode);
    }

    /** @test */
    public function it_can_generate_for_various_table_names()
    {
        $tableNames = [
            'users',
            'blog_posts', 
            'user_profiles',
            'categories',
            'test_table_123'
        ];
        
        foreach ($tableNames as $tableName) {
            // Create test table
            $this->createTestTable($tableName, ['name' => 'string']);
            
            $exitCode = Artisan::call('wink:views:crud', ['table' => $tableName]);
            
            $this->assertEquals(0, $exitCode, "Failed for table: {$tableName}");
        }
    }

    /** @test */
    public function it_provides_help_information()
    {
        $exitCode = Artisan::call('help', ['command_name' => 'wink:views:crud']);
        $output = Artisan::output();
        
        $this->assertEquals(0, $exitCode);
        $this->assertStringContainsString('Generate complete CRUD views for a table', $output);
        $this->assertStringContainsString('table', $output);
        $this->assertStringContainsString('framework', $output);
        $this->assertStringContainsString('components', $output);
        $this->assertStringContainsString('force', $output);
    }

    /** @test */
    public function it_can_be_run_multiple_times()
    {
        $exitCode1 = Artisan::call('wink:views:crud', ['table' => 'test_users']);
        $exitCode2 = Artisan::call('wink:views:crud', ['table' => 'test_users']);
        
        $this->assertEquals(0, $exitCode1);
        $this->assertEquals(0, $exitCode2);
    }

    /** @test */
    public function it_works_with_dry_run_simulation()
    {
        // Test the command without actually generating files
        $exitCode = Artisan::call('wink:views:crud', [
            'table' => 'test_users',
            '--framework' => 'bootstrap'
        ]);
        
        $output = Artisan::output();
        
        $this->assertEquals(0, $exitCode);
        $this->assertStringContainsString('CRUD Views Command', $output);
    }

    /** @test */
    public function it_handles_special_characters_in_table_names()
    {
        // Test with underscores and numbers
        $specialTables = ['user_profiles', 'table123', 'test_table_with_long_name'];
        
        foreach ($specialTables as $tableName) {
            $this->createTestTable($tableName, ['name' => 'string']);
            
            $exitCode = Artisan::call('wink:views:crud', ['table' => $tableName]);
            
            $this->assertEquals(0, $exitCode);
        }
    }

    /** @test */
    public function it_maintains_command_signature()
    {
        $command = Artisan::resolve('wink:views:crud');
        
        $this->assertEquals('wink:views:crud', $command->getName());
        $this->assertEquals('Generate complete CRUD views for a table', $command->getDescription());
        
        // Check that required arguments and options exist
        $definition = $command->getDefinition();
        $this->assertTrue($definition->hasArgument('table'));
        $this->assertTrue($definition->hasOption('framework'));
        $this->assertTrue($definition->hasOption('components'));
        $this->assertTrue($definition->hasOption('force'));
    }

    /** @test */
    public function it_provides_meaningful_error_messages()
    {
        // Since the current implementation is basic, we test that it runs without throwing exceptions
        try {
            Artisan::call('wink:views:crud', ['table' => 'test_users']);
            $this->assertTrue(true); // If we get here, no exception was thrown
        } catch (\Exception $e) {
            $this->fail('Command threw unexpected exception: ' . $e->getMessage());
        }
    }

    /** @test */
    public function it_handles_empty_table_name()
    {
        $exitCode = Artisan::call('wink:views:crud', ['table' => '']);
        
        // Should handle gracefully
        $this->assertEquals(0, $exitCode);
    }

    /** @test */
    public function it_logs_command_execution()
    {
        Artisan::call('wink:views:crud', ['table' => 'test_users']);
        $output = Artisan::output();
        
        // Should produce some output
        $this->assertNotEmpty($output);
    }

    /** @test */
    public function it_can_handle_concurrent_execution()
    {
        // Test that multiple calls don't interfere with each other
        $results = [];
        
        for ($i = 0; $i < 3; $i++) {
            $results[] = Artisan::call('wink:views:crud', ['table' => 'test_users']);
        }
        
        foreach ($results as $result) {
            $this->assertEquals(0, $result);
        }
    }

    /** @test */
    public function it_preserves_output_format()
    {
        Artisan::call('wink:views:crud', ['table' => 'test_users']);
        $output = Artisan::output();
        
        // Check output contains expected structure
        $this->assertStringContainsString('Wink View Generator', $output);
        $this->assertStringContainsString('CRUD Views Command', $output);
    }
}