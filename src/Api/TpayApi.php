<?php

namespace JakubFilip\Tpay\Api;

use stdClass;

readonly class TpayApi
{
    public function __construct(private TpayClient $tpayClient)
    {
    }

    public function createTransaction(array $data): stdClass
    {
        return $this->tpayClient->post('transactions', $data);
    }

    public function getTransactionDetails(string $transactionId): stdClass
    {
        return $this->tpayClient->get('transactions/' . $transactionId);
    }

    public function refundTransaction(string $transactionId, array $data): stdClass
    {
        return $this->tpayClient->post('transactions/' . $transactionId . '/refund', $data);
    }
}
