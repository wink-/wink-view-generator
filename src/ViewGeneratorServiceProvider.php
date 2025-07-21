<?php

namespace Wink\ViewGenerator;

use Illuminate\Support\ServiceProvider;
use Wink\ViewGenerator\Commands\GenerateViewsCommand;
use Wink\ViewGenerator\Commands\GenerateCrudViewsCommand;
use Wink\ViewGenerator\Commands\GenerateComponentsCommand;
use Wink\ViewGenerator\Commands\GenerateFormsCommand;
use Wink\ViewGenerator\Commands\GenerateTablesCommand;
use Wink\ViewGenerator\Commands\GenerateLayoutsCommand;
use Wink\ViewGenerator\Commands\GenerateAllCommand;

class ViewGeneratorServiceProvider extends ServiceProvider
{
    /**
     * The commands to register.
     */
    protected array $commands = [
        GenerateViewsCommand::class,
        GenerateCrudViewsCommand::class,
        GenerateComponentsCommand::class,
        GenerateFormsCommand::class,
        GenerateTablesCommand::class,
        GenerateLayoutsCommand::class,
        GenerateAllCommand::class,
    ];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register the main package config
        $this->mergeConfigFrom(
            __DIR__ . '/Config/wink-views.php',
            'wink-views'
        );

        // Register generator classes
        $this->registerGenerators();

        // Register analyzer classes
        $this->registerAnalyzers();

        // Register commands
        $this->registerCommands();
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Publish configuration
        $this->publishes([
            __DIR__ . '/Config/wink-views.php' => config_path('wink-views.php'),
        ], 'wink-views-config');

        // Publish templates
        $this->publishes([
            __DIR__ . '/Templates' => resource_path('stubs/wink-views'),
        ], 'wink-views-templates');

        // Publish assets
        $this->publishes([
            __DIR__ . '/Assets' => public_path('vendor/wink-views'),
        ], 'wink-views-assets');
    }

    /**
     * Register generator classes.
     */
    protected function registerGenerators(): void
    {
        $this->app->singleton('wink.view-generator.crud', function ($app) {
            return new \Wink\ViewGenerator\Generators\CrudViewGenerator();
        });

        $this->app->singleton('wink.view-generator.component', function ($app) {
            return new \Wink\ViewGenerator\Generators\ComponentGenerator();
        });

        $this->app->singleton('wink.view-generator.form', function ($app) {
            return new \Wink\ViewGenerator\Generators\FormGenerator();
        });

        $this->app->singleton('wink.view-generator.table', function ($app) {
            return new \Wink\ViewGenerator\Generators\TableGenerator();
        });

        $this->app->singleton('wink.view-generator.layout', function ($app) {
            return new \Wink\ViewGenerator\Generators\LayoutGenerator();
        });
    }

    /**
     * Register analyzer classes.
     */
    protected function registerAnalyzers(): void
    {
        $this->app->bind('wink.view-generator.model-analyzer', function ($app) {
            return new \Wink\ViewGenerator\Analyzers\ModelAnalyzer();
        });

        $this->app->bind('wink.view-generator.field-analyzer', function ($app) {
            return new \Wink\ViewGenerator\Analyzers\FieldAnalyzer();
        });

        $this->app->bind('wink.view-generator.controller-analyzer', function ($app) {
            return new \Wink\ViewGenerator\Analyzers\ControllerAnalyzer();
        });

        $this->app->bind('wink.view-generator.route-analyzer', function ($app) {
            return new \Wink\ViewGenerator\Analyzers\RouteAnalyzer();
        });
    }

    /**
     * Register commands.
     */
    protected function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands($this->commands);
        }
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return array_merge($this->commands, [
            'wink.view-generator.crud',
            'wink.view-generator.component',
            'wink.view-generator.form',
            'wink.view-generator.table',
            'wink.view-generator.layout',
            'wink.view-generator.model-analyzer',
            'wink.view-generator.field-analyzer',
            'wink.view-generator.controller-analyzer',
            'wink.view-generator.route-analyzer',
        ]);
    }
}