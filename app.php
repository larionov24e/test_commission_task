<?php

use App\Services\BinListProvider\HandyapiProvider;
use App\Services\CommissionCalculator;
use App\Services\ExchangeRateProvider\ExchangeRateApiProvider;
use App\Services\TransactionService;

require_once 'vendor/autoload.php';

try {
    $fileTransactionPath = $argv[1];
    $transactionService = new TransactionService($fileTransactionPath);
    $binListService = new HandyapiProvider();
    $exchangeService = new ExchangeRateApiProvider();

    $commissionCalculator = new CommissionCalculator(
        $exchangeService,
        $transactionService,
        $binListService
    );

    $commissions = $commissionCalculator->calculate();

    foreach ($commissions as $commission) {
        echo $commission . PHP_EOL;
    }
} catch (Exception $e) {
    echo $e->getMessage();
}
