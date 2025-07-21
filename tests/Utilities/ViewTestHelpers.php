<?php

namespace Wink\ViewGenerator\Tests\Utilities;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\View;

class ViewTestHelpers
{
    /**
     * Verify that generated view content follows Laravel conventions
     */
    public static function assertValidBladeTemplate(string $content): array
    {
        $issues = [];

        // Check for proper Blade directive usage
        if (!preg_match('/@extends\([\'"][^\'"]+[\'"]\)/', $content)) {
            if (strpos($content, '@section') !== false) {
                $issues[] = 'Uses @section without @extends';
            }
        }

        // Check for unclosed directives
        $openSections = preg_match_all('/@section\([\'"][^\'"]+[\'"]/', $content);
        $closeSections = preg_match_all('/@endsection/', $content);
        if ($openSections !== $closeSections) {
            $issues[] = 'Unmatched @section/@endsection directives';
        }

        // Check for proper CSRF protection in forms
        if (preg_match('/<form[^>]*method=[\'"](?:post|put|patch|delete)[\'"][^>]*>/', $content)) {
            if (!preg_match('/@csrf|{{ csrf_field\(\) }}/', $content)) {
                $issues[] = 'Form missing CSRF protection';
            }
        }

        // Check for proper HTML structure
        if (strpos($content, '<') !== false) {
            $htmlCheck = static::validateBasicHtmlStructure($content);
            $issues = array_merge($issues, $htmlCheck);
        }

        return $issues;
    }

    /**
     * Validate basic HTML structure
     */
    protected static function validateBasicHtmlStructure(string $content): array
    {
        $issues = [];

        // Check for unclosed tags (basic check)
        $selfClosingTags = ['area', 'base', 'br', 'col', 'embed', 'hr', 'img', 'input', 'link', 'meta', 'param', 'source', 'track', 'wbr'];
        
        preg_match_all('/<(\w+)(?:\s[^>]*)?>/', $content, $openTags);
        preg_match_all('/<\/(\w+)>/', $content, $closeTags);

        $openTagNames = array_filter($openTags[1], function($tag) use ($selfClosingTags) {
            return !in_array(strtolower($tag), $selfClosingTags);
        });

        $openCount = array_count_values($openTagNames);
        $closeCount = array_count_values($closeTags[1]);

        foreach ($openCount as $tag => $count) {
            $closeTagCount = $closeCount[$tag] ?? 0;
            if ($count !== $closeTagCount) {
                $issues[] = "Unmatched HTML tags for: {$tag} (opened: {$count}, closed: {$closeTagCount})";
            }
        }

        return $issues;
    }

    /**
     * Assert that view contains accessibility features
     */
    public static function assertAccessibilityCompliant(string $content): array
    {
        $issues = [];

        // Check for missing alt attributes on images
        if (preg_match_all('/<img(?![^>]*alt=)[^>]*>/', $content)) {
            $issues[] = 'Images missing alt attributes';
        }

        // Check for form inputs without labels
        preg_match_all('/<input[^>]*type=[\'"](?!hidden)[^\'"]*[\'"][^>]*>/', $content, $inputs);
        foreach ($inputs[0] as $input) {
            if (!preg_match('/id=[\'"]([^\'"]+)[\'"]/', $input, $idMatch)) {
                $issues[] = 'Form input missing id attribute for label association';
                continue;
            }
            
            $inputId = $idMatch[1];
            if (!preg_match('/for=[\'"]' . preg_quote($inputId, '/') . '[\'"]/', $content)) {
                if (!preg_match('/aria-label=/', $input)) {
                    $issues[] = "Form input '{$inputId}' missing label or aria-label";
                }
            }
        }

        // Check for heading hierarchy
        preg_match_all('/<h([1-6])[^>]*>/', $content, $headings);
        if (!empty($headings[1])) {
            $levels = array_map('intval', $headings[1]);
            $previousLevel = 0;
            foreach ($levels as $level) {
                if ($level > $previousLevel + 1) {
                    $issues[] = "Heading hierarchy skips levels (h{$previousLevel} to h{$level})";
                }
                $previousLevel = $level;
            }
        }

        // Check for missing ARIA landmarks
        if (strlen($content) > 200) { // Only check substantial content
            if (!preg_match('/<(?:main|nav|aside|header|footer|section|article)[^>]*>|role=[\'"](?:main|navigation|complementary|banner|contentinfo)[\'"]/', $content)) {
                $issues[] = 'Content missing ARIA landmarks or semantic HTML5 elements';
            }
        }

        return $issues;
    }

    /**
     * Assert that view follows responsive design principles
     */
    public static function assertResponsiveDesign(string $content): array
    {
        $issues = [];

        // Check for responsive classes (Bootstrap)
        $bootstrapResponsive = preg_match('/col-(?:xs|sm|md|lg|xl)-|d-(?:none|block|inline|flex)-(?:sm|md|lg|xl)|text-(?:sm|md|lg|xl)-/', $content);
        
        // Check for responsive classes (Tailwind)
        $tailwindResponsive = preg_match('/(?:sm|md|lg|xl|2xl):/', $content);
        
        // Check for CSS Grid/Flexbox
        $flexGrid = preg_match('/(?:d-flex|flex|grid|display:\s*(?:flex|grid))/', $content);

        if (!$bootstrapResponsive && !$tailwindResponsive && !$flexGrid) {
            $issues[] = 'No responsive design classes detected';
        }

        // Check for fixed widths that might break responsiveness
        if (preg_match('/width:\s*\d+px|min-width:\s*\d+px/', $content)) {
            $issues[] = 'Fixed pixel widths detected (may not be responsive)';
        }

        return $issues;
    }

