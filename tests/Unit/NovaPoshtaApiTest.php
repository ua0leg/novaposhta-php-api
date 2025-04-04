<?php

namespace Ua0leg\Novaposhta\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Ua0leg\Novaposhta\NovaPoshtaApi;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Psr7\Utils;

class NovaPoshtaApiTest extends TestCase
{
    /** @test */
    public function it_throws_exception_for_empty_city_name()
    {
        // 1. Підготовка моків
        $httpClient = $this->createMock(ClientInterface::class);
        $requestFactory = $this->createMock(RequestFactoryInterface::class);
        $streamFactory = $this->createMock(StreamFactoryInterface::class);
        $request = $this->createMock(RequestInterface::class);

        // 2. Створення реального потоку з даними
        $stream = Utils::streamFor(json_encode([
            'success' => true,
            'data'    => []
        ]));

        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);
        $response->method('getBody')->willReturn($stream);

        // 3. Налаштування моків
        $requestFactory->method('createRequest')
            ->willReturn($request);
        $streamFactory->method('createStream')
            ->willReturn($stream);
        $httpClient->method('sendRequest')
            ->willReturn($response);

        // 4. Створення екземпляра API
        $api = new NovaPoshtaApi(
            'fake_api_key',
            $httpClient,
            $requestFactory,
            $streamFactory
        );

        // 5. Перевірка винятку
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('City name cannot be empty');

        $api->searchSettlements('');
    }
}
