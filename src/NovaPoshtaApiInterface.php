<?php

namespace ua0leg\novaposhta;

interface NovaPoshtaApiInterface
{
    public function searchSettlements(string $cityName, int $limit = 50, int $page = 1): array;
    public function getWarehouses(
        ?string $cityName = null,
        ?string $cityRef = null,
        ?string $findByString = null,
        int $page = 1,
        int $limit = 50,
        string $language = 'UA',
        ?string $typeOfWarehouseRef = null,
        ?string $warehouseId = null
    ): array;
}