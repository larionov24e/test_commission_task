<?php

namespace App\Services\ExchangeRateProvider;

use Exception;
use RuntimeException;

/**
 * Class ExchangeRateApiProvider
 * https://v6.exchangerate-api.com/
 * https://www.exchangerate-api.com/docs/overview
 */
class ExchangeRateApiProvider extends ExchangeRateProviderAbstract
{

    public const string URL = 'https://v6.exchangerate-api.com/v6/00b07507763b54a2cdd2ba22/latest/EUR';

    /**
     * @throws Exception
     */
    public function getExchangeRates(): array
    {
        if (empty($this->getUrl())) {
            throw new Exception('URL is required');
        }

        $response = $this->getFileContents($this->getUrl());

        if ($response === false) {
            throw new RuntimeException("Unable to fetch data from " . $this->getUrl());
        }

        return json_decode($response, true)['conversion_rates']??[];
    }

    protected function getFileContents(string $getUrl): false|string
    {
        return @file_get_contents($getUrl);
    }
}