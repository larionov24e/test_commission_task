<?php

namespace App\Services\BinListProvider;

use RuntimeException;

/**
 * Class HandyapiProvider
 * https://www.handyapi.com/bin-list
 */
class HandyapiProvider extends BinProviderAbstract
{
    protected const string URL = 'https://data.handyapi.com/bin/';

    protected function fetchData(string $bin): array
    {
        $response = $this->getFileContents($this->getUrl() . $bin);

        if ($response === false) {
            throw new RuntimeException("Unable to fetch data from " . $this->getUrl() . $bin);
        }

        return json_decode($response, true)??[];
    }

    protected function getFileContents(string $url): false|string
    {
        return @file_get_contents($url);
    }

    public function getCountryName(): ?string
    {
        return $this->binDetails['Country']['A2']??null;
    }
}