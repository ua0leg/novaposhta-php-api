<?php

namespace Ua0leg\Novaposhta;

use Ua0leg\Novaposhta\Exceptions\NovaPoshtaException;

class NovaPoshtaApi implements NovaPoshtaApiInterface
{
    private string $apiKey;
    private string $apiUrl = 'https://api.novaposhta.ua/v2.0/json/';

    // Типи контрагента
    const COUNTERPARTY_SENDER = 'Sender';
    const COUNTERPARTY_RECIPIENT = 'Recipient';
    const COUNTERPARTY_THIRD_PERSON = 'ThirdPerson';

    // Типи оплати
    const PAYER_TYPE_SENDER = 'Sender';
    const PAYER_TYPE_RECIPIENT = 'Recipient';

    // Типи доставки
    const SERVICE_TYPE_DOORS_WAREHOUSE = 'DoorsWarehouse';
    const SERVICE_TYPE_WAREHOUSE_WAREHOUSE = 'WarehouseWarehouse';

    // Типи вантажу
    const CARGO_TYPE_CARGO = 'Cargo';
    const CARGO_TYPE_DOCUMENTS = 'Documents';
    const CARGO_TYPE_PALLET = 'Pallet';

    public function __construct($apiKey)
    {
        $this->apiKey = $apiKey;
    }

