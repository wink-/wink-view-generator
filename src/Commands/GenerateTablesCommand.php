<?php

namespace Wink\ViewGenerator\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Wink\ViewGenerator\Commands\Concerns\ValidatesInput;
use Wink\ViewGenerator\Commands\Concerns\InteractsWithUser;
use Wink\ViewGenerator\Commands\Concerns\HandlesFiles;
use Wink\ViewGenerator\Generators\TableGenerator;
use Wink\ViewGenerator\Analyzers\ModelAnalyzer;
use Wink\ViewGenerator\Analyzers\FieldAnalyzer;

class GenerateTablesCommand extends Command
{
    use ValidatesInput, InteractsWithUser, HandlesFiles;

    /**
     * The name and signature of the console command.
     */
    protected $signature = 'wink:views:tables 
                            {table : The database table name}
                            {--framework=bootstrap : UI framework (bootstrap|tailwind|custom)}
                            {--sorting : Include column sorting}
                            {--filtering : Include data filtering}
                            {--search : Include search functionality}
                            {--pagination=15 : Records per page}
                            {--bulk-actions : Include bulk action capabilities}
                            {--export : Include export functionality (CSV, PDF)}
                            {--ajax : Enable AJAX table features}
                            {--responsive : Make table responsive/mobile-friendly}
                            {--component : Generate as reusable component}
                            {--force : Overwrite existing files}
                            {--dry-run : Preview without creating files}';

    /**
     * The console command description.
     */
    protected $description = 'Generate data table views with sorting, filtering, and pagination';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('📊 Data Table Generator');
        $this->line('─────────────────────');

        $table = $this->argument('table');

        // Validate table exists
        if (!$this->validateTable($table)) {
            return 1;
        }

        // Validate framework
        $framework = $this->option('framework');
        if (!$this->validateFramework($framework)) {
            return 1;
        }

        // Validate views directory
        if (!$this->validateViewsDirectory()) {
            return 1;
        }

        // Gather options
        $options = $this->gatherTableOptions();

        // Analyze model/table
        $this->info("Analyzing table '{$table}'...");
        $modelAnalyzer = new ModelAnalyzer($table);
        $modelData = $modelAnalyzer->analyze();

        if (empty($modelData['columns'])) {
            $this->error("No columns found in table '{$table}'");
            return 1;
        }

        // Analyze fields for table display
        $fieldAnalyzer = new FieldAnalyzer($modelData['columns']);
        $tableColumns = $fieldAnalyzer->analyzeForTables($options);

        $this->info("Found " . count($tableColumns) . " displayable columns");

        // Show what will be generated
        $filesToGenerate = $this->getTableFilesToGenerate($table, $options);

