<?php

namespace Ua0leg\Novaposhta;

use Ua0leg\Novaposhta\Exceptions\NovaPoshtaException;

interface NovaPoshtaApiInterface
{
    /**
     * @throws NovaPoshtaException
     */
    public function searchSettlements(string $cityName, int $limit = 50, int $page = 1): array;

    /**
     * @throws NovaPoshtaException
     */
    public function getWarehouses(
        ?string $cityName = null,
        ?string $cityRef = null,
        ?string $findByString = null,
        int     $page = 1,
        int     $limit = 50,
        string  $language = 'UA',
        ?string $typeOfWarehouseRef = null,
        ?string $warehouseId = null
    ): array;

    /**
     * Отримання контактних осіб контрагента
     * @param string $ref Ref контрагента
     * @param int $page Номер сторінки (за замовчуванням 1)
     * @return array
     * @throws NovaPoshtaException
     */
    public function getCounterpartyContactPersons(string $ref, int $page = 1): array;

    /**
     * Отримання адрес контрагента
     * @param string $ref Ref контрагента
     * @param string $counterpartyProperty Тип контрагента ('Sender', 'Recipient' або 'ThirdPerson')
     * @return array
     * @throws NovaPoshtaException
     */
    public function getCounterpartyAddresses(string $ref, string $counterpartyProperty): array;

    /**
     * Створення інтернет-документа (накладної)
     *
     * @param array $mainData Основні дані накладної
     * @param array $backwardDeliveryData Дані зворотної доставки (optional)
     * @param array $optionsSeat Параметри місць (optional)
     * @return array
     * @throws NovaPoshtaException
     */
    public function createInternetDocument(
        array $mainData,
        array $backwardDeliveryData = [],
        array $optionsSeat = []
    ): array;


}