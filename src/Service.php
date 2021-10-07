<?php

declare(strict_types=1);

namespace WillemVerspyck\SnowflakeService;

use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use WillemVerspyck\SnowflakeService\Exception\ParameterException;
use WillemVerspyck\SnowflakeService\Exception\ResultException;
use WillemVerspyck\SnowflakeService\Serializer\FieldNameConverter;

/**
 * Class Service
 */
class Service
{
    private const CODE_SUCCESS = '090001';
    private const CODE_ASYNC = '333334';
    private const SIZE_MIN = 10;
    private const SIZE_MAX = 10000;

    /**
     * @var Client
     */
    private Client $client;

    /**
     * @var string|null
     */
    private ?string $warehouse = null;

    /**
     * @var string|null
     */
    private ?string $database = null;

    /**
     * @var string|null
     */
    private ?string $schema = null;

    /**
     * @var string|null
     */
    private ?string $role = null;

    /**
     * @var int
     */
    private int $size = self::SIZE_MAX;

    /**
     * @var bool
     */
    private bool $async = false;

    /**
     * @var bool
     */
    private bool $nullable = true;

    /**
     * Constructor
     *
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * @return Client
     */
    public function getClient(): Client
    {
        return $this->client;
    }

    /**
     * @param Client $client
     *
     * @return $this
     */
    public function setClient(Client $client): self
    {
        $this->client = $client;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getWarehouse(): ?string
    {
        return $this->warehouse;
    }

    /**
     * @param string $warehouse
     *
     * @return $this
     */
    public function setWarehouse(string $warehouse): self
    {
        $this->warehouse = $warehouse;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getDatabase(): ?string
    {
        return $this->database;
    }

    /**
     * @param string $database
     *
     * @return $this
     */
    public function setDatabase(string $database): self
    {
        $this->database = $database;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getSchema(): ?string
    {
        return $this->schema;
    }

    /**
     * @param string $schema
     *
     * @return $this
     */
    public function setSchema(string $schema): self
    {
        $this->schema = $schema;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getRole(): ?string
    {
        return $this->role;
    }

    /**
     * @param string $role
     *
     * @return $this
     */
    public function setRole(string $role): self
    {
        $this->role = $role;

        return $this;
    }

    /**
     * @return int
     */
    public function getSize(): int
    {
        return $this->size;
    }

    /**
     * @param int $size
     *
     * @return $this
     *
     * @throws ParameterException
     */
    public function setSize(int $size): self
    {
        if ($size < self::SIZE_MIN || $size > self::SIZE_MAX) {
            throw new ParameterException(sprintf('Size must be between %d and %d', self::SIZE_MIN, self::SIZE_MAX));
        }

        $this->size = $size;

        return $this;
    }

    /**
     * @return bool
     */
    public function isAsync(): bool
    {
        return $this->async;
    }

    /**
     * @param bool $async
     *
     * @return $this
     */
    public function setAsync(bool $async): self
    {
        $this->async = $async;

        return $this;
    }

    /**
     * @return bool
     */
    public function isNullable(): bool
    {
        return $this->nullable;
    }

    /**
     * @param bool $nullable
     *
     * @return $this
     */
    public function setNullable(bool $nullable): self
    {
        $this->nullable = $nullable;

        return $this;
    }

    /**
     * @param string $statement
     * @param array  $data
     *
     * @return Result
     *
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ExceptionInterface
     * @throws ParameterException
     * @throws RedirectionExceptionInterface
     * @throws ResultException
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function postStatement(string $statement, array $data = []): Result
    {
        $client = $this->getClient();

        $parameters = [
            'page' => 0,
            'pageSize' => $this->getSize(),
            'async' => $this->isAsync() ? 'true' : 'false',
            'nullable' => $this->isNullable() ? 'true' : 'false',
        ];

        $url = sprintf('https://%s.snowflakecomputing.com/api/statements?%s', $client->getAccount(), http_build_query($parameters));

        $data = [
            'statement' => $statement,
            'warehouse' => $this->getWarehouse(),
            'database' => $this->getDatabase(),
            'schema' => $this->getSchema(),
            'role' => $this->getRole(),
            'resultSetMetaData' => [
                'format' => 'json',
            ],
        ] + $data;

        $response = $client->getHttpClient()->request('POST', $url, [
            'headers' => $this->getHeaders(),
            'json' => $data,
        ]);

        return $this->translateResult($response->toArray(false));
    }

    /**
     * @param string $id
     * @param int    $page
     *
     * @return Result
     *
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ExceptionInterface
     * @throws ParameterException
     * @throws RedirectionExceptionInterface
     * @throws ResultException
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function getStatement(string $id, int $page = 1): Result
    {
        $client = $this->getClient();

        $parameters = [
            'page' => $page - 1,
            'pageSize' => $this->getSize(),
        ];

        $url = sprintf('https://%s.snowflakecomputing.com/api/statements/%s?%s', $client->getAccount(), $id, http_build_query($parameters));

        $response = $client->getHttpClient()->request('GET', $url, [
            'headers' => $this->getHeaders(),
        ]);

        return $this->translateResult($response->toArray(false));
    }

    /**
     * @param string $id
     *
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ParameterException
     * @throws RedirectionExceptionInterface
     * @throws ResultException
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function cancelStatement(string $id): void
    {
        $client = $this->getClient();

        $url = sprintf('https://%s.snowflakecomputing.com/api/statements/%s/cancel', $client->getAccount(), $id);

        $response = $client->getHttpClient()->request('POST', $url, [
            'headers' => $this->getHeaders(),
        ]);

        $this->hasResult($response->toArray(false));
    }

    /**
     * @param array $data
     *
     * @throws ResultException
     */
    private function hasResult(array $data): void
    {
        foreach (['code', 'message'] as $field) {
            if (false === array_key_exists($field, $data)) {
                throw new ResultException('Unacceptable result', 406);
            }
        }

        if (false === in_array($data['code'], [self::CODE_SUCCESS, self::CODE_ASYNC])) {
            throw new ResultException(sprintf('%s (%s)', $data['message'], $data['code']), 422);
        }

        if (false === array_key_exists('statementHandle', $data)) {
            throw new ResultException('Unprocessable result', 422);
        }
    }

    /**
     * @param array $data
     *
     * @return Result
     *
     * @throws ExceptionInterface
     * @throws ResultException
     */
    private function translateResult(array $data): Result
    {
        $this->hasResult($data);

        $data['executed'] = $data['code'] === self::CODE_SUCCESS;

        if (array_key_exists('resultSetMetaData', $data)) { // TODO: Resolve this in ObjectNormalizer
            if (array_key_exists('page', $data['resultSetMetaData'])) {
                $data['resultSetMetaData']['page'] += 1;
            }

            $data = array_merge($data, $data['resultSetMetaData']);
        }

        $fieldNameConverter = new FieldNameConverter([
            'numRows' => 'total',
            'numPages' => 'pageTotal',
            'rowType' => 'fields',
            'statementHandle' => 'id',
            'createdOn' => 'timestamp',
        ]);

        $normalizer = new ObjectNormalizer(null, $fieldNameConverter);

        $serializer = new Serializer([$normalizer], [new JsonEncoder()]);

        return $serializer->denormalize($data, Result::class);
    }

    /**
     * @return array
     *
     * @throws ParameterException
     */
    private function getHeaders(): array
    {
        return [
            sprintf('Authorization: Bearer %s', $this->getClient()->getToken()),
            'User-Agent: SnowflakeService',
            'X-Snowflake-Authorization-Token-Type: KEYPAIR_JWT',
        ];
    }
}
