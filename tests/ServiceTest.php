<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use WillemVerspyck\SnowflakeService\Client;
use WillemVerspyck\SnowflakeService\Exception\ResultException;
use WillemVerspyck\SnowflakeService\Service;
use WillemVerspyck\SnowflakeService\Exception\ParameterException;

/**
 * Class ServiceTest
 */
class ServiceTest extends TestCase
{
    /**
     * @var Service
     */
    private Service $service;

    /**
     *
     */
    public function setUp(): void
    {
        $client = $this->createMock(Client::class);

        $this->service = new Service($client);
    }

    /**
     * Test getSizeException
     */
    public function testGetSizeException(): void
    {
        self::expectException(ParameterException::class);

        $this->service->setSize(10000000);
    }

    /**
     * Test getUser
     */
    public function testGetSize(): void
    {
        $this->service->setSize(1000);

        self::assertEquals(1000, $this->service->getSize());
    }

    /**
     * Test getWarehouse
     */
    public function testGetWarehouse(): void
    {
        self::assertNull($this->service->getWarehouse());

        $this->service->setWarehouse('ACCOUNT');

        self::assertEquals('ACCOUNT', $this->service->getWarehouse());
    }

    /**
     * Test getDatabase
     */
    public function testGetDatabase(): void
    {
        self::assertNull($this->service->getDatabase());

        $this->service->setDatabase('DATABASE');

        self::assertEquals('DATABASE', $this->service->getDatabase());
    }

    /**
     * Test getSchema
     */
    public function testGetSchema(): void
    {
        self::assertNull($this->service->getSchema());

        $this->service->setSchema('SCHEMA');

        self::assertEquals('SCHEMA', $this->service->getSchema());
    }

    /**
     * Test getSchema
     */
    public function testGetRole(): void
    {
        self::assertNull($this->service->getRole());

        $this->service->setRole('ROLE');

        self::assertEquals('ROLE', $this->service->getRole());
    }

    /**
     * Test isAsync
     */
    public function testIsAsync(): void
    {
        self::assertFalse($this->service->isAsync());

        $this->service->setAsync(true);

        self::assertTrue($this->service->isAsync());
    }

    /**
     * Test isNullable
     */
    public function testIsNullable(): void
    {
        self::assertTrue($this->service->isNullable());

        $this->service->setNullable(false);

        self::assertFalse($this->service->isNullable());
    }

    /**
     * Test hasResultUnacceptable
     */
    public function testHasResultUnacceptable(): void
    {
        $reflection = new ReflectionClass($this->service);

        $method = $reflection->getMethod('hasResult');
        $method->setAccessible(true);

        $this->expectException(ResultException::class);
        $this->expectExceptionMessage('Unacceptable result');
        $this->expectExceptionCode(406);

        $method->invokeArgs($this->service, [[]]);
    }

    /**
     * Test hasResultNotSuccessOrAsync
     */
    public function testHasResultNotSuccessOrAsync(): void
    {
        $reflection = new ReflectionClass($this->service);

        $method = $reflection->getMethod('hasResult');
        $method->setAccessible(true);

        $this->expectException(ResultException::class);
        $this->expectExceptionMessage('Exception Message (000000)');
        $this->expectExceptionCode(422);

        $method->invokeArgs($this->service, [[
            'code' => '000000',
            'message' => 'Exception Message',
        ]]);
    }

    /**
     * Test hasResultUnprocessable
     */
    public function testHasResultUnprocessable(): void
    {
        $reflection = new ReflectionClass($this->service);

        $method = $reflection->getMethod('hasResult');
        $method->setAccessible(true);

        $this->expectException(ResultException::class);
        $this->expectExceptionMessage('Unprocessable result');
        $this->expectExceptionCode(422);

        $method->invokeArgs($this->service, [[
            'code' => '090001',
            'message' => 'Complete',
        ]]);
    }
}
