<?php

namespace App\Services;
use Exception;

class TransactionService implements TransactionServiceInterface
{
    private string $filePath;

    public function __construct(string $filePath)
    {
        $this->setFilePath($filePath);
    }

    /**
     * @throws Exception
     */
    public function getTransactions(): array {
        if (empty($this->getFilePath())) {
            throw new Exception('URL is required');
        }

        $lines = $this->getContentFromFile($this->getFilePath());

        $transactions = [];

        foreach ($lines as $line) {
            $data = json_decode($line, true);

            $transactions[] = [
                'bin' => $data['bin'],
                'amount' => $data['amount'],
                'currency' => $data['currency']
            ];
        }

        return $transactions;
    }

    public function getFilePath(): string
    {
        return $this->filePath;
    }

    public function setFilePath(string $filePath): void
    {
        $this->filePath = $filePath;
    }

    protected function getContentFromFile(string $getFilePath): false|array
    {
        return file($getFilePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    }
}