<?php

namespace JakubFilip\Tpay\Tests\Api;

use JakubFilip\Tpay\Api\TpayClient;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use InvalidArgumentException;

class TpayClientTest extends TestCase
{
    #[Test]
    public function throwsInvalidArgumentExceptionWhenClientIdIsEmpty(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new TpayClient(' ', 'client_secret', true, null);
    }

    #[Test]
    public function throwsInvalidArgumentExceptionWhenClientSecretIsEmpty(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new TpayClient('client_id', ' ', true, null);
    }
}
