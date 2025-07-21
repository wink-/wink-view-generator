<?php

namespace Wink\ViewGenerator\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Wink\ViewGenerator\Commands\Concerns\ValidatesInput;
use Wink\ViewGenerator\Commands\Concerns\InteractsWithUser;
use Wink\ViewGenerator\Commands\Concerns\HandlesFiles;
use Wink\ViewGenerator\Generators\FormGenerator;
use Wink\ViewGenerator\Analyzers\ModelAnalyzer;
use Wink\ViewGenerator\Analyzers\FieldAnalyzer;

class GenerateFormsCommand extends Command
{
    use ValidatesInput, InteractsWithUser, HandlesFiles;

    /**
     * The name and signature of the console command.
     */
    protected $signature = 'wink:views:forms 
                            {table : The database table name}
                            {--framework=bootstrap : UI framework (bootstrap|tailwind|custom)}
                            {--layout=layouts.app : Master layout template}
                            {--rich-text : Include rich text editor fields}
                            {--file-upload : Include file upload handling}
                            {--date-picker : Include date picker components}
                            {--ajax : Enable AJAX form submission}
                            {--validation=inline : Validation style (inline|summary|both)}
                            {--components : Use blade components for form fields}
                            {--separate-forms : Generate separate create/edit forms}
                            {--force : Overwrite existing files}
                            {--dry-run : Preview without creating files}';

    /**
     * The console command description.
     */
    protected $description = 'Generate form views and components for create/edit operations';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('📝 Form Generator');
        $this->line('─────────────────');

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
        $options = $this->gatherFormOptions();

        // Analyze model/table
        $this->info("Analyzing table '{$table}'...");
        $modelAnalyzer = new ModelAnalyzer($table);
        $modelData = $modelAnalyzer->analyze();

        if (empty($modelData['columns'])) {
            $this->error("No columns found in table '{$table}'");
            return 1;
        }

        // Analyze fields for form generation
        $fieldAnalyzer = new FieldAnalyzer($modelData['columns']);
        $formFields = $fieldAnalyzer->analyzeForForms($options);

        $this->info("Found " . count($formFields) . " form fields");

        // Show what will be generated
        $filesToGenerate = $this->getFormFilesToGenerate($table, $options);

        if ($this->option('dry-run')) {
            $this->showFormDryRun($table, $filesToGenerate, $formFields, $options);
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

        // Generate forms
        $this->info('Generating form views...');

        $generator = new FormGenerator();
        $results = $generator->generate($table, $modelData, $formFields, $options);

        // Generate additional form components if needed
        if ($options['components']) {
            $this->info('Generating form components...');
            $componentResults = $this->generateFormComponents($table, $formFields, $options);
            $results = array_merge($results, $componentResults);
        }

        // Show results
        $this->showGenerationResults($results);

        // Show form usage information
        $this->displayFormUsage($table, $formFields, $options);

        return 0;
    }

    /**
     * Gather form-specific options.
     */
    protected function gatherFormOptions(): array
    {
        return [
            'framework' => $this->option('framework'),
            'layout' => $this->option('layout'),
            'rich_text' => $this->option('rich-text'),
            'file_upload' => $this->option('file-upload'),
            'date_picker' => $this->option('date-picker'),
            'ajax' => $this->option('ajax'),
            'validation' => $this->option('validation'),
            'components' => $this->option('components'),
            'separate_forms' => $this->option('separate-forms'),
            'force' => $this->option('force'),
            'dry_run' => $this->option('dry-run'),
        ];
    }

    /**
     * Get list of form files to generate.
     */
    protected function getFormFilesToGenerate(string $table, array $options): array
    {
        $viewDir = Str::kebab(Str::plural($table));
        $files = [];

        if ($options['separate_forms']) {
            // Separate create and edit forms
            $files['Form Views'] = [
                "views/{$viewDir}/forms/create.blade.php",
                "views/{$viewDir}/forms/edit.blade.php",
            ];
        } else {
            // Unified form partial
            $files['Form Views'] = [
                "views/{$viewDir}/partials/form.blade.php",
            ];
        }

        // Form validation views
        if ($options['validation'] === 'summary' || $options['validation'] === 'both') {
            $files['Validation Views'] = [
                "views/{$viewDir}/partials/validation-summary.blade.php",
            ];
        }

        // AJAX form handling
        if ($options['ajax']) {
            $files['AJAX Views'] = [
                "views/{$viewDir}/partials/form-ajax.blade.php",
                "views/{$viewDir}/partials/loading-state.blade.php",
            ];
        }

        // Rich text editor
        if ($options['rich_text']) {
            $files['Rich Text'] = [
                "views/{$viewDir}/partials/rich-text-editor.blade.php",
            ];
        }

        // File upload components
        if ($options['file_upload']) {
            $files['File Upload'] = [
                "views/{$viewDir}/partials/file-upload.blade.php",
                "views/{$viewDir}/partials/file-preview.blade.php",
            ];
        }

        // Component-based forms
        if ($options['components']) {
            $files['Form Components'] = [
                "views/components/form-field.blade.php",
                "views/components/form-group.blade.php",
                "views/components/form-actions.blade.php",
            ];
        }

        return $files;
    }

