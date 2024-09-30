<?php

namespace Unit\Services;

use App\Services\BinListProvider\BinProviderAbstract;
use App\Services\CommissionCalculator;
use App\Services\ExchangeRateProvider\ExchangeRateProviderAbstract;
use App\Services\TransactionService;
use App\Helpers\ConfigHelper;
use App\Helpers\CountryRecognizerHelper;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

class CommissionCalculatorTest extends TestCase
{
    private $exchangeRateProviderMock;
    private $transactionServiceMock;
    private $binListProviderMock;
    private $calculator;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->exchangeRateProviderMock = $this->createMock(ExchangeRateProviderAbstract::class);
        $this->transactionServiceMock = $this->createMock(TransactionService::class);
        $this->binListProviderMock = $this->createMock(BinProviderAbstract::class);

        $this->calculator = new CommissionCalculator(
            $this->exchangeRateProviderMock,
            $this->transactionServiceMock,
            $this->binListProviderMock
        );

        $configHelper = $this->createMock(ConfigHelper::class);

        $configHelper->method('get')->with('currency')->willReturn(['base' => 'EUR']);
    }

    /**
     * @throws Exception
     * @throws \Exception
     */
    public function testCalculateReturnsCorrectCommissionForEuCountry()
    {
        $transactions = [
            ['bin' => '45717360', 'amount' => 100.00, 'currency' => 'EUR']
        ];

        $exchangeRates = ['EUR' => 1.00];
        $binDetailsMock = $this->createMock(BinProviderAbstract::class);
        $countyRecognizerHelper = $this->createMock(CountryRecognizerHelper::class);

        $binDetailsMock->expects($this->once())
        ->method('getCountryName')
        ->willReturn('DE');

        $this->transactionServiceMock->method('getTransactions')->willReturn($transactions);

        $this->exchangeRateProviderMock->expects($this->once())
        ->method('getExchangeRates')
        ->willReturn($exchangeRates);

        $this->binListProviderMock->expects($this->once())
        ->method('getBinDetails')->with('45717360')
        ->willReturn($binDetailsMock);

        $countyRecognizerHelper->method('isEuropeanCountry')->with('DE')->willReturn(true);

        $result = $this->calculator->calculate();

        $this->assertEquals([1.00], $result);
    }

    /**
     * @throws \Exception|Exception
     */
    public function testCalculateReturnsCorrectCommissionForNonEuCountry()
    {
        $transactions = [
            ['bin' => '45717360', 'amount' => 100.00, 'currency' => 'USD']
        ];

        $exchangeRates = ['USD' => 1.2];

        $binDetailsMock = $this->createMock(BinProviderAbstract::class);
        $binDetailsMock->method('getCountryName')->willReturn('US');
        $this->transactionServiceMock->method('getTransactions')->willReturn($transactions);
        $this->exchangeRateProviderMock->method('getExchangeRates')->willReturn($exchangeRates);
        $this->binListProviderMock->method('getBinDetails')->willReturn($binDetailsMock);

        $binDetailsMock = $this->createMock(CountryRecognizerHelper::class);
        $binDetailsMock->method('isEuropeanCountry')->with('US')->willReturn(false);

        $result = $this->calculator->calculate();

        $this->assertEquals([1.67], $result);
    }

    /**
     * @throws Exception
     */
    public function testCalculateThrowsExceptionWhenExchangeRateNotFound()
    {
        $transactions = [
            ['bin' => '45717360', 'amount' => 100.00, 'currency' => 'JPY']
        ];

        $exchangeRates = [];
        $binDetailsMock = $this->createMock(BinProviderAbstract::class);
        $binDetailsMock->method('getCountryName')->willReturn('JP');

        $this->transactionServiceMock->method('getTransactions')->willReturn($transactions);
        $this->exchangeRateProviderMock->method('getExchangeRates')->willReturn($exchangeRates);
        $this->binListProviderMock->method('getBinDetails')->willReturn($binDetailsMock);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Failed to fetch exchange rate');

        $this->calculator->calculate();
    }
}