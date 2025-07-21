<?php

namespace Wink\ViewGenerator\Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;
use Wink\ViewGenerator\ViewGeneratorServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Mockery;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * The test files directory
     */
    protected string $testFilesPath = '';

    /**
     * The test views directory  
     */
    protected string $testViewsPath = '';

    /**
     * Setup the test environment
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->testFilesPath = __DIR__ . '/tmp';
        $this->testViewsPath = $this->testFilesPath . '/views';

        // Create test directories
        File::ensureDirectoryExists($this->testFilesPath);
        File::ensureDirectoryExists($this->testViewsPath);

        // Set up test database
        $this->setupDatabase();

        // Load test migrations
        $this->loadTestMigrations();

        // Set test configuration
        $this->setTestConfiguration();
    }

    /**
     * Clean up after tests
     */
    protected function tearDown(): void
    {
        // Clean up test files
        if (File::exists($this->testFilesPath)) {
            File::deleteDirectory($this->testFilesPath);
        }

        Mockery::close();
        parent::tearDown();
    }

    /**
     * Get package providers
     */
    protected function getPackageProviders($app): array
    {
        return [
            ViewGeneratorServiceProvider::class,
        ];
    }

    /**
     * Define environment setup
     */
    protected function defineEnvironment($app): void
    {
        // Setup test database
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        // Set test views path
        $app['config']->set('view.paths', [
            $this->testViewsPath,
            resource_path('views'),
        ]);

        // Set test filesystem paths
        $app['config']->set('filesystems.disks.test', [
            'driver' => 'local',
            'root' => $this->testFilesPath,
        ]);
    }

    /**
     * Setup test database
     */
    protected function setupDatabase(): void
    {
        Config::set('database.default', 'testing');
    }

    /**
     * Load test migrations
     */
    protected function loadTestMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/Fixtures/migrations');
    }

    /**
     * Set test configuration
     */
    protected function setTestConfiguration(): void
    {
        Config::set('wink-views', [
            'framework' => 'bootstrap',
            'layout' => [
                'master' => 'layouts.app',
                'admin' => 'layouts.admin',
                'auth' => 'layouts.auth',
            ],
            'components' => [
                'use_components' => true,
                'component_namespace' => 'components',
                'livewire_integration' => false,
            ],
            'features' => [
                'pagination' => true,
                'search' => true,
                'filtering' => true,
                'sorting' => true,
                'bulk_actions' => true,
                'export' => true,
                'ajax_forms' => true,
            ],
            'styling' => [
                'dark_mode' => true,
                'animations' => true,
                'icons' => 'bootstrap-icons',
            ],
            'forms' => [
                'validation_style' => 'inline',
                'rich_text_editor' => 'tinymce',
                'date_picker' => 'flatpickr',
                'file_upload' => 'dropzone',
            ],
            'accessibility' => [
                'enabled' => true,
                'wcag_level' => 'AA',
                'aria_labels' => true,
                'keyboard_navigation' => true,
                'screen_reader_support' => true,
            ],
            'performance' => [
                'lazy_loading' => true,
                'asset_minification' => true,
                'cdn_assets' => false,
                'cache_templates' => true,
            ],
        ]);
    }

    /**
     * Create a test model file
     */
    protected function createTestModel(string $name, array $fillable = [], array $relations = []): void
    {
        $modelPath = app_path("Models/{$name}.php");
        
        File::ensureDirectoryExists(dirname($modelPath));

        $fillableString = !empty($fillable) ? "'" . implode("', '", $fillable) . "'" : '';
        $relationsString = '';

        foreach ($relations as $relation => $config) {
            $type = $config['type'] ?? 'belongsTo';
            $model = $config['model'] ?? $relation;
            
            $relationsString .= "
    public function {$relation}()
    {
        return \$this->{$type}({$model}::class);
    }
";
        }

        $content = "<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class {$name} extends Model
{
    use HasFactory;

    protected \$fillable = [{$fillableString}];
{$relationsString}
}";

        File::put($modelPath, $content);
    }

    /**
     * Create a test controller file
     */
    protected function createTestController(string $name, string $model = null): void
    {
        $controllerPath = app_path("Http/Controllers/{$name}Controller.php");
        
        File::ensureDirectoryExists(dirname($controllerPath));

        $model = $model ?? $name;
        $modelVariable = lcfirst($model);

        $content = "<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\\{$model};
use Illuminate\Http\Request;

class {$name}Controller extends Controller
{
    public function index()
    {
        \${$modelVariable}s = {$model}::paginate(15);
        return view('{$modelVariable}.index', compact('{$modelVariable}s'));
    }

    public function show({$model} \${$modelVariable})
    {
        return view('{$modelVariable}.show', compact('{$modelVariable}'));
    }

    public function create()
    {
        return view('{$modelVariable}.create');
    }

    public function store(Request \$request)
    {
        \$validated = \$request->validate([
            // Add validation rules
        ]);

        {$model}::create(\$validated);

        return redirect()->route('{$modelVariable}.index')->with('success', '{$model} created successfully.');
    }

    public function edit({$model} \${$modelVariable})
    {
        return view('{$modelVariable}.edit', compact('{$modelVariable}'));
    }

    public function update(Request \$request, {$model} \${$modelVariable})
    {
        \$validated = \$request->validate([
            // Add validation rules
        ]);

        \${$modelVariable}->update(\$validated);

        return redirect()->route('{$modelVariable}.index')->with('success', '{$model} updated successfully.');
    }

    public function destroy({$model} \${$modelVariable})
    {
        \${$modelVariable}->delete();

        return redirect()->route('{$modelVariable}.index')->with('success', '{$model} deleted successfully.');
    }
}";

        File::put($controllerPath, $content);
    }

    /**
     * Create test routes
     */
    protected function createTestRoutes(string $model): void
    {
        $routesPath = base_path('routes/test.php');
        
        File::ensureDirectoryExists(dirname($routesPath));

        $modelVariable = lcfirst($model);

        $content = "<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\\{$model}Controller;

Route::resource('{$modelVariable}', {$model}Controller::class);
";

        File::put($routesPath, $content);
    }

    /**
     * Assert that a view file exists
     */
    protected function assertViewExists(string $viewPath): void
    {
        $fullPath = $this->testViewsPath . '/' . str_replace('.', '/', $viewPath) . '.blade.php';
        $this->assertFileExists($fullPath, "View {$viewPath} does not exist at {$fullPath}");
    }

    /**
     * Assert that a view file contains specific content
     */
    protected function assertViewContains(string $viewPath, string $content): void
    {
        $fullPath = $this->testViewsPath . '/' . str_replace('.', '/', $viewPath) . '.blade.php';
        $this->assertFileExists($fullPath);
        $this->assertStringContainsString($content, File::get($fullPath));
    }

    /**
     * Assert that a view file doesn't contain specific content
     */
    protected function assertViewNotContains(string $viewPath, string $content): void
    {
        $fullPath = $this->testViewsPath . '/' . str_replace('.', '/', $viewPath) . '.blade.php';
        $this->assertFileExists($fullPath);
        $this->assertStringNotContainsString($content, File::get($fullPath));
    }

    /**
     * Get the content of a view file
     */
    protected function getViewContent(string $viewPath): string
    {
        $fullPath = $this->testViewsPath . '/' . str_replace('.', '/', $viewPath) . '.blade.php';
        return File::get($fullPath);
    }

    /**
     * Create a temporary view file
     */
    protected function createTestView(string $viewPath, string $content): void
    {
        $fullPath = $this->testViewsPath . '/' . str_replace('.', '/', $viewPath) . '.blade.php';
        File::ensureDirectoryExists(dirname($fullPath));
        File::put($fullPath, $content);
    }

    /**
     * Assert command output contains text
     */
    protected function assertCommandOutputContains(string $command, string $expectedOutput): void
    {
        $this->artisan($command)
            ->expectsOutput($expectedOutput)
            ->assertExitCode(0);
    }

    /**
     * Assert command fails with specific exit code
     */
    protected function assertCommandFails(string $command, int $expectedExitCode = 1): void
    {
        $this->artisan($command)
            ->assertExitCode($expectedExitCode);
    }

    /**
     * Get test stub content
     */
    protected function getStubContent(string $framework, string $type, string $stub): string
    {
        $stubPath = __DIR__ . "/../resources/stubs/{$framework}/{$type}/{$stub}.blade.php.stub";
        return File::get($stubPath);
    }

    /**
     * Mock file system operations
     */
    protected function mockFileSystem(): Mockery\MockInterface
    {
        return Mockery::mock(Filesystem::class);
    }

    /**
     * Create a test database table
     */
    protected function createTestTable(string $table, array $columns): void
    {
        $this->app['db']->connection()->getSchemaBuilder()->create($table, function ($table) use ($columns) {
            $table->id();
            
            foreach ($columns as $column => $type) {
                if (is_array($type)) {
                    $table->{$type['type']}($column, ...$type['args'] ?? []);
                } else {
                    $table->{$type}($column);
                }
            }
            
            $table->timestamps();
        });
    }

    /**
     * Create test data
     */
    protected function createTestData(string $model, array $data = []): object
    {
        $modelClass = "App\\Models\\{$model}";
        return $modelClass::create($data);
    }

    /**
     * Assert that generated code is valid PHP
     */
    protected function assertValidPhp(string $code): void
    {
        $this->assertNotFalse(
            php_check_syntax($code, $error),
            "Generated PHP code is invalid: {$error}"
        );
    }

    /**
     * Assert that generated HTML is valid
     */
    protected function assertValidHtml(string $html): void
    {
        libxml_use_internal_errors(true);
        $doc = new \DOMDocument();
        $valid = $doc->loadHTML($html);
        $errors = libxml_get_errors();
        libxml_clear_errors();
        
        $this->assertTrue($valid, 'Generated HTML is invalid: ' . implode(', ', array_map(function($error) {
            return $error->message;
        }, $errors)));
    }

    /**
     * Assert accessibility compliance
     */
    protected function assertAccessibilityCompliant(string $html): void
    {
        // Check for basic accessibility attributes
        $this->assertStringContainsString('aria-', $html, 'Missing ARIA attributes');
        
        // Check for alt attributes on images
        if (strpos($html, '<img') !== false) {
            $this->assertStringContainsString('alt=', $html, 'Images missing alt attributes');
        }
        
        // Check for labels on form inputs
        if (strpos($html, '<input') !== false || strpos($html, '<textarea') !== false || strpos($html, '<select') !== false) {
            $this->assertTrue(
                strpos($html, '<label') !== false || strpos($html, 'aria-label') !== false,
                'Form inputs missing labels or aria-label attributes'
            );
        }
    }

    /**
     * Assert responsive design compliance
     */
    protected function assertResponsiveDesign(string $html): void
    {
        // Check for responsive classes (Bootstrap or Tailwind)
        $responsivePatterns = [
            'col-', 'col-sm-', 'col-md-', 'col-lg-', 'col-xl-', // Bootstrap
            'sm:', 'md:', 'lg:', 'xl:', '2xl:', // Tailwind
            'flex', 'grid', // CSS Grid/Flexbox
        ];
        
        $hasResponsive = false;
        foreach ($responsivePatterns as $pattern) {
            if (strpos($html, $pattern) !== false) {
                $hasResponsive = true;
                break;
            }
        }
        
        $this->assertTrue($hasResponsive, 'Generated HTML lacks responsive design classes');
    }
}