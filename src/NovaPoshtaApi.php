<?php

namespace Ua0leg\Novaposhta;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Ua0leg\Novaposhta\Exceptions\NovaPoshtaException;

class NovaPoshtaApi implements NovaPoshtaApiInterface
{
    private string $apiKey;
    private string $apiUrl = 'https://api.novaposhta.ua/v2.0/json/';
    private ClientInterface $httpClient;
    private RequestFactoryInterface $requestFactory;
    private StreamFactoryInterface $streamFactory;

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

    public function __construct(
        string $apiKey,
        ?ClientInterface $httpClient = null,
        ?RequestFactoryInterface $requestFactory = null,
        ?StreamFactoryInterface $streamFactory = null
    ) {
        $this->apiKey = $apiKey;
        $this->httpClient = $httpClient ?? $this->createDefaultHttpClient();
        $this->requestFactory = $requestFactory ?? $this->createDefaultRequestFactory();
        $this->streamFactory = $streamFactory ?? $this->createDefaultStreamFactory();
    }

    protected function sendRequest(array $requestData): array
    {
        try {
            $request = $this->requestFactory->createRequest(
                'POST',
                $this->apiUrl
            )->withHeader('Content-Type', 'application/json');

            $stream = $this->streamFactory->createStream(
                json_encode($requestData)
            );

            $request = $request->withBody($stream);

            $response = $this->httpClient->sendRequest($request);

            if ($response->getStatusCode() !== 200) {
                throw new NovaPoshtaException(
                    "HTTP error: " . $response->getStatusCode()
                );
            }

            $responseData = json_decode($response->getBody(), true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new NovaPoshtaException(
                    "JSON decode error: " . json_last_error_msg()
                );
            }

            return $responseData;

        } catch (\Psr\Http\Client\ClientExceptionInterface $e) {
            throw new NovaPoshtaException(
                "HTTP client error: " . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    private function createDefaultHttpClient(): ClientInterface
    {
        return new class implements ClientInterface {
            public function sendRequest(\Psr\Http\Message\RequestInterface $request): \Psr\Http\Message\ResponseInterface
            {
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, (string)$request->getUri());
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $request->getHeaders());
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, (string)$request->getBody());
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

                $response = curl_exec($ch);
                $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $error = curl_error($ch);
                curl_close($ch);

                if ($error) {
                    throw new \RuntimeException($error);
                }

                return new \GuzzleHttp\Psr7\Response(
                    $statusCode,
                    [],
                    $response
                );
            }
        };
    }

    private function createDefaultRequestFactory(): RequestFactoryInterface
    {
        return new class implements RequestFactoryInterface {
            public function createRequest(string $method, $uri): \Psr\Http\Message\RequestInterface
            {
                return new \GuzzleHttp\Psr7\Request($method, $uri);
            }
        };
    }

    private function createDefaultStreamFactory(): StreamFactoryInterface
    {
        return new class implements StreamFactoryInterface {
            public function createStream(string $content = ''): \Psr\Http\Message\StreamInterface
            {
                return \GuzzleHttp\Psr7\Utils::streamFor($content);
            }

            public function createStreamFromFile(string $filename, string $mode = 'r'): \Psr\Http\Message\StreamInterface
            {
                return \GuzzleHttp\Psr7\Utils::streamFor(fopen($filename, $mode));
            }

            public function createStreamFromResource($resource): \Psr\Http\Message\StreamInterface
            {
                return \GuzzleHttp\Psr7\Utils::streamFor($resource);
            }
        };
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
        if (empty($cityName)) {
            throw new \InvalidArgumentException('City name cannot be empty');
        }

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
     * @param string|null $cityName Назва міста
     * @param string|null $cityRef Ref міста
     * @param string|null $findByString Пошуковий рядок
     * @param int $page Номер сторінки
     * @param int $limit Ліміт результатів
     * @param string $language Мова
     * @param string|null $typeOfWarehouseRef Тип відділення
     * @param string|null $warehouseId ID відділення
     * @return array
     * @throws NovaPoshtaException
     */
    public function getWarehouses(
        ?string $cityName = null,
        ?string $cityRef = null,
        ?string $findByString = null,
        int $page = 1,
        int $limit = 50,
        string $language = 'UA',
        ?string $typeOfWarehouseRef = null,
        ?string $warehouseId = null
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
