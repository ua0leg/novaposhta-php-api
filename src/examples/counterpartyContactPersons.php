<?php

use Ua0leg\Novaposhta\Exceptions\NovaPoshtaException;
use Ua0leg\Novaposhta\NovaPoshtaApi;

$apiKey = 'API_KEY';
$api = new NovaPoshtaApi($apiKey);

try {
    $contactPersons = $api->getCounterpartyContactPersons('00000000-0000-0000-0000-000000000000');

    foreach ($contactPersons as $person) {
        echo "Контактна особа: {$person['Description']}\n";
        echo "Телефон: {$person['Phones']}\n";
        echo "Email: {$person['Email']}\n";
    }
} catch (NovaPoshtaException $e) {
    echo "Помилка: " . $e->getMessage();
}
