<?php

namespace ua0leg\novaposhta;

use Exception;

class NovaPoshtaApi
{
    private $apiKey;
    private $apiUrl = 'https://api.novaposhta.ua/v2.0/json/';

    public function __construct($apiKey)
    {
        $this->apiKey = $apiKey;
    }

    private function sendRequest($requestData)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestData));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new Exception("cURL Error: " . $error);
        }

        return json_decode($response, true);
    }

    /**
     * Пошук населених пунктів
     *
     * @param string $cityName Назва населеного пункту для пошуку
     * @param int $limit Ліміт результатів на сторінку
     * @param int $page Номер сторінки
     * @return array
     * @throws Exception
     */
    public function searchSettlements($cityName, $limit = 50, $page = 1)
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
            throw new Exception('API Error: ' . implode(', ', $response['errors']));
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
     * @throws Exception
     */
    public function getWarehouses(
        $cityName = null,
        $cityRef = null,
        $findByString = null,
        $page = 1,
        $limit = 50,
        $language = 'UA',
        $typeOfWarehouseRef = null,
        $warehouseId = null
    )
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
            throw new Exception('API Error: ' . implode(', ', $response['errors']));
        }

        return $response['data'];
    }
}
