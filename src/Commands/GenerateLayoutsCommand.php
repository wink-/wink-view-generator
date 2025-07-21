<?php

declare(strict_types=1);

namespace Wink\ViewGenerator\Commands;

use Illuminate\Console\Command;
use Wink\ViewGenerator\Commands\Concerns\ValidatesInput;
use Wink\ViewGenerator\Commands\Concerns\InteractsWithUser;
use Wink\ViewGenerator\Commands\Concerns\HandlesFiles;
use Wink\ViewGenerator\Generators\LayoutGenerator;

class GenerateLayoutsCommand extends Command
{
    use ValidatesInput, InteractsWithUser, HandlesFiles;

    /**
     * The name and signature of the console command.
     */
    protected $signature = 'wink:views:layouts 
                            {--framework=bootstrap : UI framework (bootstrap|tailwind|custom)}
                            {--auth : Include authentication layouts}
                            {--admin : Include admin/dashboard layouts}
                            {--error : Include error page layouts}
                            {--email : Include email layouts}
                            {--navigation : Include navigation components}
                            {--sidebar : Include sidebar navigation}
                            {--breadcrumbs : Include breadcrumb navigation}
                            {--footer : Include footer component}
                            {--all : Generate all layout types}
                            {--force : Overwrite existing files}
                            {--dry-run : Preview without creating files}';

    /**
     * The console command description.
     */
    protected $description = 'Generate layout templates, navigation, and page structures';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🏗️ Layout Generator');
        $this->line('──────────────────');

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
        $options = $this->gatherLayoutOptions();

        // Determine layout types to generate
        $layoutTypes = $this->getLayoutTypes($options);

        if (empty($layoutTypes)) {
            $this->error('No layout types selected. Use --all or specify individual layout flags.');
            return Command::FAILURE;
        }

        // Show what will be generated
        $filesToGenerate = $this->getLayoutFilesToGenerate($layoutTypes, $options);

        if ($this->option('dry-run')) {
            $this->showLayoutDryRun($layoutTypes, $filesToGenerate, $options);
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

        // Generate layouts
        $this->info('Generating layout templates...');

        $generator = new LayoutGenerator();
        $results = [];

        foreach ($layoutTypes as $type) {
            $this->line("  Generating {$type} layouts...");
            
            try {
                $layoutResults = $generator->generateType($type, $options);
                $results = array_merge($results, $layoutResults);
            } catch (\Exception $e) {
                $this->error("Failed to generate {$type} layouts: " . $e->getMessage());
                $results[] = [
                    'file' => "{$type} layouts",
                    'success' => false,
                    'error' => $e->getMessage()
                ];
            }
        }

        // Show results
        $this->showGenerationResults($results);

        // Show layout usage information
        $this->displayLayoutUsage($layoutTypes, $options);

        return Command::SUCCESS;
    }

    /**
     * Gather layout-specific options.
     */
    protected function gatherLayoutOptions(): array
    {
        return [
            'framework' => $this->option('framework'),
            'auth' => $this->option('auth'),
            'admin' => $this->option('admin'),
            'error' => $this->option('error'),
            'email' => $this->option('email'),
            'navigation' => $this->option('navigation'),
            'sidebar' => $this->option('sidebar'),
            'breadcrumbs' => $this->option('breadcrumbs'),
            'footer' => $this->option('footer'),
            'all' => $this->option('all'),
            'force' => $this->option('force'),
            'dry_run' => $this->option('dry-run'),
        ];
    }

    /**
     * Determine which layout types to generate.
     */
    protected function getLayoutTypes(array $options): array
    {
        if ($options['all']) {
            return ['app', 'auth', 'admin', 'error', 'email'];
        }

        $types = ['app']; // Always include base app layout

        if ($options['auth']) $types[] = 'auth';
        if ($options['admin']) $types[] = 'admin';
        if ($options['error']) $types[] = 'error';
        if ($options['email']) $types[] = 'email';

        return array_unique($types);
    }

