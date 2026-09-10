<?php

namespace JakubFilip\Tpay\Loggers;

class TpayLogger
{
    public function log(string $request, string $response): void
    {
        logModuleCall('Tpay', 'TpayLogger', $request, '', $response, []);
    }

    public function request(string $request, string $response): void
    {
        $this->log($request, $response);
    }
}
