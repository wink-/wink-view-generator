<?php

namespace Wink\ViewGenerator\Tests\Unit\Generators;

use Wink\ViewGenerator\Tests\TestCase;
use Wink\ViewGenerator\Generators\AbstractViewGenerator;
use Illuminate\Support\Facades\File;
use Mockery;

class AbstractViewGeneratorTest extends TestCase
{
    protected AbstractViewGenerator $generator;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a concrete implementation for testing
        $this->generator = new class extends AbstractViewGenerator {
            public function generate(string $table, array $modelData, array $options): array
            {
                return ['test' => 'result'];
            }

            // Expose protected methods for testing
            public function testGetTemplatePath(string $framework, string $type, string $template): string
            {
                return $this->getTemplatePath($framework, $type, $template);
            }

            public function testProcessTemplate(string $templatePath, array $variables = []): string
            {
                return $this->processTemplate($templatePath, $variables);
            }

            public function testGenerateViewFile(string $template, string $destination, array $variables = []): bool
            {
                return $this->generateViewFile($template, $destination, $variables);
            }
        };
    }

    /** @test */
    public function it_can_be_instantiated()
    {
        $this->assertInstanceOf(AbstractViewGenerator::class, $this->generator);
    }

    /** @test */
    public function it_generates_correct_template_path()
    {
        $templatePath = $this->generator->testGetTemplatePath('bootstrap', 'crud', 'index.blade.php');
        
        $expectedPath = __DIR__ . '/../../../src/Templates/bootstrap/crud/index.blade.php.stub';
        $this->assertEquals($expectedPath, $templatePath);
    }

    /** @test */
    public function it_generates_template_paths_for_different_frameworks()
    {
        $testCases = [
            ['bootstrap', 'crud', 'index.blade.php'],
            ['tailwind', 'components', 'form-field.blade.php'],
            ['custom', 'layouts', 'app.blade.php'],
        ];

        foreach ($testCases as [$framework, $type, $template]) {
            $templatePath = $this->generator->testGetTemplatePath($framework, $type, $template);
            
            $expectedPath = __DIR__ . "/../../../src/Templates/{$framework}/{$type}/{$template}.stub";
            $this->assertEquals($expectedPath, $templatePath);
        }
    }

    /** @test */
    public function it_processes_template_with_variables()
    {
        // Create a temporary template file
        $templatePath = $this->testFilesPath . '/test_template.stub';
        $templateContent = 'Hello {{ name }}, welcome to {{ app }}!';
        File::put($templatePath, $templateContent);

        $variables = [
            'name' => 'John',
            'app' => 'Laravel App',
        ];

        $result = $this->generator->testProcessTemplate($templatePath, $variables);
        
        $this->assertEquals('Hello John, welcome to Laravel App!', $result);
    }

    /** @test */
    public function it_processes_template_without_variables()
    {
        // Create a temporary template file
        $templatePath = $this->testFilesPath . '/test_template.stub';
        $templateContent = 'Static content without variables';
        File::put($templatePath, $templateContent);

        $result = $this->generator->testProcessTemplate($templatePath);
        
        $this->assertEquals('Static content without variables', $result);
    }

    /** @test */
    public function it_throws_exception_when_template_not_found()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Template not found:');

        $nonExistentPath = $this->testFilesPath . '/non_existent_template.stub';
        $this->generator->testProcessTemplate($nonExistentPath);
    }

    /** @test */
    public function it_processes_multiple_variable_replacements()
    {
        // Create a template with multiple occurrences of the same variable
        $templatePath = $this->testFilesPath . '/test_template.stub';
        $templateContent = '{{ name }} said "Hello {{ name }}!" to {{ friend }}.';
        File::put($templatePath, $templateContent);

        $variables = [
            'name' => 'Alice',
            'friend' => 'Bob',
        ];

        $result = $this->generator->testProcessTemplate($templatePath, $variables);
        
        $this->assertEquals('Alice said "Hello Alice!" to Bob.', $result);
    }

    /** @test */
    public function it_handles_templates_with_unused_variables()
    {
        // Template with variables that aren't provided
        $templatePath = $this->testFilesPath . '/test_template.stub';
        $templateContent = 'Hello {{ name }}, your score is {{ score }}!';
        File::put($templatePath, $templateContent);

        $variables = [
            'name' => 'John',
            // 'score' variable is missing
        ];

        $result = $this->generator->testProcessTemplate($templatePath, $variables);
        
        // Unused variable should remain as placeholder
        $this->assertEquals('Hello John, your score is {{ score }}!', $result);
    }

    /** @test */
    public function it_handles_templates_with_no_variables()
    {
        $templatePath = $this->testFilesPath . '/test_template.stub';
        $templateContent = 'No variables in this template.';
        File::put($templatePath, $templateContent);

        $variables = [
            'unused' => 'value',
        ];

        $result = $this->generator->testProcessTemplate($templatePath, $variables);
        
        $this->assertEquals('No variables in this template.', $result);
    }

    /** @test */
    public function it_generates_view_file_successfully()
    {
        // Create a template file
        $templatePath = $this->testFilesPath . '/test_template.stub';
        $templateContent = 'Hello {{ name }}!';
        File::put($templatePath, $templateContent);

        // Generate view file
        $destinationPath = $this->testViewsPath . '/test_view.blade.php';
        $variables = ['name' => 'World'];

        $result = $this->generator->testGenerateViewFile($templatePath, $destinationPath, $variables);

        $this->assertTrue($result);
        $this->assertFileExists($destinationPath);
        $this->assertEquals('Hello World!', File::get($destinationPath));
    }

    /** @test */
    public function it_creates_directory_when_generating_view_file()
    {
        // Create a template file
        $templatePath = $this->testFilesPath . '/test_template.stub';
        $templateContent = 'Test content';
        File::put($templatePath, $templateContent);

        // Generate view file in a nested directory that doesn't exist
        $destinationPath = $this->testViewsPath . '/nested/deep/test_view.blade.php';

        $result = $this->generator->testGenerateViewFile($templatePath, $destinationPath);

        $this->assertTrue($result);
        $this->assertFileExists($destinationPath);
        $this->assertDirectoryExists(dirname($destinationPath));
    }

    /** @test */
    public function it_handles_view_file_generation_errors()
    {
        // Try to generate from non-existent template
        $templatePath = $this->testFilesPath . '/non_existent.stub';
        $destinationPath = $this->testViewsPath . '/test_view.blade.php';

        $result = $this->generator->testGenerateViewFile($templatePath, $destinationPath);

        $this->assertFalse($result);
        $this->assertFileDoesNotExist($destinationPath);
    }

    /** @test */
    public function it_overwrites_existing_view_files()
    {
        // Create a template file
        $templatePath = $this->testFilesPath . '/test_template.stub';
        $templateContent = 'New content';
        File::put($templatePath, $templateContent);

        // Create existing view file
        $destinationPath = $this->testViewsPath . '/test_view.blade.php';
        File::put($destinationPath, 'Old content');

        $result = $this->generator->testGenerateViewFile($templatePath, $destinationPath);

        $this->assertTrue($result);
        $this->assertEquals('New content', File::get($destinationPath));
    }

    /** @test */
    public function it_processes_complex_template_variables()
    {
        $templatePath = $this->testFilesPath . '/complex_template.stub';
        $templateContent = '@extends(\'{{ layout }}\')

@section(\'title\', \'{{ title }}\')

@section(\'content\')
<div class="container">
    <h1>{{ heading }}</h1>
    <p>{{ description }}</p>
    
    @if({{ condition }})
        <p>{{ conditional_content }}</p>
    @endif
</div>
@endsection';
        
        File::put($templatePath, $templateContent);

        $variables = [
            'layout' => 'layouts.app',
            'title' => 'Test Page',
            'heading' => 'Welcome',
            'description' => 'This is a test page.',
            'condition' => 'true',
            'conditional_content' => 'Condition is true!',
        ];

        $result = $this->generator->testProcessTemplate($templatePath, $variables);

        $this->assertStringContainsString('@extends(\'layouts.app\')', $result);
        $this->assertStringContainsString('@section(\'title\', \'Test Page\')', $result);
        $this->assertStringContainsString('<h1>Welcome</h1>', $result);
        $this->assertStringContainsString('<p>This is a test page.</p>', $result);
        $this->assertStringContainsString('@if(true)', $result);
        $this->assertStringContainsString('<p>Condition is true!</p>', $result);
    }

    /** @test */
    public function abstract_generate_method_must_be_implemented()
    {
        $concreteGenerator = new class extends AbstractViewGenerator {
            public function generate(string $table, array $modelData, array $options): array
            {
                return [
                    'table' => $table,
                    'model_data' => $modelData,
                    'options' => $options,
                ];
            }
        };

        $result = $concreteGenerator->generate('users', ['model' => 'User'], ['framework' => 'bootstrap']);

        $this->assertEquals('users', $result['table']);
        $this->assertEquals(['model' => 'User'], $result['model_data']);
        $this->assertEquals(['framework' => 'bootstrap'], $result['options']);
    }
}