    /**
     * Get list of layout files to generate.
     */
    protected function getLayoutFilesToGenerate(array $layoutTypes, array $options): array
    {
        $files = [];

        foreach ($layoutTypes as $type) {
            switch ($type) {
                case 'app':
                    $files['App Layouts'] = [
                        'views/layouts/app.blade.php',
                        'views/layouts/guest.blade.php',
                    ];
                    break;

                case 'auth':
                    $files['Auth Layouts'] = [
                        'views/layouts/auth.blade.php',
                        'views/auth/login.blade.php',
                        'views/auth/register.blade.php',
                        'views/auth/forgot-password.blade.php',
                        'views/auth/reset-password.blade.php',
                    ];
                    break;

                case 'admin':
                    $files['Admin Layouts'] = [
                        'views/layouts/admin.blade.php',
                        'views/layouts/dashboard.blade.php',
                    ];
                    break;

                case 'error':
                    $files['Error Pages'] = [
                        'views/errors/404.blade.php',
                        'views/errors/500.blade.php',
                        'views/errors/403.blade.php',
                        'views/errors/419.blade.php',
                        'views/errors/503.blade.php',
                    ];
                    break;

                case 'email':
                    $files['Email Layouts'] = [
                        'views/layouts/email.blade.php',
                        'views/emails/base.blade.php',
                    ];
                    break;
            }
        }

        // Navigation components
        if ($options['navigation'] || $options['all']) {
            $files['Navigation'] = [
                'views/components/navigation/header.blade.php',
                'views/components/navigation/main-nav.blade.php',
                'views/components/navigation/user-menu.blade.php',
            ];
        }

        // Sidebar components
        if ($options['sidebar'] || $options['admin']) {
            $files['Sidebar'] = [
                'views/components/navigation/sidebar.blade.php',
                'views/components/navigation/sidebar-menu.blade.php',
                'views/components/navigation/sidebar-item.blade.php',
            ];
        }

        // Breadcrumb components
        if ($options['breadcrumbs'] || $options['all']) {
            $files['Breadcrumbs'] = [
                'views/components/navigation/breadcrumbs.blade.php',
                'views/components/navigation/breadcrumb-item.blade.php',
            ];
        }

        // Footer components
        if ($options['footer'] || $options['all']) {
            $files['Footer'] = [
                'views/components/layout/footer.blade.php',
            ];
        }

        return $files;
    }

    /**
     * Show layout dry run summary.
     */
    protected function showLayoutDryRun(array $layoutTypes, array $files, array $options): void
    {
        $this->info('🔍 Layout Generation Preview');
        $this->line('─────────────────────────────');

        $this->line("Framework: " . ucfirst($options['framework']));
        $this->line("Layout Types: " . implode(', ', $layoutTypes));

        $features = [];
        if ($options['navigation']) $features[] = 'Navigation';
        if ($options['sidebar']) $features[] = 'Sidebar';
        if ($options['breadcrumbs']) $features[] = 'Breadcrumbs';
        if ($options['footer']) $features[] = 'Footer';

        if (!empty($features)) {
            $this->line("Components: " . implode(', ', $features));
        }

        $this->newLine();
        $this->line('Layout files that would be generated:');

        $totalFiles = 0;
        foreach ($files as $category => $fileList) {
            $this->line("  {$category}:");
            foreach ($fileList as $file) {
                $this->line("    - {$file}");
                $totalFiles++;
            }
        }

        $this->newLine();
        $this->info("Total: {$totalFiles} layout files would be created");
        $this->info('Run without --dry-run to generate layouts.');
    }

