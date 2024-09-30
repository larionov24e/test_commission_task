<?php

namespace App\Services\BinListProvider;

abstract class BinProviderAbstract implements BinListProviderInterface
{
    protected const string URL = '';
    protected array $binDetails;

    public function getBinDetails(string $bin): BinListProviderInterface
    {
        $binData = $this->fetchData($bin);

        $this->setBinDetails($binData);

        return $this;
    }

    abstract public function getCountryName(): ?string;

    abstract protected function fetchData(string $bin): ?array;

    public function getUrl(): string
    {
        return static::URL;
    }

    protected function setBinDetails(mixed $json_decode): void
    {
        $this->binDetails = $json_decode;
    }

}