<?php

declare(strict_types=1);

namespace Wink\ViewGenerator\Commands\Concerns;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

trait InteractsWithUser
{
    /**
     * Ask user to select a table from available tables.
     */
    protected function askForTable(): ?string
    {
        $tables = $this->getAllTables();
        
        if (empty($tables)) {
            $this->error('No tables found in the database.');
            return null;
        }
        
        $tableNames = array_map(function ($table) {
            return is_array($table) ? reset($table) : $table;
        }, $tables);
        
        sort($tableNames);
        
        return $this->choice('Which table would you like to generate views for?', $tableNames);
    }
    
    /**
     * Ask user to select a framework.
     */
    protected function askForFramework(): string
    {
        $frameworks = [
            'bootstrap' => 'Bootstrap 5',
            'tailwind' => 'Tailwind CSS',
            'custom' => 'Custom CSS Framework'
        ];
        
        $choice = $this->choice(
            'Which UI framework would you like to use?',
            array_values($frameworks),
            'Bootstrap 5'
        );
        
        return array_search($choice, $frameworks);
    }
    
    /**
     * Ask user for additional features.
     */
    protected function askForFeatures(): array
    {
        $features = [];
        
        if ($this->confirm('Include AJAX functionality?', true)) {
            $features[] = 'ajax';
        }
        
        if ($this->confirm('Generate reusable components?', true)) {
            $features[] = 'components';
        }
        
        if ($this->confirm('Include search and filtering?', true)) {
            $features[] = 'search';
        }
        
        if ($this->confirm('Include sorting capabilities?', true)) {
            $features[] = 'sorting';
        }
        
        if ($this->confirm('Include bulk actions?', false)) {
            $features[] = 'bulk-actions';
        }
        
        if ($this->confirm('Include export functionality?', false)) {
            $features[] = 'export';
        }
        
        return $features;
    }
    
    /**
     * Ask user to confirm file overwrite.
     */
    protected function confirmOverwrite(array $existingFiles): bool
    {
        if (empty($existingFiles)) {
            return true;
        }
        
        $this->warn('The following files already exist:');
        foreach ($existingFiles as $file) {
            $this->line("  - {$file}");
        }
        
        return $this->confirm('Do you want to overwrite these files?', false);
    }
    
    /**
     * Display generation progress.
     */
    protected function showProgress(string $message, callable $callback)
    {
        $this->info($message);
        
        $result = $callback();
        
        if ($result === false) {
            $this->error('Failed!');
        } else {
            $this->info('Done!');
        }
        
        return $result;
    }
    
    /**
     * Display a summary of what will be generated.
     */
    protected function showGenerationSummary(array $files, array $options): void
    {
        $this->info('Generation Summary:');
        $this->line('─────────────────');
        
        $this->line("Framework: " . ucfirst($options['framework'] ?? 'bootstrap'));
        
        if (!empty($options['features'])) {
            $this->line("Features: " . implode(', ', $options['features']));
        }
        
        $this->newLine();
        $this->line('Files to be generated:');
        
        foreach ($files as $category => $fileList) {
            $this->line("  {$category}:");
            foreach ($fileList as $file) {
                $this->line("    - {$file}");
            }
        }
        
        $this->newLine();
    }
    
    /**
     * Display generation results.
     */
    protected function showGenerationResults(array $results): void
    {
        $successful = array_filter($results, fn($result) => $result['success']);
        $failed = array_filter($results, fn($result) => !$result['success']);
        
        if (!empty($successful)) {
            $this->info('Successfully generated:');
            foreach ($successful as $result) {
                $this->line("  ✓ {$result['file']}");
            }
        }
        
        if (!empty($failed)) {
            $this->error('Failed to generate:');
            foreach ($failed as $result) {
                $this->line("  ✗ {$result['file']} - {$result['error']}");
            }
        }
        
        $total = count($results);
        $successCount = count($successful);
        
        $this->newLine();
        $this->info("Generation complete: {$successCount}/{$total} files created successfully.");
    }
    
    /**
     * Ask for layout template name.
     */
    protected function askForLayout(): string
    {
        return $this->ask('Which layout template should be extended?', 'layouts.app');
    }
    
    /**
     * Ask for component namespace.
     */
    protected function askForComponentNamespace(): string
    {
        return $this->ask('Component namespace (for blade components)?', 'components');
    }
}