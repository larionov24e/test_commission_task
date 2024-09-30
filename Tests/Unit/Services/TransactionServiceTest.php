<?php

namespace Unit\Services;

use App\Services\TransactionService;
use Exception;
use PHPUnit\Framework\TestCase;

class TransactionServiceTest extends TestCase
{
    public function testGetTransactionsThrowsExceptionIfFilePathIsEmpty()
    {
        $mock = $this->getMockBuilder(TransactionService::class)
            ->onlyMethods(['getFilePath'])
            ->setConstructorArgs([''])
            ->getMock();

        $mock->expects($this->once())
            ->method('getFilePath')
            ->willReturn('');

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('URL is required');

        $mock->getTransactions();
    }

    /**
     * @throws Exception
     */
    public function testGetTransactionsSuccess()
    {
        $fileContents = [
            '{"bin": "45717360", "amount": 100.00, "currency": "EUR"}',
            '{"bin": "516793", "amount": 50.00, "currency": "USD"}'
        ];

        $mock = $this->getMockBuilder(TransactionService::class)
            ->onlyMethods(['getFilePath', 'getContentFromFile'])
            ->setConstructorArgs(['/path/to/transactions'])
            ->getMock();

        $mock
            ->method('getFilePath')
            ->willReturn('/path/to/transactions');

        $mock
            ->method('getContentFromFile')->with('/path/to/transactions')
            ->willReturn($fileContents);

        $transactions = $mock->getTransactions();

        $this->assertCount(2, $transactions);
        $this->assertEquals('45717360', $transactions[0]['bin']);
        $this->assertEquals(100.00, $transactions[0]['amount']);
        $this->assertEquals('EUR', $transactions[0]['currency']);
        $this->assertEquals('516793', $transactions[1]['bin']);
        $this->assertEquals(50.00, $transactions[1]['amount']);
        $this->assertEquals('USD', $transactions[1]['currency']);
    }

    /**
     * @throws Exception
     */
    public function testGetTransactionsReturnsEmptyArrayForEmptyFile()
    {
        $fileContents = [];

        $mock = $this->getMockBuilder(TransactionService::class)
            ->onlyMethods(['getFilePath', 'getContentFromFile'])
            ->setConstructorArgs(['/path/to/transactions'])
            ->getMock();

        $mock
            ->method('getFilePath')
            ->willReturn('/path/to/transactions');

        $mock
            ->method('getContentFromFile')->with('/path/to/transactions')
            ->willReturn($fileContents);

        $transactions = $mock->getTransactions();

        $this->assertEmpty($transactions);
    }

    public function testSetFilePath()
    {
        $transactionService = new TransactionService('/path/to/transactions');

        $this->assertEquals('/path/to/transactions', $transactionService->getFilePath());

        $transactionService->setFilePath('/new/path/to/transactions');

        $this->assertEquals('/new/path/to/transactions', $transactionService->getFilePath());
    }

    protected function mockFileFunction(array $fileContents): void
    {
        $file = $this->getMockBuilder(TransactionService::class)
            ->onlyMethods(['getContentFromFile'])
            ->getMock();

        $file->expects($this->any())
            ->method('getContentFromFile')
            ->willReturn($fileContents);
    }
}