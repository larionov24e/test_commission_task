<?php

namespace App\Services;

interface TransactionServiceInterface
{
    public function getTransactions(): array;
}