    /**
     * Show form dry run summary.
     */
    protected function showFormDryRun(string $table, array $files, array $fields, array $options): void
    {
        $this->info('🔍 Form Generation Preview');
        $this->line('─────────────────────────');

        $this->line("Table: {$table}");
        $this->line("Framework: " . ucfirst($options['framework']));
        $this->line("Layout: {$options['layout']}");
        $this->line("Validation Style: {$options['validation']}");

        $features = [];
        if ($options['rich_text']) $features[] = 'Rich Text';
        if ($options['file_upload']) $features[] = 'File Upload';
        if ($options['date_picker']) $features[] = 'Date Picker';
        if ($options['ajax']) $features[] = 'AJAX';
        if ($options['components']) $features[] = 'Components';

        if (!empty($features)) {
            $this->line("Features: " . implode(', ', $features));
        }

        $this->newLine();
        $this->line('Form fields to be generated:');

        foreach ($fields as $field) {
            $type = $field['input_type'] ?? 'text';
            $required = $field['required'] ? ' (required)' : '';
            $this->line("  - {$field['name']}: {$type}{$required}");
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
        $this->info('Run without --dry-run to generate forms.');
    }

    /**
     * Generate additional form components.
     */
    protected function generateFormComponents(string $table, array $fields, array $options): array
    {
        $results = [];

        // Generate field-specific components
        foreach ($fields as $field) {
            if ($field['input_type'] === 'rich_text' && $options['rich_text']) {
                $componentPath = resource_path("views/components/rich-text-field.blade.php");
                $template = $this->getTemplatePath($options['framework'], 'components', 'rich-text-field');
                
                $variables = [
                    'field_name' => $field['name'],
                    'field_label' => $field['label'],
                ];

                $results[] = [
                    'file' => 'views/components/rich-text-field.blade.php',
                    'success' => $this->generateViewFile($template, $componentPath, $variables),
                ];
            }

            if ($field['input_type'] === 'file' && $options['file_upload']) {
                $componentPath = resource_path("views/components/file-upload-field.blade.php");
                $template = $this->getTemplatePath($options['framework'], 'components', 'file-upload-field');
                
                $variables = [
                    'field_name' => $field['name'],
                    'field_label' => $field['label'],
                    'accept_types' => $field['accept_types'] ?? '*',
                ];

                $results[] = [
                    'file' => 'views/components/file-upload-field.blade.php',
                    'success' => $this->generateViewFile($template, $componentPath, $variables),
                ];
            }

            if ($field['input_type'] === 'date' && $options['date_picker']) {
                $componentPath = resource_path("views/components/date-picker-field.blade.php");
                $template = $this->getTemplatePath($options['framework'], 'components', 'date-picker-field');
                
                $variables = [
                    'field_name' => $field['name'],
                    'field_label' => $field['label'],
                ];

                $results[] = [
                    'file' => 'views/components/date-picker-field.blade.php',
                    'success' => $this->generateViewFile($template, $componentPath, $variables),
                ];
            }
        }

        return $results;
    }

    /**
     * Display form usage information.
     */
    protected function displayFormUsage(string $table, array $fields, array $options): void
    {
        $this->newLine();
        $this->info('📖 Form Usage Guide:');
        $this->line('──────────────────────');

        $modelName = Str::studly(Str::singular($table));
        $viewDir = Str::kebab(Str::plural($table));

        // Form inclusion examples
        $this->line("\n🔗 Include forms in your views:");
        
        if ($options['separate_forms']) {
            $this->line("  <!-- In create.blade.php -->");
            $this->line("  @include('{$viewDir}.forms.create')");
            $this->line("  <!-- In edit.blade.php -->");
            $this->line("  @include('{$viewDir}.forms.edit', ['{$modelName}' => \${$modelName}])");
        } else {
            $this->line("  <!-- In create.blade.php -->");
            $this->line("  @include('{$viewDir}.partials.form', ['action' => 'create'])");
            $this->line("  <!-- In edit.blade.php -->");
            $this->line("  @include('{$viewDir}.partials.form', ['action' => 'edit', '{$modelName}' => \${$modelName}])");
        }

        // Controller requirements
        $this->line("\n🎛️ Controller requirements:");
        $this->line("  public function create()");
        $this->line("  {");
        $this->line("      return view('{$viewDir}.create');");
        $this->line("  }");
        $this->line("  ");
        $this->line("  public function edit({$modelName} \${$modelName})");
        $this->line("  {");
        $this->line("      return view('{$viewDir}.edit', compact('{$modelName}'));");
        $this->line("  }");

        // Validation
        $this->line("\n✅ Form validation:");
        $this->line("  • Create a FormRequest class for validation rules");
        $this->line("  • Use \$request->validate() in controller methods");
        $this->line("  • Errors will be displayed automatically");

        // Special field handling
        $specialFields = array_filter($fields, function($field) {
            return in_array($field['input_type'], ['rich_text', 'file', 'date']);
        });

        if (!empty($specialFields)) {
            $this->line("\n🎯 Special field handling:");
            
            foreach ($specialFields as $field) {
                switch ($field['input_type']) {
                    case 'rich_text':
                        $this->line("  • {$field['name']}: Include TinyMCE/CKEditor scripts");
                        break;
                    case 'file':
                        $this->line("  • {$field['name']}: Handle file uploads in controller");
                        break;
                    case 'date':
                        $this->line("  • {$field['name']}: Include Flatpickr or similar date picker");
                        break;
                }
            }
        }

        // AJAX forms
        if ($options['ajax']) {
            $this->line("\n🚀 AJAX form handling:");
            $this->line("  • Include CSRF token in meta tags");
            $this->line("  • Return JSON responses from controller");
            $this->line("  • Handle success/error states in JavaScript");
            $this->line("  • Use loading states for better UX");
        }

        $this->newLine();
        $this->info("✨ Forms for '{$table}' generated successfully!");
    }
}