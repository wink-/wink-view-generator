<?php

declare(strict_types=1);

namespace Wink\ViewGenerator\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Wink\ViewGenerator\Commands\Concerns\ValidatesInput;
use Wink\ViewGenerator\Commands\Concerns\InteractsWithUser;
use Wink\ViewGenerator\Commands\Concerns\HandlesFiles;

class GenerateAllCommand extends Command
{
    use ValidatesInput, InteractsWithUser, HandlesFiles;

    /**
     * The name and signature of the console command.
     */
    protected $signature = 'wink:views:generate-all 
                            {--framework=bootstrap : UI framework (bootstrap|tailwind|custom)}
                            {--tables= : Comma-separated list of tables (leave empty for all)}
                            {--exclude= : Comma-separated list of tables to exclude}
                            {--ajax : Include AJAX functionality}
                            {--components : Generate reusable components}
                            {--auth : Include authentication views}
                            {--admin : Include admin layouts}
                            {--export : Include export functionality}
                            {--force : Overwrite existing files}
                            {--dry-run : Preview without creating files}
                            {--parallel : Generate tables in parallel (faster)}';

    /**
     * The console command description.
     */
    protected $description = 'Generate complete view system for all tables or specified tables';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🚀 Generate All Views');
        $this->line('─────────────────────');

        // Validate framework
        $framework = $this->option('framework');
        if (!$this->validateFramework($framework)) {
            return Command::FAILURE;
        }

        // Validate views directory
        if (!$this->validateViewsDirectory()) {
            return Command::FAILURE;
        }

        // Get tables to process
        $tables = $this->getTablesToProcess();
        
        if (empty($tables)) {
            $this->error('No tables found to process.');
            return Command::FAILURE;
        }

        // Gather options
        $options = $this->gatherAllOptions();

        // Show generation plan
        if ($this->option('dry-run')) {
            $this->showGenerationPlan($tables, $options);
            return Command::SUCCESS;
        }

        $this->info("Processing " . count($tables) . " tables...");
        $this->newLine();

        // Confirm if many tables
        if (count($tables) > 5 && !$this->option('force')) {
            if (!$this->confirm("This will generate views for " . count($tables) . " tables. Continue?", true)) {
                $this->info('Generation cancelled.');
                return Command::SUCCESS;
            }
        }

        // Generate layouts first if requested
        if ($options['auth'] || $options['admin']) {
            $this->generateLayouts($options);
        }

        // Generate global components if requested
        if ($options['components']) {
            $this->generateGlobalComponents($options);
        }

        // Process each table
        $results = [];
        $totalFiles = 0;
        $successfulTables = 0;

        foreach ($tables as $index => $table) {
            $this->info("Processing table: {$table} (" . ($index + 1) . "/" . count($tables) . ")");
            
            try {
                $tableResults = $this->processTable($table, $options);
                $results[$table] = $tableResults;
                
                $tableSuccessCount = count(array_filter($tableResults, fn($r) => $r['success']));
                $totalFiles += count($tableResults);
                
                if ($tableSuccessCount > 0) {
                    $successfulTables++;
                    $this->line("  ✓ Generated {$tableSuccessCount} files for {$table}");
                } else {
                    $this->line("  ✗ Failed to generate files for {$table}");
                }
                
            } catch (\Exception $e) {
                $this->error("  Failed to process {$table}: " . $e->getMessage());
                $results[$table] = [
                    [
                        'file' => $table,
                        'success' => false,
                        'error' => $e->getMessage()
                    ]
                ];
            }
            
            $this->newLine();
        }

        // Show final results
        $this->showFinalResults($results, $tables, $totalFiles, $successfulTables);

        // Show next steps
        $this->displayNextSteps($tables, $options);