        if ($this->option('dry-run')) {
            $this->showTableDryRun($table, $filesToGenerate, $tableColumns, $options);
            return 0;
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
                return 0;
            }
        }

        // Generate table views
        $this->info('Generating data table views...');

        $generator = new TableGenerator();
        $results = $generator->generate($table, $modelData, $tableColumns, $options);

        // Generate additional table features if needed
        if ($options['export']) {
            $this->info('Generating export functionality...');
            $exportResults = $this->generateExportViews($table, $tableColumns, $options);
            $results = array_merge($results, $exportResults);
        }

        if ($options['bulk_actions']) {
            $this->info('Generating bulk action components...');
            $bulkResults = $this->generateBulkActionComponents($table, $options);
            $results = array_merge($results, $bulkResults);
        }

        // Show results
        $this->showGenerationResults($results);

        // Show table usage information
        $this->displayTableUsage($table, $tableColumns, $options);

        return 0;
    }

    /**
     * Gather table-specific options.
     */
    protected function gatherTableOptions(): array
    {
        return [
            'framework' => $this->option('framework'),
            'sorting' => $this->option('sorting'),
            'filtering' => $this->option('filtering'),
            'search' => $this->option('search'),
            'pagination' => (int) $this->option('pagination'),
            'bulk_actions' => $this->option('bulk-actions'),
            'export' => $this->option('export'),
            'ajax' => $this->option('ajax'),
            'responsive' => $this->option('responsive'),
            'component' => $this->option('component'),
            'force' => $this->option('force'),
            'dry_run' => $this->option('dry-run'),
        ];
    }

    /**
     * Get list of table files to generate.
     */
    protected function getTableFilesToGenerate(string $table, array $options): array
    {
        $viewDir = Str::kebab(Str::plural($table));
        $files = [];

        // Core table views
        if ($options['component']) {
            $files['Table Component'] = [
                "views/components/data-table.blade.php",
                "views/components/table-header.blade.php",
                "views/components/table-row.blade.php",
            ];
        } else {
            $files['Table Views'] = [
                "views/{$viewDir}/table.blade.php",
                "views/{$viewDir}/partials/table-header.blade.php",
                "views/{$viewDir}/partials/table-body.blade.php",
            ];
        }

        // Search and filter components
        if ($options['search'] || $options['filtering']) {
            $files['Search & Filter'] = [
                "views/{$viewDir}/partials/search-bar.blade.php",
                "views/{$viewDir}/partials/filters.blade.php",
                "views/{$viewDir}/partials/filter-dropdown.blade.php",
            ];
        }

        // Sorting components
        if ($options['sorting']) {
            $files['Sorting Components'] = [
                "views/{$viewDir}/partials/sortable-header.blade.php",
                "views/{$viewDir}/partials/sort-indicator.blade.php",
            ];
        }

        // Pagination components
        $files['Pagination'] = [
            "views/{$viewDir}/partials/pagination.blade.php",
            "views/{$viewDir}/partials/pagination-info.blade.php",
        ];

        // Export components
        if ($options['export']) {
            $files['Export Views'] = [
                "views/{$viewDir}/export/buttons.blade.php",
                "views/{$viewDir}/export/modal.blade.php",
                "views/{$viewDir}/export/csv-template.blade.php",
                "views/{$viewDir}/export/pdf-template.blade.php",
            ];
        }

        // Bulk action components
        if ($options['bulk_actions']) {
            $files['Bulk Actions'] = [
                "views/{$viewDir}/partials/bulk-actions.blade.php",
                "views/{$viewDir}/partials/bulk-select.blade.php",
                "views/{$viewDir}/partials/bulk-toolbar.blade.php",
            ];
        }

        // AJAX components
        if ($options['ajax']) {
            $files['AJAX Components'] = [
                "views/{$viewDir}/partials/table-ajax.blade.php",
                "views/{$viewDir}/partials/loading-skeleton.blade.php",
                "views/{$viewDir}/partials/no-results.blade.php",
            ];
        }

        // Responsive components
        if ($options['responsive']) {
            $files['Responsive Components'] = [
                "views/{$viewDir}/partials/mobile-card.blade.php",
                "views/{$viewDir}/partials/responsive-table.blade.php",
            ];
        }

        return $files;
    }

    /**
     * Show table dry run summary.
     */
    protected function showTableDryRun(string $table, array $files, array $columns, array $options): void
    {
        $this->info('🔍 Table Generation Preview');
        $this->line('─────────────────────────');

        $this->line("Table: {$table}");
        $this->line("Framework: " . ucfirst($options['framework']));
        $this->line("Pagination: {$options['pagination']} per page");

        $features = [];
        if ($options['sorting']) $features[] = 'Sorting';
        if ($options['filtering']) $features[] = 'Filtering';
        if ($options['search']) $features[] = 'Search';
        if ($options['bulk_actions']) $features[] = 'Bulk Actions';
        if ($options['export']) $features[] = 'Export';
        if ($options['ajax']) $features[] = 'AJAX';
        if ($options['responsive']) $features[] = 'Responsive';
        if ($options['component']) $features[] = 'Component';

        if (!empty($features)) {
            $this->line("Features: " . implode(', ', $features));
        }

        $this->newLine();
        $this->line('Table columns to be displayed:');

        foreach ($columns as $column) {
            $sortable = $column['sortable'] ? ' (sortable)' : '';
            $filterable = $column['filterable'] ? ' (filterable)' : '';
            $this->line("  - {$column['name']}: {$column['display_type']}{$sortable}{$filterable}");
        }

        $this->newLine();
        $this->line('Files that would be generated:');

        $totalFiles = 0;
        foreach ($files as $category => $fileList) {
            $this->line("  {$category}:");
            foreach ($fileList as $file) {
                $this->line("    - {$file}");
                $totalFiles++;
            }
        }

        $this->newLine();
        $this->info("Total: {$totalFiles} files would be created");
        $this->info('Run without --dry-run to generate table views.');
    }

    /**
     * Generate export views.
     */
    protected function generateExportViews(string $table, array $columns, array $options): array
    {
        $results = [];
        $viewDir = Str::kebab(Str::plural($table));

        // Export buttons
        $buttonsPath = resource_path("views/{$viewDir}/export/buttons.blade.php");
        $buttonsTemplate = $this->getTemplatePath($options['framework'], 'export', 'buttons');

        $variables = [
            'table' => $table,
            'route_name' => Str::kebab(Str::plural($table)),
        ];

        $results[] = [
            'file' => "views/{$viewDir}/export/buttons.blade.php",
            'success' => $this->generateViewFile($buttonsTemplate, $buttonsPath, $variables),
        ];

        // CSV template
        $csvPath = resource_path("views/{$viewDir}/export/csv-template.blade.php");
        $csvTemplate = $this->getTemplatePath($options['framework'], 'export', 'csv-template');

        $variables['columns'] = $columns;

        $results[] = [
            'file' => "views/{$viewDir}/export/csv-template.blade.php",
            'success' => $this->generateViewFile($csvTemplate, $csvPath, $variables),
        ];

        return $results;
    }

    /**
     * Generate bulk action components.
     */
    protected function generateBulkActionComponents(string $table, array $options): array
    {
        $results = [];
        $viewDir = Str::kebab(Str::plural($table));

        // Bulk actions toolbar
        $toolbarPath = resource_path("views/{$viewDir}/partials/bulk-toolbar.blade.php");
        $toolbarTemplate = $this->getTemplatePath($options['framework'], 'partials', 'bulk-toolbar');

        $variables = [
            'table' => $table,
            'route_name' => Str::kebab(Str::plural($table)),
        ];

        $results[] = [
            'file' => "views/{$viewDir}/partials/bulk-toolbar.blade.php",
            'success' => $this->generateViewFile($toolbarTemplate, $toolbarPath, $variables),
        ];

        // Bulk select component
        $selectPath = resource_path("views/{$viewDir}/partials/bulk-select.blade.php");
        $selectTemplate = $this->getTemplatePath($options['framework'], 'partials', 'bulk-select');

        $results[] = [
            'file' => "views/{$viewDir}/partials/bulk-select.blade.php",
            'success' => $this->generateViewFile($selectTemplate, $selectPath, $variables),
        ];

        return $results;
    }

    /**
     * Display table usage information.
     */
    protected function displayTableUsage(string $table, array $columns, array $options): void
    {
        $this->newLine();
        $this->info('📖 Data Table Usage Guide:');
        $this->line('──────────────────────────');

        $modelName = Str::studly(Str::singular($table));
        $viewDir = Str::kebab(Str::plural($table));

        // Basic usage
        $this->line("\n📋 Basic table usage:");
        
        if ($options['component']) {
            $this->line("  <x-data-table :data=\"\$data\" :columns=\"\$columns\" />");
        } else {
            $this->line("  @include('{$viewDir}.table', ['data' => \$data])");
        }

        // Controller setup
        $this->line("\n🎛️ Controller setup:");
        $this->line("  public function index(Request \$request)");
        $this->line("  {");
        $this->line("      \$query = {$modelName}::query();");
        
        if ($options['search']) {
            $this->line("      ");
            $this->line("      // Search functionality");
            $this->line("      if (\$request->filled('search')) {");
            $this->line("          \$query->where('name', 'like', '%' . \$request->search . '%');");
            $this->line("      }");
        }

        if ($options['filtering']) {
            $this->line("      ");
            $this->line("      // Filtering");
            $this->line("      if (\$request->filled('status')) {");
            $this->line("          \$query->where('status', \$request->status);");
            $this->line("      }");
        }

        if ($options['sorting']) {
            $this->line("      ");
            $this->line("      // Sorting");
            $this->line("      if (\$request->filled('sort')) {");
            $this->line("          \$direction = \$request->get('direction', 'asc');");
            $this->line("          \$query->orderBy(\$request->sort, \$direction);");
            $this->line("      }");
        }

        $this->line("      ");
        $this->line("      \$data = \$query->paginate({$options['pagination']});");
        $this->line("      ");
        $this->line("      return view('{$viewDir}.index', compact('data'));");
        $this->line("  }");

        // Feature-specific instructions
        if ($options['export']) {
            $this->line("\n📤 Export functionality:");
            $this->line("  • Install maatwebsite/excel package");
            $this->line("  • Add export routes in web.php");
            $this->line("  • Implement export methods in controller");
            $this->line("  • Use export buttons in table header");
        }

        if ($options['bulk_actions']) {
            $this->line("\n🔄 Bulk actions:");
            $this->line("  • Add bulk action routes");
            $this->line("  • Handle bulk operations in controller");
            $this->line("  • Use JavaScript for checkbox selection");
            $this->line("  • Provide user feedback for bulk operations");
        }

        if ($options['ajax']) {
            $this->line("\n🚀 AJAX functionality:");
            $this->line("  • Return JSON responses for AJAX requests");
            $this->line("  • Handle pagination via AJAX");
            $this->line("  • Update table content without page refresh");
            $this->line("  • Include loading states for better UX");
        }

        // Required assets
        $this->line("\n🔧 Required assets:");
        $framework = $options['framework'];
        
        switch ($framework) {
            case 'bootstrap':
                $this->line("  • Bootstrap 5 CSS/JS");
                if ($options['ajax']) {
                    $this->line("  • Include table.js for AJAX functionality");
                }
                break;
            case 'tailwind':
                $this->line("  • Tailwind CSS");
                if ($options['responsive']) {
                    $this->line("  • Configure responsive breakpoints");
                }
                break;
            case 'custom':
                $this->line("  • Custom CSS for table styling");
                $this->line("  • JavaScript for interactive features");
                break;
        }

        $this->newLine();
        $this->info("✨ Data table for '{$table}' generated successfully!");
    }
}