<?php

if (!defined('WHMCS')) {
    exit('This file cannot be accessed directly.');
}

require_once __DIR__ . DIRECTORY_SEPARATOR . 'tpay' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

function tpay_MetaData(): array
{
    return [
        'DisplayName' => 'Tpay',
        'APIVersion' => '1.1',
    ];
}

function tpay_config(): array
{
    return [
        'FriendlyName' => [
            'Type' => 'System',
            'Value' => 'Tpay',
        ],
    ];
}

function tpay_link(array $params): string
{
    return <<<HTML
<button type="button" class="btn btn-primary">Pay Now</button>
HTML;

}