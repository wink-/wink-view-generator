<?php

declare(strict_types=1);

namespace Wink\ViewGenerator\Commands\Concerns;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

trait HandlesFiles
{
    /**
     * Generate a view file from template.
     */
    protected function generateViewFile(string $template, string $destination, array $variables = []): bool
    {
        try {
            $content = $this->processTemplate($template, $variables);
            
            // Ensure directory exists
            $directory = dirname($destination);
            if (!File::isDirectory($directory)) {
                File::makeDirectory($directory, 0755, true);
            }
            
            File::put($destination, $content);
            
            return true;
        } catch (\Exception $e) {
            $this->error("Failed to generate {$destination}: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Process template with variables.
     */
    protected function processTemplate(string $templatePath, array $variables = []): string
    {
        if (!File::exists($templatePath)) {
            throw new \Exception("Template not found: {$templatePath}");
        }
        
        $content = File::get($templatePath);
        
        // Replace template variables
        foreach ($variables as $key => $value) {
            $placeholder = "{{ {$key} }}";
            $content = str_replace($placeholder, $value, $content);
        }
        
        return $content;
    }
    
    /**
     * Get template path for framework and type.
     */
    protected function getTemplatePath(string $framework, string $type, string $template): string
    {
        $basePath = __DIR__ . '/../../Templates';
        return "{$basePath}/{$framework}/{$type}/{$template}.stub";
    }
    
    /**
     * Get view file path.
     */
    protected function getViewPath(string $table, string $viewType): string
    {
        $viewDir = Str::kebab(Str::plural($table));
        return resource_path("views/{$viewDir}/{$viewType}.blade.php");
    }
    
    /**
     * Get component file path.
     */
    protected function getComponentPath(string $componentName, string $namespace = 'components'): string
    {
        $componentDir = str_replace('.', '/', $namespace);
        $fileName = Str::kebab($componentName);
        return resource_path("views/{$componentDir}/{$fileName}.blade.php");
    }
    
    /**
     * Check if files exist.
     */
    protected function checkExistingFiles(array $filePaths): array
    {
        $existing = [];
        
        foreach ($filePaths as $path) {
            if (File::exists($path)) {
                $existing[] = $this->getRelativePath($path);
            }
        }
        
        return $existing;
    }
    
    /**
     * Get relative path from resources.
     */
    protected function getRelativePath(string $fullPath): string
    {
        $resourcePath = resource_path();
        return str_replace($resourcePath . '/', '', $fullPath);
    }
    
    /**
     * Backup existing file.
     */
    protected function backupFile(string $filePath): bool
    {
        if (!File::exists($filePath)) {
            return true;
        }
        
        $backupPath = $filePath . '.backup.' . date('Y-m-d-H-i-s');
        
        try {
            File::copy($filePath, $backupPath);
            return true;
        } catch (\Exception $e) {
            $this->warn("Could not backup {$filePath}: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Create directory if it doesn't exist.
     */
    protected function ensureDirectoryExists(string $path): bool
    {
        if (File::isDirectory($path)) {
            return true;
        }
        
        try {
            File::makeDirectory($path, 0755, true);
            return true;
        } catch (\Exception $e) {
            $this->error("Could not create directory {$path}: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get list of files to be generated.
     */
    protected function getFilesToGenerate(string $table, array $types, array $options = []): array
    {
        $files = [];
        $viewDir = Str::kebab(Str::plural($table));
        
        foreach ($types as $type) {
            switch ($type) {
                case 'crud':
                    $files['CRUD Views'] = [
                        "views/{$viewDir}/index.blade.php",
                        "views/{$viewDir}/show.blade.php",
                        "views/{$viewDir}/create.blade.php",
                        "views/{$viewDir}/edit.blade.php",
                    ];
                    
                    if ($options['ajax'] ?? false) {
                        $files['CRUD Views'][] = "views/{$viewDir}/partials/delete-modal.blade.php";
                    }
                    break;
                    
                case 'components':
                    $componentNamespace = $options['component_namespace'] ?? 'components';
                    $files['Components'] = [
                        "views/{$componentNamespace}/form-input.blade.php",
                        "views/{$componentNamespace}/data-table.blade.php",
                        "views/{$componentNamespace}/search-form.blade.php",
                    ];
                    break;
                    
                case 'forms':
                    $files['Forms'] = [
                        "views/{$viewDir}/forms/create.blade.php",
                        "views/{$viewDir}/forms/edit.blade.php",
                    ];
                    break;
                    
                case 'layouts':
                    $files['Layouts'] = [
                        "views/layouts/app.blade.php",
                        "views/layouts/admin.blade.php",
                    ];
                    
                    if ($options['auth'] ?? false) {
                        $files['Layouts'][] = "views/layouts/auth.blade.php";
                    }
                    break;
            }
        }
        
        return $files;
    }
    
    /**
     * Generate asset files if needed.
     */
    protected function generateAssets(string $framework, array $features = []): array
    {
        $results = [];
        
        // Generate CSS file
        if (in_array('custom-styles', $features)) {
            $cssPath = public_path("css/wink-views-{$framework}.css");
            $cssTemplate = $this->getTemplatePath($framework, 'assets', 'styles');
            
            $results[] = [
                'file' => "css/wink-views-{$framework}.css",
                'success' => $this->generateViewFile($cssTemplate, $cssPath),
            ];
        }
        
        // Generate JavaScript file
        if (in_array('ajax', $features) || in_array('interactive', $features)) {
            $jsPath = public_path("js/wink-views-{$framework}.js");
            $jsTemplate = $this->getTemplatePath($framework, 'assets', 'scripts');
            
            $results[] = [
                'file' => "js/wink-views-{$framework}.js",
                'success' => $this->generateViewFile($jsTemplate, $jsPath),
            ];
        }
        
        return $results;
    }
}