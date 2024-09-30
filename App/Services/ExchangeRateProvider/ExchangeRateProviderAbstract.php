<?php

namespace App\Services\ExchangeRateProvider;

abstract class ExchangeRateProviderAbstract implements ExchangeRateProviderInterface
{
    public const string URL = '';

    abstract public function getExchangeRates(): array;

    public function getUrl(): string
    {
        return static::URL;
    }

}