<?php

namespace App\Services;

use App\Helpers\ConfigHelper;
use App\Helpers\CountryRecognizerHelper;
use App\Services\BinListProvider\BinListProviderInterface;
use App\Services\ExchangeRateProvider\ExchangeRateProviderInterface;
use Exception;

class CommissionCalculator implements Calculator
{
    /**
     * @var ExchangeRateProviderInterface
     */
    private ExchangeRateProviderInterface $exchangeRateService;

    /**
     * @var TransactionService
     */
    private TransactionServiceInterface $transactionService;

    /**
     * @var BinListProviderInterface
     */
    private BinListProviderInterface $binListProvider;
    private ConfigHelper $configHelper;
    private CountryRecognizerHelper $countryRecognizerHelper;

    public function __construct(
        ExchangeRateProviderInterface $exchangeRateProvider,
        TransactionServiceInterface $transactionService,
        BinListProviderInterface $binListProvider
    )
    {
        $this->exchangeRateService = $exchangeRateProvider;
        $this->transactionService = $transactionService;
        $this->binListProvider = $binListProvider;

        $this->configHelper = new ConfigHelper();
        $this->countryRecognizerHelper = new CountryRecognizerHelper();
    }

    /**
     * @throws Exception
     */
    public function calculate(): ?array
    {
        $transactions = $this->transactionService->getTransactions();
        $exchangeRates = $this->exchangeRateService->getExchangeRates();
        $baseCurrency = $this->getBaseCurrency();

        $results = [];

        foreach ($transactions as $transaction) {
            $binDetails = $this->binListProvider->getBinDetails($transaction['bin']);
            $rate = $this->getExchangeRate($transaction['currency'], $exchangeRates);

            $amountFixed = $this->convertAmountToBaseCurrency($transaction['amount'], $transaction['currency'], $rate, $baseCurrency);
            $isEuCountry = $this->isEuropeanCountry($binDetails->getCountryName());

            $commission = $this->calculateCommission($amountFixed, $isEuCountry);
            $results[] = $this->roundUpCommission($commission);
        }

        return $results;
    }

    private function getBaseCurrency(): array
    {
        return $this->configHelper->get('currency');
    }

    /**
     * @throws Exception
     */
    private function getExchangeRate(string $currency, array $exchangeRates): float
    {
        if (empty($exchangeRates[$currency])) {
            throw new Exception('Failed to fetch exchange rate');
        }

        return $exchangeRates[$currency];
    }

    private function convertAmountToBaseCurrency(float $amount, string $currency, float $rate, array $baseCurrency): float
    {
        return ($currency === $baseCurrency['base']) ? $amount : $amount / $rate;
    }

    private function isEuropeanCountry(string $countryName): bool
    {
        return $this->countryRecognizerHelper->isEuropeanCountry($countryName);
    }

    private function calculateCommission(float $amountFixed, bool $isEuCountry): float
    {
        return $amountFixed * ($isEuCountry ? 0.01 : 0.02);
    }

    private function roundUpCommission(float $commission): float
    {
        return ceil($commission * 100) / 100;
    }
}