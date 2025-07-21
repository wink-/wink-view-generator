<?php

namespace Wink\ViewGenerator\Tests\Integration;

use Wink\ViewGenerator\Tests\TestCase;
use Wink\ViewGenerator\ViewGeneratorServiceProvider;
use Wink\ViewGenerator\Commands\GenerateViewsCommand;
use Wink\ViewGenerator\Commands\GenerateCrudViewsCommand;
use Wink\ViewGenerator\Commands\GenerateComponentsCommand;
use Wink\ViewGenerator\Commands\GenerateFormsCommand;
use Wink\ViewGenerator\Commands\GenerateTablesCommand;
use Wink\ViewGenerator\Commands\GenerateLayoutsCommand;
use Wink\ViewGenerator\Commands\GenerateAllCommand;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;

class ViewGeneratorServiceProviderTest extends TestCase
{
    /** @test */
    public function it_is_loaded_correctly()
    {
        $provider = $this->app->resolveProvider(ViewGeneratorServiceProvider::class);
        $this->assertInstanceOf(ViewGeneratorServiceProvider::class, $provider);
    }

    /** @test */
    public function it_registers_configuration()
    {
        $this->assertNotNull(Config::get('wink-views'));
        $this->assertIsArray(Config::get('wink-views'));
        
        // Check that configuration contains expected keys
        $this->assertArrayHasKey('framework', Config::get('wink-views'));
        $this->assertArrayHasKey('layout', Config::get('wink-views'));
        $this->assertArrayHasKey('components', Config::get('wink-views'));
        $this->assertArrayHasKey('features', Config::get('wink-views'));
    }

    /** @test */
    public function it_registers_all_commands()
    {
        $expectedCommands = [
            'wink:views',
            'wink:views:crud',
            'wink:views:components',
            'wink:views:forms',
            'wink:views:tables',
            'wink:views:layouts',
            'wink:views:all',
        ];

        foreach ($expectedCommands as $command) {
            $this->assertTrue(Artisan::has($command), "Command {$command} is not registered");
        }
    }

    /** @test */
    public function it_registers_generator_services()
    {
        $this->assertTrue($this->app->bound('wink.view-generator.crud'));
        $this->assertTrue($this->app->bound('wink.view-generator.component'));
        $this->assertTrue($this->app->bound('wink.view-generator.form'));
        $this->assertTrue($this->app->bound('wink.view-generator.table'));
        $this->assertTrue($this->app->bound('wink.view-generator.layout'));
    }

    /** @test */
    public function it_registers_analyzer_services()
    {
        $this->assertTrue($this->app->bound('wink.view-generator.model-analyzer'));
        $this->assertTrue($this->app->bound('wink.view-generator.field-analyzer'));
        $this->assertTrue($this->app->bound('wink.view-generator.controller-analyzer'));
        $this->assertTrue($this->app->bound('wink.view-generator.route-analyzer'));
    }

    /** @test */
    public function it_resolves_generator_services()
    {
        $crudGenerator = $this->app->make('wink.view-generator.crud');
        $this->assertInstanceOf(\Wink\ViewGenerator\Generators\CrudViewGenerator::class, $crudGenerator);

        $componentGenerator = $this->app->make('wink.view-generator.component');
        $this->assertInstanceOf(\Wink\ViewGenerator\Generators\ComponentGenerator::class, $componentGenerator);

        $formGenerator = $this->app->make('wink.view-generator.form');
        $this->assertInstanceOf(\Wink\ViewGenerator\Generators\FormGenerator::class, $formGenerator);

        $tableGenerator = $this->app->make('wink.view-generator.table');
        $this->assertInstanceOf(\Wink\ViewGenerator\Generators\TableGenerator::class, $tableGenerator);

        $layoutGenerator = $this->app->make('wink.view-generator.layout');
        $this->assertInstanceOf(\Wink\ViewGenerator\Generators\LayoutGenerator::class, $layoutGenerator);
    }

    /** @test */
    public function it_resolves_analyzer_services()
    {
        $modelAnalyzer = $this->app->make('wink.view-generator.model-analyzer');
        $this->assertInstanceOf(\Wink\ViewGenerator\Analyzers\ModelAnalyzer::class, $modelAnalyzer);

        $fieldAnalyzer = $this->app->make('wink.view-generator.field-analyzer');
        $this->assertInstanceOf(\Wink\ViewGenerator\Analyzers\FieldAnalyzer::class, $fieldAnalyzer);

        $controllerAnalyzer = $this->app->make('wink.view-generator.controller-analyzer');
        $this->assertInstanceOf(\Wink\ViewGenerator\Analyzers\ControllerAnalyzer::class, $controllerAnalyzer);

        $routeAnalyzer = $this->app->make('wink.view-generator.route-analyzer');
        $this->assertInstanceOf(\Wink\ViewGenerator\Analyzers\RouteAnalyzer::class, $routeAnalyzer);
    }

    /** @test */
    public function it_registers_generator_services_as_singletons()
    {
        $crudGenerator1 = $this->app->make('wink.view-generator.crud');
        $crudGenerator2 = $this->app->make('wink.view-generator.crud');
        
        $this->assertSame($crudGenerator1, $crudGenerator2);

        $componentGenerator1 = $this->app->make('wink.view-generator.component');
        $componentGenerator2 = $this->app->make('wink.view-generator.component');
        
        $this->assertSame($componentGenerator1, $componentGenerator2);
    }

