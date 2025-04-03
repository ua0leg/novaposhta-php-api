<?php

use Ua0leg\Novaposhta\Exceptions\NovaPoshtaException;
use Ua0leg\Novaposhta\NovaPoshtaApi;

$apiKey = 'API_KEY';
$api = new NovaPoshtaApi($apiKey);

try {
    // Проста накладна без зворотної доставки
    $basicWaybill = $api->createInternetDocument([
        'PayerType'        => NovaPoshtaApi::PAYER_TYPE_SENDER,
        'PaymentMethod'    => 'Cash',
        'DateTime'         => date('d.m.Y'),
        'CargoType'        => NovaPoshtaApi::CARGO_TYPE_CARGO,
        'Weight'           => 0.5,
        'ServiceType'      => NovaPoshtaApi::SERVICE_TYPE_DOORS_WAREHOUSE,
        'Description'      => 'Додатковий опис відправлення',
        'Cost'             => 15000,
        'CitySender'       => '00000000-0000-0000-0000-000000000000',
        'Sender'           => '00000000-0000-0000-0000-000000000000',
        'SenderAddress'    => '00000000-0000-0000-0000-000000000000',
        'ContactSender'    => '00000000-0000-0000-0000-000000000000',
        'SendersPhone'     => '380660000000',
        'CityRecipient'    => '00000000-0000-0000-0000-000000000000',
        'Recipient'        => '00000000-0000-0000-0000-000000000000',
        'RecipientAddress' => '00000000-0000-0000-0000-000000000000',
        'ContactRecipient' => '00000000-0000-0000-0000-000000000000',
        'RecipientsPhone'  => '380660000000'
    ]);

    // Накладна зі зворотною доставкою
    $waybill = $api->createInternetDocument(
        [
            'PayerType'        => NovaPoshtaApi::PAYER_TYPE_SENDER,
            'PaymentMethod'    => 'Cash',
            'DateTime'         => date('d.m.Y'),
            'CargoType'        => NovaPoshtaApi::CARGO_TYPE_CARGO,
            'Weight'           => 0.5,
            'ServiceType'      => NovaPoshtaApi::SERVICE_TYPE_DOORS_WAREHOUSE,
            'Description'      => 'Додатковий опис відправлення',
            'Cost'             => 15000,
            'CitySender'       => '00000000-0000-0000-0000-000000000000',
            'Sender'           => '00000000-0000-0000-0000-000000000000',
            'SenderAddress'    => '00000000-0000-0000-0000-000000000000',
            'ContactSender'    => '00000000-0000-0000-0000-000000000000',
            'SendersPhone'     => '380660000000',
            'CityRecipient'    => '00000000-0000-0000-0000-000000000000',
            'Recipient'        => '00000000-0000-0000-0000-000000000000',
            'RecipientAddress' => '00000000-0000-0000-0000-000000000000',
            'ContactRecipient' => '00000000-0000-0000-0000-000000000000',
            'RecipientsPhone'  => '380660000000'
        ],
        // Зворотня доставка
        [
            [
                'PayerType'        => NovaPoshtaApi::PAYER_TYPE_RECIPIENT,
                'CargoType'        => 'Money',
                'RedeliveryString' => '4552'
            ]
        ],
        // Параметри місць
        [
            [
                'volumetricVolume' => 1,
                'volumetricWidth'  => 30,
                'volumetricLength' => 30,
                'volumetricHeight' => 30,
                'weight'           => 20
            ]
        ]
    );

    echo "Накладна створена! Номер: {$waybill['IntDocNumber']}";
    echo "Ref: {$waybill['Ref']}";
    echo "Орієнтовна дата доставки: {$waybill['EstimatedDeliveryDate']}";

} catch (NovaPoshtaException $e) {
    echo "Помилка: " . $e->getMessage();
}