    /**
     * Display layout usage information.
     */
    protected function displayLayoutUsage(array $layoutTypes, array $options): void
    {
        $this->newLine();
        $this->info('📖 Layout Usage Guide:');
        $this->line('─────────────────────────');

        // Basic layout usage
        $this->line("\n🏗️ Using layouts in views:");
        $this->line("  @extends('layouts.app')");
        $this->line("  @section('title', 'Page Title')");
        $this->line("  @section('content')");
        $this->line("      <!-- Your content here -->");
        $this->line("  @endsection");

        // Layout-specific usage
        foreach ($layoutTypes as $type) {
            switch ($type) {
                case 'auth':
                    $this->line("\n🔐 Authentication layouts:");
                    $this->line("  • login.blade.php: @extends('layouts.auth')");
                    $this->line("  • register.blade.php: @extends('layouts.auth')");
                    $this->line("  • Include validation error display");
                    $this->line("  • Add password strength indicators");
                    break;

                case 'admin':
                    $this->line("\n👨‍💼 Admin layouts:");
                    $this->line("  • Use @extends('layouts.admin') for admin pages");
                    $this->line("  • Include sidebar navigation");
                    $this->line("  • Add breadcrumb navigation");
                    $this->line("  • Include user menu and notifications");
                    break;

                case 'error':
                    $this->line("\n❌ Error pages:");
                    $this->line("  • Custom 404, 500, 403 error pages");
                    $this->line("  • Include navigation back to home");
                    $this->line("  • Add helpful error messages");
                    $this->line("  • Maintain site branding");
                    break;

                case 'email':
                    $this->line("\n📧 Email layouts:");
                    $this->line("  • Use @extends('layouts.email') for email templates");
                    $this->line("  • Include responsive email styles");
                    $this->line("  • Add unsubscribe links where needed");
                    $this->line("  • Test across email clients");
                    break;
            }
        }

        // Navigation components
        if ($options['navigation'] || $options['all']) {
            $this->line("\n🧭 Navigation components:");
            $this->line("  <x-navigation.header />");
            $this->line("  <x-navigation.main-nav :items=\"\$navItems\" />");
            $this->line("  <x-navigation.user-menu :user=\"\$user\" />");
        }

        // Sidebar usage
        if ($options['sidebar'] || $options['admin']) {
            $this->line("\n📋 Sidebar navigation:");
            $this->line("  <x-navigation.sidebar :items=\"\$sidebarItems\" />");
            $this->line("  <x-navigation.sidebar-item :item=\"\$item\" />");
        }

        // Breadcrumbs
        if ($options['breadcrumbs'] || $options['all']) {
            $this->line("\n🍞 Breadcrumb navigation:");
            $this->line("  <x-navigation.breadcrumbs :items=\"\$breadcrumbs\" />");
        }

        // Asset requirements
        $this->line("\n🔧 Asset requirements:");
        $framework = $options['framework'];
        
        switch ($framework) {
            case 'bootstrap':
                $this->line("  • Bootstrap 5 CSS/JS");
                $this->line("  • Bootstrap Icons");
                if (in_array('admin', $layoutTypes)) {
                    $this->line("  • Admin dashboard CSS");
                }
                break;
            case 'tailwind':
                $this->line("  • Tailwind CSS");
                $this->line("  • Heroicons or similar icon set");
                if (in_array('admin', $layoutTypes)) {
                    $this->line("  • Configure sidebar responsive breakpoints");
                }
                break;
            case 'custom':
                $this->line("  • Custom CSS framework");
                $this->line("  • Icon font or SVG icons");
                $this->line("  • JavaScript for interactive navigation");
                break;
        }

        // Configuration tips
        $this->line("\n⚙️ Configuration tips:");
        $this->line("  • Update config/app.php with site name");
        $this->line("  • Configure navigation items in a service provider");
        $this->line("  • Add meta tags for SEO in layout head section");
        $this->line("  • Include CSRF token meta tag for AJAX requests");

        // Security considerations
        $this->line("\n🔒 Security considerations:");
        $this->line("  • Include CSRF protection in forms");
        $this->line("  • Add Content Security Policy headers");
        $this->line("  • Escape all user-generated content");
        $this->line("  • Validate all navigation permissions");

        $this->newLine();
        $this->info("✨ Layout templates generated successfully!");
    }
}