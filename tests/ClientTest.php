<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use WillemVerspyck\SnowflakeService\Client;
use WillemVerspyck\SnowflakeService\Exception\ParameterException;

/**
 * Class ClientTest
 */
class ClientTest extends TestCase
{
    /**
     * @var Client
     */
    private Client $client;

    /**
     *
     */
    public function setUp(): void
    {
        $this->client = new Client();
    }

    /**
     * Test getUserException
     */
    public function testGetUserException(): void
    {
        self::expectException(ParameterException::class);

        $this->client->getUser();
    }

    /**
     * Test getUser
     */
    public function testGetUser(): void
    {
        $this->client->setUser('USER');

        self::assertEquals('USER', $this->client->getUser());
    }

    /**
     * Test getAccountException
     */
    public function testGetAccountException(): void
    {
        self::expectException(ParameterException::class);

        $this->client->getAccount();
    }

    /**
     * Test getAccount
     */
    public function testGetAccount(): void
    {
        $this->client->setAccount('ACCOUNT');

        self::assertEquals('ACCOUNT', $this->client->getAccount());
    }

    /**
     * Test getPublicKeyException
     */
    public function testGetPublicKeyException(): void
    {
        self::expectException(ParameterException::class);

        $this->client->getPublicKey();
    }

    /**
     * Test getPublicKey
     */
    public function testGetPublicKey(): void
    {
        $this->client->setPublicKey('PUBLIC_KEY');

        self::assertEquals('PUBLIC_KEY', $this->client->getPublicKey());
    }

    /**
     * Test getPrivateKeyException
     */
    public function testGetPrivateKeyException(): void
    {
        self::expectException(ParameterException::class);

        $this->client->getPrivateKey();
    }

    /**
     * Test getPrivateKey
     */
    public function testGetPrivateKey(): void
    {
        $this->client->setPrivateKey('PRIVATE_KEY');

        self::assertEquals('PRIVATE_KEY', $this->client->getPrivateKey());
    }
}
