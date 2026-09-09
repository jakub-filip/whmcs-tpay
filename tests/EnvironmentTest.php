<?php

namespace JakubFilip\Tpay\Tests;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class EnvironmentTest extends TestCase
{
    #[Test]
    public function phpVersionIsSupported(): void
    {
        $this->assertTrue(
            version_compare(PHP_VERSION, '8.2.0', '>='),
            'The module requires PHP version 8.2.0 or higher. Current version: ' . PHP_VERSION . '.'
        );
    }
}