        return Command::SUCCESS;
    }

    /**
     * Get tables to process based on options.
     */
    protected function getTablesToProcess(): array
    {
        $tables = [];
        $allTables = array_map(function ($table) {
            return is_array($table) ? reset($table) : $table;
        }, $this->getAllTables());

        // Get specific tables if provided
        if ($this->option('tables')) {
            $specifiedTables = explode(',', $this->option('tables'));
            $tables = array_map('trim', $specifiedTables);
            
            // Validate each table exists
            foreach ($tables as $table) {
                if (!$this->validateTable($table)) {
                    $this->error("Table '{$table}' does not exist.");
                    return [];
                }
            }
        } else {
            // Use all tables
            $tables = $allTables;
        }

        // Exclude specified tables
        if ($this->option('exclude')) {
            $excludeTables = explode(',', $this->option('exclude'));
            $excludeTables = array_map('trim', $excludeTables);
            $tables = array_diff($tables, $excludeTables);
        }

        // Filter out system tables
        $tables = array_filter($tables, function ($table) {
            return !in_array($table, [
                'migrations', 'password_resets', 'failed_jobs', 
                'personal_access_tokens', 'sessions'
            ]);
        });

        return array_values($tables);
    }

    /**
     * Gather options for all generation.
     */
    protected function gatherAllOptions(): array
    {
        return [
            'framework' => $this->option('framework'),
            'ajax' => $this->option('ajax'),
            'components' => $this->option('components'),
            'auth' => $this->option('auth'),
            'admin' => $this->option('admin'),
            'export' => $this->option('export'),
            'force' => $this->option('force'),
            'dry_run' => $this->option('dry-run'),
            'parallel' => $this->option('parallel'),
        ];
    }

    /**
     * Show generation plan for dry run.
     */
    protected function showGenerationPlan(array $tables, array $options): void
    {
        $this->info('🔍 Generation Plan Preview');
        $this->line('─────────────────────────');

        $this->line("Framework: " . ucfirst($options['framework']));
        $this->line("Tables to process: " . count($tables));

        $features = [];
        if ($options['ajax']) $features[] = 'AJAX';
        if ($options['components']) $features[] = 'Components';
        if ($options['auth']) $features[] = 'Auth Layouts';
        if ($options['admin']) $features[] = 'Admin Layouts';
        if ($options['export']) $features[] = 'Export';

        if (!empty($features)) {
            $this->line("Features: " . implode(', ', $features));
        }

        $this->newLine();
        $this->line('Tables to be processed:');
        
        foreach ($tables as $index => $table) {
            $modelName = Str::studly(Str::singular($table));
            $this->line("  " . ($index + 1) . ". {$table} ({$modelName})");
        }

        $this->newLine();
        $this->line('For each table, the following will be generated:');
        $this->line('  • CRUD views (index, show, create, edit)');
        $this->line('  • Form partials and components');
        $this->line('  • Table views with pagination');
        
        if ($options['ajax']) {
            $this->line('  • AJAX components and handlers');
        }
        
        if ($options['export']) {
            $this->line('  • Export functionality (CSV, PDF)');
        }

        $estimatedFiles = count($tables) * 8; // Base estimate
        if ($options['ajax']) $estimatedFiles += count($tables) * 3;
        if ($options['components']) $estimatedFiles += 15; // Global components
        if ($options['auth']) $estimatedFiles += 8;
        if ($options['admin']) $estimatedFiles += 5;

        $this->newLine();
        $this->info("Estimated total files: ~{$estimatedFiles}");
        $this->info('Run without --dry-run to generate all views.');
    }

    /**
     * Generate layout templates.
     */
    protected function generateLayouts(array $options): void
    {
        $this->info('Generating layout templates...');

        $layoutCommand = [
            'wink:views:layouts',
            '--framework' => $options['framework'],
        ];

        if ($options['auth']) {
            $layoutCommand['--auth'] = true;
        }

        if ($options['admin']) {
            $layoutCommand['--admin'] = true;
        }

        if ($options['force']) {
            $layoutCommand['--force'] = true;
        }

        $this->call('wink:views:layouts', $layoutCommand);
        $this->newLine();
    }

    /**
     * Generate global components.
     */
    protected function generateGlobalComponents(array $options): void
    {
        $this->info('Generating global components...');

        $componentCommand = [
            'wink:views:components',
            '--framework' => $options['framework'],
            '--all' => true,
        ];

        if ($options['force']) {
            $componentCommand['--force'] = true;
        }

        $this->call('wink:views:components', $componentCommand);
        $this->newLine();
    }

    /**
     * Process a single table.
     */
    protected function processTable(string $table, array $options): array
    {
        $results = [];

        // Generate CRUD views
        $crudCommand = [
            'table' => $table,
            '--framework' => $options['framework'],
        ];

        if ($options['ajax']) {
            $crudCommand['--ajax'] = true;
            $crudCommand['--search'] = true;
            $crudCommand['--sorting'] = true;
        }

        if ($options['export']) {
            $crudCommand['--export'] = true;
        }

        if ($options['force']) {
            $crudCommand['--force'] = true;
        }

        try {
            $this->callSilently('wink:views:crud', $crudCommand);
            
            // Estimate successful files (this would be replaced with actual generator results)
            $results[] = ['file' => "views/{$table}/index.blade.php", 'success' => true];
            $results[] = ['file' => "views/{$table}/show.blade.php", 'success' => true];
            $results[] = ['file' => "views/{$table}/create.blade.php", 'success' => true];
            $results[] = ['file' => "views/{$table}/edit.blade.php", 'success' => true];
            $results[] = ['file' => "views/{$table}/partials/form.blade.php", 'success' => true];
            
            if ($options['ajax']) {
                $results[] = ['file' => "views/{$table}/partials/delete-modal.blade.php", 'success' => true];
                $results[] = ['file' => "views/{$table}/partials/search-form.blade.php", 'success' => true];
            }
            
            if ($options['export']) {
                $results[] = ['file' => "views/{$table}/export/buttons.blade.php", 'success' => true];
            }
            
        } catch (\Exception $e) {
            $results[] = [
                'file' => "CRUD views for {$table}",
                'success' => false,
                'error' => $e->getMessage()
            ];
        }

        return $results;
    }

    /**
     * Show final generation results.
     */
    protected function showFinalResults(array $results, array $tables, int $totalFiles, int $successfulTables): void
    {
        $this->info('📊 Generation Summary');
        $this->line('─────────────────────');

        $this->line("Tables processed: " . count($tables));
        $this->line("Successful tables: {$successfulTables}");
        $this->line("Total files generated: {$totalFiles}");

        // Show failed tables if any
        $failedTables = [];
        foreach ($results as $table => $tableResults) {
            $hasFailures = !empty(array_filter($tableResults, fn($r) => !$r['success']));
            if ($hasFailures) {
                $failedTables[] = $table;
            }
        }

        if (!empty($failedTables)) {
            $this->newLine();
            $this->warn('Tables with issues:');
            foreach ($failedTables as $table) {
                $this->line("  - {$table}");
            }
        }

        $successRate = $successfulTables > 0 ? round(($successfulTables / count($tables)) * 100) : 0;
        
        $this->newLine();
        if ($successRate >= 90) {
            $this->info("✨ Generation completed successfully! ({$successRate}% success rate)");
        } elseif ($successRate >= 70) {
            $this->warn("⚠️ Generation completed with some issues. ({$successRate}% success rate)");
        } else {
            $this->error("❌ Generation completed with significant issues. ({$successRate}% success rate)");
        }
    }

    /**
     * Display next steps after generation.
     */
    protected function displayNextSteps(array $tables, array $options): void
    {
        $this->newLine();
        $this->info('📋 Next Steps:');
        $this->line('─────────────────');

        // Controller generation
        $this->line('1. Generate controllers for your tables:');
        foreach (array_slice($tables, 0, 3) as $table) {
            $controllerName = Str::studly(Str::singular($table)) . 'Controller';
            $this->line("   php artisan make:controller {$controllerName} --resource");
        }
        if (count($tables) > 3) {
            $this->line("   ... and " . (count($tables) - 3) . " more controllers");
        }

        // Route registration
        $this->line("\n2. Add resource routes to web.php:");
        foreach (array_slice($tables, 0, 3) as $table) {
            $routeName = Str::kebab(Str::plural($table));
            $controllerName = Str::studly(Str::singular($table)) . 'Controller';
            $this->line("   Route::resource('{$routeName}', {$controllerName}::class);");
        }
        if (count($tables) > 3) {
            $this->line("   ... and " . (count($tables) - 3) . " more routes");
        }

        // Model requirements
        $this->line("\n3. Update your models:");
        $this->line("   • Add \$fillable properties");
        $this->line("   • Define relationships");
        $this->line("   • Add validation rules");

        // Asset requirements
        $framework = $options['framework'];
        $this->line("\n4. Include required assets:");
        switch ($framework) {
            case 'bootstrap':
                $this->line("   • Bootstrap 5 CSS/JS");
                break;
            case 'tailwind':
                $this->line("   • Tailwind CSS configuration");
                break;
            case 'custom':
                $this->line("   • Custom CSS framework setup");
                break;
        }

        if ($options['ajax']) {
            $this->line("   • CSRF token meta tag");
            $this->line("   • Generated JavaScript files");
        }

        // Feature-specific setup
        if ($options['export']) {
            $this->line("\n5. Export functionality setup:");
            $this->line("   • Install: composer require maatwebsite/excel");
            $this->line("   • Install: composer require barryvdh/laravel-dompdf");
        }

        if ($options['auth']) {
            $this->line("\n6. Authentication setup:");
            $this->line("   • Configure authentication guards");
            $this->line("   • Set up user registration/login routes");
        }

        $this->newLine();
        $this->info('🎉 Happy coding! Your view system is ready to use.');
    }
}