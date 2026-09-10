<?php

namespace JakubFilip\Tpay\Templates;

use InvalidArgumentException;
use RuntimeException;

class TemplateRenderer
{
    private string $templatesDirectory;

    public function __construct(string $templatesDirectory)
    {
        $directory = trim($templatesDirectory);

        if ($directory === '') {
            throw new InvalidArgumentException('Templates directory cannot be empty');
        }

        $normalizedDirectory = $this->normalizeDirectory($directory);

        if (!$this->directoryExistsAndReadable($normalizedDirectory)) {
            throw new RuntimeException('Templates directory does not exist or is not readable');
        }

        $this->templatesDirectory = $normalizedDirectory;
    }

    public function render(string $templateName, array $parameters = []): string
    {
        $template = trim($templateName);

        if ($template === '') {
            throw new InvalidArgumentException('Template name cannot be empty');
        }

        $normalizedTemplateName = $this->normalizeTemplateName($template);

        $templateFile = $this->templatesDirectory . $normalizedTemplateName . '.html';

        if (!$this->fileExistsAndReadable($templateFile)) {
            throw new RuntimeException('Template file does not exist or is not readable');
        }

        $templateContent = file_get_contents($templateFile);

        if ($templateContent === false) {
            throw new RuntimeException('Template content cannot be read');
        }

        foreach ($parameters as $key => $value) {
            if (!is_string($value) && !is_int($value) && !is_float($value)) {
                throw new InvalidArgumentException('Parameter value must be string, int or float');
            }

            $templateContent = str_replace(
                "[#" . $key . "#]",
                htmlspecialchars($value, ENT_QUOTES, 'UTF-8'),
                $templateContent
            );
        }

        return $templateContent;
    }

    private function normalizeDirectory(string $directory): string
    {
        return rtrim(str_replace('\\', '/', $directory), '/') . '/';
    }

    private function normalizeTemplateName(string $templateName): string
    {
        return trim(str_replace('\\', '/', $templateName), '/');
    }

    private function directoryExistsAndReadable(string $directory): bool
    {
        return file_exists($directory) && is_dir($directory) && is_readable($directory);
    }

    private function fileExistsAndReadable(string $file): bool
    {
        return file_exists($file) && is_file($file) && is_readable($file);
    }
}
