<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use WillemVerspyck\SnowflakeService\Client;
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
     * Test getWarehouseException
     */
    public function testGetWarehouseException(): void
    {
        self::expectException(ParameterException::class);

        $this->service->getWarehouse();
    }

    /**
     * Test getWarehouse
     */
    public function testGetWarehouse(): void
    {
        $this->service->setWarehouse('ACCOUNT');

        self::assertEquals('ACCOUNT', $this->service->getWarehouse());
    }

    /**
     * Test getDatabaseException
     */
    public function testGetDatabaseException(): void
    {
        self::expectException(ParameterException::class);

        $this->service->getDatabase();
    }

    /**
     * Test getDatabase
     */
    public function testGetDatabase(): void
    {
        $this->service->setDatabase('DATABASE');

        self::assertEquals('DATABASE', $this->service->getDatabase());
    }

    /**
     * Test getSchemaException
     */
    public function testGetSchemaException(): void
    {
        self::expectException(ParameterException::class);

        $this->service->getSchema();
    }

    /**
     * Test getSchema
     */
    public function testGetSchema(): void
    {
        $this->service->setSchema('SCHEMA');

        self::assertEquals('SCHEMA', $this->service->getSchema());
    }

    /**
     * Test getRoleException
     */
    public function testGetRoleException(): void
    {
        self::expectException(ParameterException::class);

        $this->service->getRole();
    }

    /**
     * Test getSchema
     */
    public function testGetRole(): void
    {
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
}
