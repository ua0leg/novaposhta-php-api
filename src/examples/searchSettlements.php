<?php

use Ua0leg\Novaposhta\Exceptions\NovaPoshtaException;
use Ua0leg\Novaposhta\NovaPoshtaApi;

$apiKey = 'API_KEY';
$api = new NovaPoshtaApi($apiKey);

try {
    // Пошук населених пунктів
    $settlements = $api->searchSettlements('київ', 50, 2);
    print_r($settlements);
} catch (NovaPoshtaException $e) {
    echo 'Помилка: ' . $e->getMessage();
}
