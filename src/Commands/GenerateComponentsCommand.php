<?php

declare(strict_types=1);

namespace Wink\ViewGenerator\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Wink\ViewGenerator\Commands\Concerns\ValidatesInput;
use Wink\ViewGenerator\Commands\Concerns\InteractsWithUser;
use Wink\ViewGenerator\Commands\Concerns\HandlesFiles;
use Wink\ViewGenerator\Generators\ComponentGenerator;
use Wink\ViewGenerator\Analyzers\ModelAnalyzer;

class GenerateComponentsCommand extends Command
{
    use ValidatesInput, InteractsWithUser, HandlesFiles;

    /**
     * The name and signature of the console command.
     */
    protected $signature = 'wink:views:components 
                            {table? : The database table name (optional for generic components)}
                            {--framework=bootstrap : UI framework (bootstrap|tailwind|custom)}
                            {--namespace=components : Component namespace}
                            {--form-inputs : Generate form input components}
                            {--data-tables : Generate data table components}
                            {--modals : Generate modal components}
                            {--search : Generate search components}
                            {--alerts : Generate alert/notification components}
                            {--pagination : Generate pagination components}
                            {--all : Generate all component types}
                            {--force : Overwrite existing files}
                            {--dry-run : Preview without creating files}';

    /**
     * The console command description.
     */
    protected $description = 'Generate reusable Blade components for forms, tables, modals, and UI elements';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🧩 Component Generator');
        $this->line('───────────────────');

        $table = $this->argument('table');
        
        // Validate table if provided
        if ($table && !$this->validateTable($table)) {
            return Command::FAILURE;
        }

        // Validate framework
        $framework = $this->option('framework');
        if (!$this->validateFramework($framework)) {
            return Command::FAILURE;
        }

        // Validate views directory
        if (!$this->validateViewsDirectory()) {
            return Command::FAILURE;
        }

        // Gather options
        $options = $this->gatherComponentOptions();

        // Determine component types to generate
        $componentTypes = $this->getComponentTypes($options);

        if (empty($componentTypes)) {
            $this->error('No component types selected. Use --all or specify individual component flags.');
            return Command::FAILURE;
        }

        // Analyze model/table if provided
        $modelData = [];
        if ($table) {
            $this->info("Analyzing table '{$table}'...");
            $modelAnalyzer = new ModelAnalyzer($table);
            $modelData = $modelAnalyzer->analyze();
        }

        // Show what will be generated
        $filesToGenerate = $this->getComponentFilesToGenerate($componentTypes, $options);

        if ($this->option('dry-run')) {
            $this->showComponentDryRun($componentTypes, $filesToGenerate, $options);
            return Command::SUCCESS;
        }

        $this->showGenerationSummary($filesToGenerate, $options);

        // Check for existing files
        $allFiles = [];
        foreach ($filesToGenerate as $files) {
            foreach ($files as $file) {
                $allFiles[] = resource_path($file);
            }
        }

        $existingFiles = $this->checkExistingFiles($allFiles);

        // Handle existing files
        if (!empty($existingFiles) && !$this->option('force')) {
            if (!$this->confirmOverwrite($existingFiles)) {
                $this->info('Generation cancelled.');
                return Command::SUCCESS;
            }
        }

        // Generate components
        $this->info('Generating components...');

        $generator = new ComponentGenerator();
        $results = [];

        foreach ($componentTypes as $type) {
            $this->line("  Generating {$type} components...");
            
            try {
                $componentResults = $generator->generateType($type, $table, $modelData, $options);
                $results = array_merge($results, $componentResults);
            } catch (\Exception $e) {
                $this->error("Failed to generate {$type} components: " . $e->getMessage());
                $results[] = [
                    'file' => "{$type} components",
                    'success' => false,
                    'error' => $e->getMessage()
                ];
            }
        }

        // Show results
        $this->showGenerationResults($results);

        // Show usage examples
        $this->displayComponentUsage($componentTypes, $options);

