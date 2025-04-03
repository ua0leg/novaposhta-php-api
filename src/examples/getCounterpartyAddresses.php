<?php

use Ua0leg\Novaposhta\Exceptions\NovaPoshtaException;
use Ua0leg\Novaposhta\NovaPoshtaApi;

$apiKey = 'API_KEY';
$api = new NovaPoshtaApi($apiKey);

try {
    $addresses = $api->getCounterpartyAddresses(
        '00000000-0000-0000-0000-000000000000',
        'Sender'
    );

    foreach ($addresses as $address) {
        echo "Адреса (Ref: {$address['Ref']}): {$address['Description']}\n";
    }
} catch (NovaPoshtaException $e) {
    echo "Помилка: " . $e->getMessage();
}
