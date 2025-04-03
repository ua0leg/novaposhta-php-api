<?php

use Ua0leg\Novaposhta\NovaPoshtaApi;

$apiKey = 'API_KEY';
$api = new NovaPoshtaApi($apiKey);

// Отримати всі відділення у Києві
$warehouses = $api->getWarehouses('Київ');

// Пошук відділень за рядком
$warehouses = $api->getWarehouses(null, null, 'Грушевського');

// Отримати конкретне відділення за ID
$warehouse = $api->getWarehouses(null, null, null, 1, 1, 'UA', null, '151');

// Отримати поштомати (якщо знаємо TypeOfWarehouseRef)
$postmats = $api->getWarehouses('Київ', null, null, 1, 50, 'UA', 'тип_поштомата');
