<?php

namespace JakubFilip\Tpay\Tests\Templates;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use JakubFilip\Tpay\Templates\TemplateRenderer;
use InvalidArgumentException;
use RuntimeException;
use stdClass;

class TemplateRendererTest extends TestCase
{
    private string $nonExistentTemplatesFixturesDirectory;
    private string $existingTemplateName = 'test_template';
    private string $nonExistentTemplateName = 'non_existent_template';
    private TemplateRenderer $templateRenderer;

    protected function setUp(): void
    {
        parent::setUp();

        $templatesFixturesDirectory = __DIR__ . '/../fixtures/templates/';
        $this->nonExistentTemplatesFixturesDirectory = $templatesFixturesDirectory . 'non_existent_directory/';
        $this->templateRenderer = new TemplateRenderer($templatesFixturesDirectory);
    }

    #[Test]
    public function throwsInvalidArgumentExceptionWhenTemplatesDirectoryIsEmpty(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new TemplateRenderer(' ');
    }

    #[Test]
    public function throwsRuntimeExceptionWhenTemplatesDirectoryDoesNotExist(): void
    {
        $this->expectException(RuntimeException::class);

        new TemplateRenderer($this->nonExistentTemplatesFixturesDirectory);
    }

    #[Test]
    public function throwsInvalidArgumentExceptionWhenTemplateNameIsEmpty(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->templateRenderer->render(' ');
    }

    #[Test]
    public function throwsRuntimeExceptionWhenTemplateFileDoesNotExist(): void
    {
        $this->expectException(RuntimeException::class);

        $this->templateRenderer->render($this->nonExistentTemplateName);
    }

    #[Test]
    public function throwsInvalidArgumentExceptionWhenTemplateParametersAreInvalidType(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->templateRenderer->render($this->existingTemplateName, [
            'testTemplateParameter' => new stdClass(),
        ]);
    }

    #[Test]
    public function returnsTemplateContent(): void
    {
        $templateContent = $this->templateRenderer->render($this->existingTemplateName);

        $this->assertEquals('<h1>test template</h1><h2>[#testTemplateParameter#]</h2>', $templateContent);
    }

    #[Test]
    public function returnsTemplateContentWithParameters(): void
    {
        $templateContent = $this->templateRenderer->render($this->existingTemplateName, [
            'testTemplateParameter' => 'test template parameter value'
        ]);

        $this->assertEquals('<h1>test template</h1><h2>test template parameter value</h2>', $templateContent);
    }
}
