<?php

namespace Unit\Services\BinListProvider;

use App\Services\BinListProvider\HandyapiProvider;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class HandyapiProviderTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testGetBinDetailsSuccess()
    {
        $bin = '123456';
        $mockedResponse = [
            'Country' => ['A2' => 'US']
        ];

        $mock = $this->getMockBuilder(HandyapiProvider::class)->onlyMethods(['fetchData'])
            ->getMock();

        $mock->expects($this->once())
            ->method('fetchData')
            ->with($bin)
            ->willReturn($mockedResponse);

        $result = $mock->getBinDetails($bin);

        $this->assertSame($mock, $result);
    }

    public function testFetchDataFailure()
    {
        $bin = '123456';

        $mock = $this->getMockBuilder(HandyapiProvider::class)
            ->onlyMethods(['getUrl', 'getFileContents'])
            ->getMock();

        $mock->method('getUrl')
            ->willReturn('https://data.handyapi.com/bin/');

        $mock->method('getFileContents')->with('https://data.handyapi.com/bin/' . $bin)
            ->willReturn(false);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unable to fetch data from https://data.handyapi.com/bin/' . $bin);

        $mock->getBinDetails($bin);
    }

    public function testGetCountryName()
    {
        $bin = '123456';
        $mockedResponse = [
            'Country' => ['A2' => 'US']
        ];

        $mock = $this->getMockBuilder(HandyapiProvider::class)
            ->onlyMethods(['fetchData'])
            ->getMock();

        $mock->expects($this->once())
            ->method('fetchData')
            ->with($bin)
            ->willReturn($mockedResponse);

        $mock->getBinDetails($bin);

        $this->assertEquals('US', $mock->getCountryName());
    }

    public function testGetCountryNameReturnsNullIfNoCountry()
    {
        $bin = '123456';
        $mockedResponse = [
            'Country' => []
        ];

        $mock = $this->getMockBuilder(HandyapiProvider::class)
            ->onlyMethods(['fetchData'])
            ->getMock();

        $mock->expects($this->once())
            ->method('fetchData')
            ->with($bin)
            ->willReturn($mockedResponse);

        $mock->getBinDetails($bin);

        $this->assertNull($mock->getCountryName());
    }
}