        return Command::SUCCESS;
    }

    /**
     * Gather component-specific options.
     */
    protected function gatherComponentOptions(): array
    {
        return [
            'framework' => $this->option('framework'),
            'namespace' => $this->option('namespace'),
            'form_inputs' => $this->option('form-inputs'),
            'data_tables' => $this->option('data-tables'),
            'modals' => $this->option('modals'),
            'search' => $this->option('search'),
            'alerts' => $this->option('alerts'),
            'pagination' => $this->option('pagination'),
            'all' => $this->option('all'),
            'force' => $this->option('force'),
            'dry_run' => $this->option('dry-run'),
        ];
    }

    /**
     * Determine which component types to generate.
     */
    protected function getComponentTypes(array $options): array
    {
        if ($options['all']) {
            return ['form-inputs', 'data-tables', 'modals', 'search', 'alerts', 'pagination'];
        }

        $types = [];

        if ($options['form_inputs']) $types[] = 'form-inputs';
        if ($options['data_tables']) $types[] = 'data-tables';
        if ($options['modals']) $types[] = 'modals';
        if ($options['search']) $types[] = 'search';
        if ($options['alerts']) $types[] = 'alerts';
        if ($options['pagination']) $types[] = 'pagination';

        return $types;
    }

    /**
     * Get list of component files to generate.
     */
    protected function getComponentFilesToGenerate(array $componentTypes, array $options): array
    {
        $namespace = $options['namespace'];
        $files = [];

        foreach ($componentTypes as $type) {
            switch ($type) {
                case 'form-inputs':
                    $files['Form Components'] = [
                        "views/{$namespace}/form/input.blade.php",
                        "views/{$namespace}/form/textarea.blade.php",
                        "views/{$namespace}/form/select.blade.php",
                        "views/{$namespace}/form/checkbox.blade.php",
                        "views/{$namespace}/form/radio.blade.php",
                        "views/{$namespace}/form/file.blade.php",
                        "views/{$namespace}/form/date.blade.php",
                        "views/{$namespace}/form/number.blade.php",
                        "views/{$namespace}/form/email.blade.php",
                        "views/{$namespace}/form/password.blade.php",
                    ];
                    break;

                case 'data-tables':
                    $files['Table Components'] = [
                        "views/{$namespace}/table/data-table.blade.php",
                        "views/{$namespace}/table/sortable-header.blade.php",
                        "views/{$namespace}/table/pagination.blade.php",
                        "views/{$namespace}/table/empty-state.blade.php",
                        "views/{$namespace}/table/actions.blade.php",
                    ];
                    break;

                case 'modals':
                    $files['Modal Components'] = [
                        "views/{$namespace}/modal/base.blade.php",
                        "views/{$namespace}/modal/confirm.blade.php",
                        "views/{$namespace}/modal/form.blade.php",
                        "views/{$namespace}/modal/info.blade.php",
                    ];
                    break;

                case 'search':
                    $files['Search Components'] = [
                        "views/{$namespace}/search/form.blade.php",
                        "views/{$namespace}/search/filters.blade.php",
                        "views/{$namespace}/search/results.blade.php",
                        "views/{$namespace}/search/suggestions.blade.php",
                    ];
                    break;

                case 'alerts':
                    $files['Alert Components'] = [
                        "views/{$namespace}/alert/base.blade.php",
                        "views/{$namespace}/alert/success.blade.php",
                        "views/{$namespace}/alert/error.blade.php",
                        "views/{$namespace}/alert/warning.blade.php",
                        "views/{$namespace}/alert/info.blade.php",
                    ];
                    break;

                case 'pagination':
                    $files['Pagination Components'] = [
                        "views/{$namespace}/pagination/simple.blade.php",
                        "views/{$namespace}/pagination/detailed.blade.php",
                        "views/{$namespace}/pagination/info.blade.php",
                    ];
                    break;
            }
        }

        return $files;
    }

    /**
     * Show component dry run summary.
     */
    protected function showComponentDryRun(array $componentTypes, array $files, array $options): void
    {
        $this->info('🔍 Component Generation Preview');
        $this->line('─────────────────────────────');

        $this->line("Framework: " . ucfirst($options['framework']));
        $this->line("Namespace: {$options['namespace']}");
        $this->line("Component Types: " . implode(', ', $componentTypes));

        $this->newLine();
        $this->line('Components that would be generated:');

        $totalFiles = 0;
        foreach ($files as $category => $fileList) {
            $this->line("  {$category}:");
            foreach ($fileList as $file) {
                $this->line("    - {$file}");
                $totalFiles++;
            }
        }

        $this->newLine();
        $this->info("Total: {$totalFiles} component files would be created");
        $this->info('Run without --dry-run to generate components.');
    }

    /**
     * Display component usage examples.
     */
    protected function displayComponentUsage(array $componentTypes, array $options): void
    {
        $this->newLine();
        $this->info('📖 Component Usage Examples:');
        $this->line('─────────────────────────────');

        $namespace = $options['namespace'];

        foreach ($componentTypes as $type) {
            switch ($type) {
                case 'form-inputs':
                    $this->line("\n🔤 Form Components:");
                    $this->line("  <x-{$namespace}.form.input name=\"title\" label=\"Title\" required />");
                    $this->line("  <x-{$namespace}.form.select name=\"status\" label=\"Status\" :options=\"\$statuses\" />");
                    $this->line("  <x-{$namespace}.form.textarea name=\"description\" label=\"Description\" rows=\"4\" />");
                    break;

                case 'data-tables':
                    $this->line("\n📊 Table Components:");
                    $this->line("  <x-{$namespace}.table.data-table :data=\"\$users\" :columns=\"\$columns\" />");
                    $this->line("  <x-{$namespace}.table.sortable-header column=\"name\" label=\"Name\" />");
                    break;

                case 'modals':
                    $this->line("\n🪟 Modal Components:");
                    $this->line("  <x-{$namespace}.modal.confirm id=\"deleteModal\" title=\"Confirm Delete\" />");
                    $this->line("  <x-{$namespace}.modal.form id=\"editModal\" title=\"Edit Item\" />");
                    break;

                case 'search':
                    $this->line("\n🔍 Search Components:");
                    $this->line("  <x-{$namespace}.search.form placeholder=\"Search items...\" />");
                    $this->line("  <x-{$namespace}.search.filters :filters=\"\$filters\" />");
                    break;

                case 'alerts':
                    $this->line("\n🚨 Alert Components:");
                    $this->line("  <x-{$namespace}.alert.success message=\"Item saved successfully!\" />");
                    $this->line("  <x-{$namespace}.alert.error :message=\"\$errors->first()\" />");
                    break;

                case 'pagination':
                    $this->line("\n📄 Pagination Components:");
                    $this->line("  <x-{$namespace}.pagination.detailed :paginator=\"\$items\" />");
                    $this->line("  <x-{$namespace}.pagination.simple :paginator=\"\$items\" />");
                    break;
            }
        }

        $this->newLine();
        $this->info('💡 Tips:');
        $this->line('────────');
        $this->line('• All components are customizable via props');
        $this->line('• Use slots for complex content');
        $this->line('• Components follow ' . ucfirst($options['framework']) . ' conventions');
        $this->line('• Check individual component files for all available props');

        $this->newLine();
        $this->info("✨ Components generated successfully in '{$namespace}' namespace!");
    }
}