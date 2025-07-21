<?php

namespace Wink\ViewGenerator\Commands\Concerns;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

trait ValidatesInput
{
    /**
     * Validate that a table exists in the database.
     */
    protected function validateTable(string $table): bool
    {
        if (!Schema::hasTable($table)) {
            $this->error("Table '{$table}' does not exist in the database.");
            
            // Suggest similar table names
            $tables = $this->getAllTables();
            $suggestions = $this->findSimilarNames($table, $tables);
            
            if (!empty($suggestions)) {
                $this->line('Did you mean one of these?');
                foreach ($suggestions as $suggestion) {
                    $this->line("  - {$suggestion}");
                }
            }
            
            return false;
        }
        
        return true;
    }
    
    /**
     * Validate that a model exists.
     */
    protected function validateModel(string $model): bool
    {
        $modelClass = $this->getModelClass($model);
        
        if (!class_exists($modelClass)) {
            $this->error("Model '{$modelClass}' does not exist.");
            return false;
        }
        
        return true;
    }
    
    /**
     * Validate framework option.
     */
    protected function validateFramework(string $framework): bool
    {
        $validFrameworks = ['bootstrap', 'tailwind', 'custom'];
        
        if (!in_array($framework, $validFrameworks)) {
            $this->error("Invalid framework '{$framework}'. Valid options: " . implode(', ', $validFrameworks));
            return false;
        }
        
        return true;
    }
    
    /**
     * Get all database table names.
     */
    protected function getAllTables(): array
    {
        return Schema::getAllTables();
    }
    
    /**
     * Find similar names using levenshtein distance.
     */
    protected function findSimilarNames(string $input, array $candidates, int $maxDistance = 3): array
    {
        $suggestions = [];
        
        foreach ($candidates as $candidate) {
            $tableName = is_array($candidate) ? reset($candidate) : $candidate;
            $distance = levenshtein($input, $tableName);
            
            if ($distance <= $maxDistance) {
                $suggestions[] = $tableName;
            }
        }
        
        return array_slice($suggestions, 0, 5); // Limit to 5 suggestions
    }
    
    /**
     * Get the model class name from table name.
     */
    protected function getModelClass(string $table): string
    {
        $modelName = Str::studly(Str::singular($table));
        return "App\\Models\\{$modelName}";
    }
    
    /**
     * Validate that the views directory is writable.
     */
    protected function validateViewsDirectory(): bool
    {
        $viewsPath = resource_path('views');
        
        if (!is_dir($viewsPath)) {
            $this->error("Views directory does not exist: {$viewsPath}");
            return false;
        }
        
        if (!is_writable($viewsPath)) {
            $this->error("Views directory is not writable: {$viewsPath}");
            return false;
        }
        
        return true;
    }
    
    /**
     * Check if a view file already exists.
     */
    protected function viewExists(string $viewPath): bool
    {
        return file_exists(resource_path("views/{$viewPath}.blade.php"));
    }
}