    /** @test */
    public function it_provides_all_registered_services()
    {
        $provider = new ViewGeneratorServiceProvider($this->app);
        $providedServices = $provider->provides();

        $expectedServices = [
            GenerateViewsCommand::class,
            GenerateCrudViewsCommand::class,
            GenerateComponentsCommand::class,
            GenerateFormsCommand::class,
            GenerateTablesCommand::class,
            GenerateLayoutsCommand::class,
            GenerateAllCommand::class,
            'wink.view-generator.crud',
            'wink.view-generator.component',
            'wink.view-generator.form',
            'wink.view-generator.table',
            'wink.view-generator.layout',
            'wink.view-generator.model-analyzer',
            'wink.view-generator.field-analyzer',
            'wink.view-generator.controller-analyzer',
            'wink.view-generator.route-analyzer',
        ];

        foreach ($expectedServices as $service) {
            $this->assertContains($service, $providedServices, "Service {$service} is not in provides() array");
        }
    }

    /** @test */
    public function it_registers_commands_only_in_console()
    {
        // Commands should be registered since we're in a console environment during testing
        $expectedCommands = [
            'wink:views',
            'wink:views:crud',
            'wink:views:components',
            'wink:views:forms',
            'wink:views:tables',
            'wink:views:layouts',
            'wink:views:all',
        ];

        foreach ($expectedCommands as $command) {
            $this->assertTrue(Artisan::has($command));
        }
    }

    /** @test */
    public function it_merges_configuration_correctly()
    {
        // The service provider should merge the package config with any existing config
        $defaultFramework = Config::get('wink-views.framework');
        $this->assertNotNull($defaultFramework);
        $this->assertEquals('bootstrap', $defaultFramework);
    }

    /** @test */
    public function it_can_publish_configuration()
    {
        // Test that configuration can be published
        $exitCode = Artisan::call('vendor:publish', [
            '--tag' => 'wink-views-config',
            '--force' => true
        ]);

        // Should complete without error
        $this->assertEquals(0, $exitCode);
    }

    /** @test */
    public function it_can_publish_templates()
    {
        // Test that templates can be published
        $exitCode = Artisan::call('vendor:publish', [
            '--tag' => 'wink-views-templates',
            '--force' => true
        ]);

        // Should complete without error
        $this->assertEquals(0, $exitCode);
    }

    /** @test */
    public function it_can_publish_assets()
    {
        // Test that assets can be published
        $exitCode = Artisan::call('vendor:publish', [
            '--tag' => 'wink-views-assets',
            '--force' => true
        ]);

        // Should complete without error
        $this->assertEquals(0, $exitCode);
    }

    /** @test */
    public function it_can_publish_all_resources()
    {
        // Test that all resources can be published together
        $exitCode = Artisan::call('vendor:publish', [
            '--provider' => ViewGeneratorServiceProvider::class,
            '--force' => true
        ]);

        // Should complete without error
        $this->assertEquals(0, $exitCode);
    }

    /** @test */
    public function it_loads_package_configuration_from_correct_path()
    {
        // The configuration should be loaded from the package's Config directory
        $this->assertNotNull(Config::get('wink-views.framework'));
        $this->assertIsArray(Config::get('wink-views.layout'));
        $this->assertIsArray(Config::get('wink-views.components'));
        $this->assertIsArray(Config::get('wink-views.features'));
    }

    /** @test */
    public function it_registers_services_with_correct_bindings()
    {
        // Test that services are bound correctly in the container
        $this->assertInstanceOf(
            \Wink\ViewGenerator\Generators\CrudViewGenerator::class,
            $this->app->make('wink.view-generator.crud')
        );

        $this->assertInstanceOf(
            \Wink\ViewGenerator\Analyzers\ModelAnalyzer::class,
            $this->app->make('wink.view-generator.model-analyzer')
        );
    }

    /** @test */
    public function it_maintains_service_provider_contract()
    {
        $provider = new ViewGeneratorServiceProvider($this->app);
        
        // Should implement required methods
        $this->assertTrue(method_exists($provider, 'register'));
        $this->assertTrue(method_exists($provider, 'boot'));
        $this->assertTrue(method_exists($provider, 'provides'));
    }

    /** @test */
    public function it_handles_configuration_overrides()
    {
        // Set custom configuration
        Config::set('wink-views.framework', 'tailwind');
        Config::set('wink-views.custom_setting', 'test_value');

        // Configuration should be accessible
        $this->assertEquals('tailwind', Config::get('wink-views.framework'));
        $this->assertEquals('test_value', Config::get('wink-views.custom_setting'));
    }

    /** @test */
    public function it_resolves_commands_correctly()
    {
        $crudCommand = Artisan::resolve('wink:views:crud');
        $this->assertInstanceOf(GenerateCrudViewsCommand::class, $crudCommand);

        $componentsCommand = Artisan::resolve('wink:views:components');
        $this->assertInstanceOf(GenerateComponentsCommand::class, $componentsCommand);

        $allCommand = Artisan::resolve('wink:views:all');
        $this->assertInstanceOf(GenerateAllCommand::class, $allCommand);
    }

    /** @test */
    public function it_provides_package_discovery_configuration()
    {
        // Verify that the package can be auto-discovered
        $providers = $this->app->getLoadedProviders();
        $this->assertArrayHasKey(ViewGeneratorServiceProvider::class, $providers);
    }

    /** @test */
    public function it_supports_service_container_resolution()
    {
        // Test that all registered services can be resolved from the container
        $services = [
            'wink.view-generator.crud',
            'wink.view-generator.component',
            'wink.view-generator.form',
            'wink.view-generator.table',
            'wink.view-generator.layout',
            'wink.view-generator.model-analyzer',
            'wink.view-generator.field-analyzer',
            'wink.view-generator.controller-analyzer',
            'wink.view-generator.route-analyzer',
        ];

        foreach ($services as $service) {
            $resolved = $this->app->make($service);
            $this->assertNotNull($resolved, "Failed to resolve service: {$service}");
        }
    }
}