<?php

namespace Unit\Services\ExchangeRateProvider;

use App\Services\ExchangeRateProvider\ExchangeRateApiProvider;
use Exception;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class ExchangeRateApiProviderTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testGetExchangeRatesSuccess()
    {
        $mock = $this->getMockBuilder(ExchangeRateApiProvider::class)
            ->onlyMethods(['getFileContents'])
            ->getMock();

        $mockedResponse = json_encode([
            'conversion_rates' => [
                'USD' => 1.12,
                'GBP' => 0.85,
            ],
        ]);

        $mock->expects($this->once())
            ->method('getFileContents')
            ->with(ExchangeRateApiProvider::URL)
            ->willReturn($mockedResponse);

        $result = $mock->getExchangeRates();

        $this->assertArrayHasKey('USD', $result);
        $this->assertEquals(1.12, $result['USD']);
        $this->assertArrayHasKey('GBP', $result);
        $this->assertEquals(0.85, $result['GBP']);
    }

    public function testGetExchangeRatesThrowsExceptionIfUrlIsEmpty()
    {
        $mock = $this->getMockBuilder(ExchangeRateApiProvider::class)
            ->onlyMethods(['getUrl'])
            ->getMock();

        $mock->expects($this->once())
            ->method('getUrl')
            ->willReturn('');

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('URL is required');

        $mock->getExchangeRates();
    }

    /**
     * @throws Exception
     */
    public function testGetExchangeRatesThrowsRuntimeExceptionIfRequestFails()
    {
        $mock = $this->getMockBuilder(ExchangeRateApiProvider::class)
            ->onlyMethods(['getFileContents'])
            ->getMock();

        $mock->expects($this->once())
            ->method('getFileContents')
            ->with(ExchangeRateApiProvider::URL)
            ->willReturn(false);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unable to fetch data from ' . ExchangeRateApiProvider::URL);

        $mock->getExchangeRates();
    }

    public function testGetExchangeRatesReturnsEmptyArrayIfNoConversionRates()
    {
        $mock = $this->getMockBuilder(ExchangeRateApiProvider::class)
            ->onlyMethods(['getFileContents'])
            ->getMock();

        $mockedResponse = json_encode([]);

        $mock->expects($this->once())
            ->method('getFileContents')
            ->with(ExchangeRateApiProvider::URL)
            ->willReturn($mockedResponse);

        $result = $mock->getExchangeRates();

        $this->assertEmpty($result);
    }
}