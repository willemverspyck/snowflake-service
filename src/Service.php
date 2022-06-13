<?php

declare(strict_types=1);

namespace WillemVerspyck\SnowflakeService;

use Symfony\Component\HttpClient\Exception\JsonException;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use WillemVerspyck\SnowflakeService\Exception\ParameterException;
use WillemVerspyck\SnowflakeService\Exception\ResultException;

class Service
{
    private const CODE_SUCCESS = '090001';
    private const CODE_ASYNC = '333334';

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
     * @param array  $parameters
     *
     * @return Result
     *
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ParameterException
     * @throws RedirectionExceptionInterface
     * @throws ResultException
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function postStatement(string $statement, array $parameters = []): Result
    {
        $client = $this->getClient();

        $account = $client->getAccount();
        
        $variables = http_build_query([
            'async' => $this->isAsync() ? 'true' : 'false',
            'nullable' => $this->isNullable() ? 'true' : 'false',
        ]);

        $url = sprintf('https://%s.snowflakecomputing.com/api/v2/statements?%s', $account, $variables);

        $data = [
            'statement' => $statement,
            'warehouse' => $this->getWarehouse(),
            'database' => $this->getDatabase(),
            'schema' => $this->getSchema(),
            'role' => $this->getRole(),
            'resultSetMetaData' => [
                'format' => 'jsonv2',
            ],
        ];

        $response = $client->getHttpClient()->request('POST', $url, [
            'headers' => $this->getHeaders(),
            'json' => $data,
        ]);

        $content = $this->toArray($response);

        $this->hasResult($content);

        return $this->translateResult($content, $content['code'] === self::CODE_SUCCESS);
    }

    /**
     * @param string $id
     * @param int    $page
     *
     * @return array|Result
     *
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ParameterException
     * @throws RedirectionExceptionInterface
     * @throws ResultException
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function getStatement(string $id, int $page = 1): array|Result
    {
        $client = $this->getClient();
        
        $account = $client->getAccount();

        $variables = http_build_query([
            'partition' => $page - 1,
        ]);

        $url = sprintf('https://%s.snowflakecomputing.com/api/v2/statements/%s?%s', $account, $id, $variables);

        $response = $client->getHttpClient()->request('GET', $url, [
            'headers' => $this->getHeaders(),
        ]);

        // Remove custom toArray when bug in PHP is fixed with support for multiple GZIP's (CRC-32 check and length)
        // $content = $response->toArray(true);

        $content = $this->toArray($response);

        if ($page > 1) {
            return $this->translateData($content);
        }

        return $this->translateResult($content, true);
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

        $url = sprintf('https://%s.snowflakecomputing.com/api/v2/statements/%s/cancel', $client->getAccount(), $id);

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

        foreach (['statementHandle', 'statementStatusUrl'] as $field) {
            if (false === array_key_exists($field, $data)) {
                throw new ResultException('Unprocessable result', 422);
            }
        }
    }

    /**
     * @param array $data
     * @param bool  $executed
     *
     * @return Result
     *
     * @throws ResultException
     */
    private function translateResult(array $data, bool $executed = false): Result
    {
        $result = new Result();
        $result->setId($data['statementHandle']);
        $result->setExecuted($executed);

        if (false === $executed) {
            return $result;
        }

        foreach (['resultSetMetaData', 'data', 'createdOn'] as $field) {
            if (false === array_key_exists($field, $data)) {
                throw new ResultException(sprintf('Object "%s" not found', $field));
            }
        }

        foreach (['numRows', 'partitionInfo', 'rowType'] as $field) {
            if (false === array_key_exists($field, $data['resultSetMetaData'])) {
                throw new ResultException(sprintf('Object "%s" in "resultSetMetaData" not found', $field));
            }
        }

        $result->setTotal($data['resultSetMetaData']['numRows']);
        $result->setPage(1);
        $result->setPageTotal(count($data['resultSetMetaData']['partitionInfo']));
        $result->setFields($data['resultSetMetaData']['rowType']);
        $result->setData($data['data']);
        $result->setTimestamp($data['createdOn']);

        return $result;
    }

    /**
     * @param array $data
     *
     * @return array
     *
     * @throws ResultException
     */
    private function translateData(array $data): array
    {
        if (false === array_key_exists('data', $data)) {
            throw new ResultException('Object "data" not found');
        }

        return $data['data'];
    }

    /**
     * @return array
     *
     * @throws ParameterException
     *
     * @todo Remove "Accept-Encoding" when bugfix GZIP is fixed
     */
    private function getHeaders(): array
    {
        return [
            sprintf('Authorization: Bearer %s', $this->getClient()->getToken()),
            'Accept-Encoding: gzip',
            'User-Agent: SnowflakeService/0.5',
            'X-Snowflake-Authorization-Token-Type: KEYPAIR_JWT',
        ];
    }

    /**
     * @param ResponseInterface $response
     *
     * @return array
     *
     * @throws JsonException
     *
     * @todo Remove this method when bugfix GZIP is fixed
     */
    private function toArray(ResponseInterface $response): array
    {
        if ('' === $content = $response->getContent(true)) {
            throw new JsonException('Response body is empty.');
        }

        $headers = $response->getHeaders();

        if ('gzip' === ($headers['content-encoding'][0] ?? null)) {
            $content = $this->gzdecode($content);
        }

        try {
            $content = json_decode($content, true, 512, JSON_BIGINT_AS_STRING | JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new JsonException(sprintf('%s for "%s".', $exception->getMessage(), $response->getInfo('url')), $exception->getCode());
        }

        if (false === is_array($content)) {
            throw new JsonException(sprintf('JSON content was expected to decode to an array, "%s" returned for "%s".', get_debug_type($content), $response->getInfo('url')));
        }

        return $content;
    }

    /**
     * @param string $data
     *
     * @return string
     */
    private function gzdecode(string $data): string
    {
        $inflate = inflate_init(ZLIB_ENCODING_GZIP);

        $content = '';
        $offset = 0;

        do {
            $content .= inflate_add($inflate, substr($data, $offset));

            if (ZLIB_STREAM_END === inflate_get_status($inflate)) {
                $offset += inflate_get_read_len($inflate);
            }
        } while ($offset < strlen($data));

        return $content;
    }
}