    /**
     * Відправляє запит до API Нової Пошти
     *
     * @param array $requestData Дані для запиту
     * @return array
     * @throws NovaPoshtaException
     */
    private function sendRequest(array $requestData): array
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestData));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        $curlError = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // Обробка помилок cURL
        if ($curlError) {
            throw new NovaPoshtaException(
                "cURL Error: {$curlError}",
                $httpCode ?: 500
            );
        }

        // Декодування JSON
        $decodedResponse = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new NovaPoshtaException(
                "JSON decode error: " . json_last_error_msg(),
                500
            );
        }

        // Обробка помилок API Нової Пошти
        if (empty($decodedResponse['success'])) {
            $errorMessage = !empty($decodedResponse['errors'])
                ? implode(', ', $decodedResponse['errors'])
                : 'Unknown API error';

            throw new NovaPoshtaException(
                "API Error: {$errorMessage}",
                $httpCode,
                $decodedResponse['errorCodes'][0] ?? null
            );
        }

        return $decodedResponse;
    }

    /**
     * Пошук населених пунктів
     *
     * @param string $cityName Назва населеного пункту для пошуку
     * @param int $limit Ліміт результатів на сторінку
     * @param int $page Номер сторінки
     * @return array
     * @throws NovaPoshtaException
     */
    public function searchSettlements(string $cityName, int $limit = 50, int $page = 1): array
    {
        $requestData = [
            'apiKey'           => $this->apiKey,
            'modelName'        => 'AddressGeneral',
            'calledMethod'     => 'searchSettlements',
            'methodProperties' => [
                'CityName' => $cityName,
                'Limit'    => $limit,
                'Page'     => $page
            ]
        ];

        $response = $this->sendRequest($requestData);

        if (!$response['success']) {
            throw new NovaPoshtaException(
                "API Error: " . implode(', ', $response['errors']),
                500
            );
        }

        return $response['data'];
    }

    /**
     * Отримання списку відділень/поштоматів
     *
     * @param string|null $cityName Назва міста (необов'язково)
     * @param string|null $cityRef Ref міста (необов'язково)
     * @param string|null $findByString Пошуковий рядок (необов'язково)
     * @param int $page Номер сторінки (за замовчуванням 1)
     * @param int $limit Ліміт результатів (за замовчуванням 50)
     * @param string $language Мова (UA/RU, за замовчуванням UA)
     * @param string|null $typeOfWarehouseRef Тип відділення (необов'язково)
     * @param string|null $warehouseId ID відділення (необов'язково)
     * @return array
     * @throws NovaPoshtaException
     */
    public function getWarehouses(
        string $cityName = null,
        string $cityRef = null,
        string $findByString = null,
        int    $page = 1,
        int    $limit = 50,
        string $language = 'UA',
        string $typeOfWarehouseRef = null,
        string $warehouseId = null
    ): array
    {
        $methodProperties = [
            'Page'     => $page,
            'Limit'    => $limit,
            'Language' => $language
        ];

        // Додаємо необов'язкові параметри, якщо вони вказані
        if ($cityName !== null) $methodProperties['CityName'] = $cityName;
        if ($cityRef !== null) $methodProperties['CityRef'] = $cityRef;
        if ($findByString !== null) $methodProperties['FindByString'] = $findByString;
        if ($typeOfWarehouseRef !== null) $methodProperties['TypeOfWarehouseRef'] = $typeOfWarehouseRef;
        if ($warehouseId !== null) $methodProperties['WarehouseId'] = $warehouseId;

        $requestData = [
            'apiKey'           => $this->apiKey,
            'modelName'        => 'AddressGeneral',
            'calledMethod'     => 'getWarehouses',
            'methodProperties' => $methodProperties
        ];

        $response = $this->sendRequest($requestData);

        if (!$response['success']) {
            throw new NovaPoshtaException(
                "API Error: " . implode(', ', $response['errors']),
                500
            );
        }

        return $response['data'];
    }

    public function getCounterpartyContactPersons(string $ref, int $page = 1): array
    {
        $requestData = [
            'apiKey'           => $this->apiKey,
            'modelName'        => 'CounterpartyGeneral',
            'calledMethod'     => 'getCounterpartyContactPersons',
            'methodProperties' => [
                'Ref'  => $ref,
                'Page' => $page
            ]
        ];

        $response = $this->sendRequest($requestData);

        if (empty($response['data'])) {
            throw new NovaPoshtaException('Не вдалося отримати контактних осіб');
        }

        return $response['data'];
    }

    public function getCounterpartyAddresses(string $ref, string $counterpartyProperty = 'Sender'): array
    {
        $validProperties = ['Sender', 'Recipient', 'ThirdPerson'];

        if (!in_array($counterpartyProperty, $validProperties)) {
            throw new NovaPoshtaException(
                "Невірний тип контрагента. Допустимі значення: " .
                implode(', ', $validProperties)
            );
        }

        $requestData = [
            'apiKey'           => $this->apiKey,
            'modelName'        => 'CounterpartyGeneral',
            'calledMethod'     => 'getCounterpartyAddresses',
            'methodProperties' => [
                'Ref'                  => $ref,
                'CounterpartyProperty' => $counterpartyProperty
            ]
        ];

        $response = $this->sendRequest($requestData);

        if (empty($response['data'])) {
            throw new NovaPoshtaException('Не вдалося отримати адреси контрагента');
        }

        return $response['data'];
    }

    /**

     */
    public function createInternetDocument(
        array $mainData,
        array $backwardDeliveryData = [],
        array $optionsSeat = []
    ): array
    {
        // Валідація обов'язкових полів
        $requiredParams = [
            'PayerType', 'PaymentMethod', 'CargoType', 'Weight',
            'ServiceType', 'CitySender', 'Sender', 'SenderAddress',
            'ContactSender', 'SendersPhone', 'CityRecipient', 'Recipient',
            'RecipientAddress', 'ContactRecipient', 'RecipientsPhone'
        ];

        foreach ($requiredParams as $param) {
            if (!isset($mainData[$param])) {
                throw new NovaPoshtaException("Відсутній обов'язковий параметр: $param");
            }
        }

        // Формування запиту
        $requestData = [
            'apiKey'           => $this->apiKey,
            'modelName'        => 'InternetDocumentGeneral',
            'calledMethod'     => 'save',
            'methodProperties' => array_merge([
                'DateTime'             => $mainData['DateTime'] ?? date('d.m.Y'),
                'NewAddress'           => $mainData['NewAddress'] ?? 1,
                'SeatsAmount'          => $mainData['SeatsAmount'] ?? count($optionsSeat) ?: 1,
                'Description'          => $mainData['Description'] ?? '',
                'Cost'                 => $mainData['Cost'] ?? 0,
                'OptionsSeat'          => $optionsSeat,
                'BackwardDeliveryData' => $backwardDeliveryData
            ], $mainData)
        ];

        $response = $this->sendRequest($requestData);

        if (empty($response['data'][0]['Ref'])) {
            throw new NovaPoshtaException(
                'Помилка створення накладної: ' .
                ($response['errors'][0] ?? 'Невідома помилка')
            );
        }

        return $response['data'][0];
    }
}
