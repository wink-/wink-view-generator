<?php

namespace Wink\ViewGenerator\Generators;

abstract class AbstractViewGenerator
{
    /**
     * Generate views based on the specific implementation.
     */
    abstract public function generate(string $table, array $modelData, array $options): array;

    /**
     * Get template path for framework and type.
     */
    protected function getTemplatePath(string $framework, string $type, string $template): string
    {
        // Find the package root directory by looking for composer.json
        $currentDir = __DIR__;
        while ($currentDir !== '/' && !file_exists($currentDir . '/composer.json')) {
            $currentDir = dirname($currentDir);
        }
        
        $basePath = $currentDir . '/resources/stubs';
        return "{$basePath}/{$framework}/{$type}/{$template}";
    }

    /**
     * Process template with variables.
     */
    protected function processTemplate(string $templatePath, array $variables = []): string
    {
        if (!file_exists($templatePath)) {
            throw new \Exception("Template not found: {$templatePath}");
        }

        $content = file_get_contents($templatePath);

        // Replace template variables
        foreach ($variables as $key => $value) {
            $placeholder = "{{ {$key} }}";
            $content = str_replace($placeholder, $value, $content);
        }

        return $content;
    }

    /**
     * Generate a view file from template.
     */
    protected function generateViewFile(string $template, string $destination, array $variables = []): bool
    {
        try {
            $content = $this->processTemplate($template, $variables);

            // Ensure directory exists
            $directory = dirname($destination);
            if (!is_dir($directory)) {
                mkdir($directory, 0755, true);
            }

            file_put_contents($destination, $content);

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}