    /**
     * Validate that view follows framework conventions
     */
    public static function assertFrameworkConventions(string $content, string $framework): array
    {
        $issues = [];

        switch ($framework) {
            case 'bootstrap':
                $issues = array_merge($issues, static::validateBootstrapConventions($content));
                break;
            case 'tailwind':
                $issues = array_merge($issues, static::validateTailwindConventions($content));
                break;
            case 'custom':
                $issues = array_merge($issues, static::validateCustomFrameworkConventions($content));
                break;
        }

        return $issues;
    }

    /**
     * Validate Bootstrap-specific conventions
     */
    protected static function validateBootstrapConventions(string $content): array
    {
        $issues = [];

        // Check for proper Bootstrap container usage
        if (preg_match('/<div[^>]*class=[\'"][^\'"]*(row|col-)[^\'"]/', $content)) {
            if (!preg_match('/<div[^>]*class=[\'"][^\'"]*(container|container-fluid)[^\'"]/', $content)) {
                $issues[] = 'Bootstrap grid system used without container';
            }
        }

        // Check for proper form classes
        if (preg_match('/<form/', $content)) {
            if (!preg_match('/form-group|mb-3|form-floating/', $content)) {
                $issues[] = 'Bootstrap forms missing proper form grouping classes';
            }
        }

        // Check for button classes
        if (preg_match('/<button/', $content)) {
            if (!preg_match('/btn\s+btn-/', $content)) {
                $issues[] = 'Bootstrap buttons missing btn classes';
            }
        }

        return $issues;
    }

    /**
     * Validate Tailwind-specific conventions
     */
    protected static function validateTailwindConventions(string $content): array
    {
        $issues = [];

        // Check for utility-first approach
        if (preg_match('/<style[^>]*>|style=[\'"]/', $content)) {
            $issues[] = 'Inline styles or style tags found (not Tailwind utility-first approach)';
        }

        // Check for proper spacing utilities
        if (preg_match('/<div[^>]*class=[\'"][^\'"]*(p-|m-|px-|py-|mx-|my-)[^\'"]/', $content)) {
            // Good - using Tailwind spacing utilities
        } else if (strlen($content) > 100) {
            $issues[] = 'No Tailwind spacing utilities detected';
        }

        return $issues;
    }

    /**
     * Validate custom framework conventions
     */
    protected static function validateCustomFrameworkConventions(string $content): array
    {
        $issues = [];

        // For custom frameworks, check for consistent class naming
        preg_match_all('/class=[\'"]([^\'"]+)[\'"]/', $content, $classes);
        $allClasses = [];
        foreach ($classes[1] as $classString) {
            $allClasses = array_merge($allClasses, explode(' ', $classString));
        }

        // Check for consistent naming conventions
        $namingPatterns = [
            'kebab-case' => '/^[a-z][a-z0-9-]*$/',
            'camelCase' => '/^[a-z][a-zA-Z0-9]*$/',
            'snake_case' => '/^[a-z][a-z0-9_]*$/',
        ];

        $patternMatches = [];
        foreach ($namingPatterns as $pattern => $regex) {
            $matches = 0;
            foreach ($allClasses as $class) {
                if (preg_match($regex, $class)) {
                    $matches++;
                }
            }
            $patternMatches[$pattern] = $matches;
        }

        if (max($patternMatches) < count($allClasses) * 0.8) {
            $issues[] = 'Inconsistent CSS class naming conventions';
        }

        return $issues;
    }

    /**
     * Generate view validation report
     */
    public static function generateValidationReport(string $content, string $framework = 'bootstrap'): array
    {
        return [
            'blade_template_issues' => static::assertValidBladeTemplate($content),
            'accessibility_issues' => static::assertAccessibilityCompliant($content),
            'responsive_design_issues' => static::assertResponsiveDesign($content),
            'framework_convention_issues' => static::assertFrameworkConventions($content, $framework),
        ];
    }

    /**
     * Check if view content is production-ready
     */
    public static function isProductionReady(string $content, string $framework = 'bootstrap'): bool
    {
        $report = static::generateValidationReport($content, $framework);
        
        // No critical issues for production readiness
        $criticalIssues = [
            'blade_template_issues',
            'accessibility_issues',
        ];

        foreach ($criticalIssues as $issueType) {
            if (!empty($report[$issueType])) {
                return false;
            }
        }

        return true;
    }

    /**
     * Extract template variables from content
     */
    public static function extractTemplateVariables(string $content): array
    {
        preg_match_all('/\{\{\s*([^}]+)\s*\}\}/', $content, $matches);
        return array_unique(array_map('trim', $matches[1]));
    }

    /**
     * Validate that all required variables are present
     */
    public static function validateRequiredVariables(string $content, array $requiredVars): array
    {
        $foundVars = static::extractTemplateVariables($content);
        $missingVars = [];

        foreach ($requiredVars as $requiredVar) {
            $found = false;
            foreach ($foundVars as $foundVar) {
                if (strpos($foundVar, $requiredVar) !== false) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $missingVars[] = $requiredVar;
            }
        }

        return $missingVars;
    }

    /**
     * Count code complexity metrics
     */
    public static function calculateComplexityMetrics(string $content): array
    {
        return [
            'total_lines' => substr_count($content, "\n") + 1,
            'blade_directives' => preg_match_all('/@\w+/', $content),
            'html_elements' => preg_match_all('/<\w+/', $content),
            'template_variables' => count(static::extractTemplateVariables($content)),
            'conditional_blocks' => preg_match_all('/@if|@unless|@switch/', $content),
            'loop_blocks' => preg_match_all('/@foreach|@for|@while/', $content),
        ];
    }
}