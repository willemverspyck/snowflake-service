<?php

declare(strict_types=1);

namespace WillemVerspyck\SnowflakeService;

use Jose\Component\Core\AlgorithmManager;
use Jose\Component\KeyManagement\JWKFactory;
use Jose\Component\Signature\Algorithm\RS256;
use Jose\Component\Signature\JWSBuilder;
use Jose\Component\Signature\Serializer\CompactSerializer;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use WillemVerspyck\SnowflakeService\Exception\ParameterException;

final class Client
{
    /**
     * @var HttpClientInterface|null
     */
    private ?HttpClientInterface $httpClient = null;

    /**
     * @var string|null
     */
    private ?string $account = null;

    /**
     * @var string|null
     */
    private ?string $user = null;

    /**
     * @var string|null
     */
    private ?string $publicKey = null;

    /**
     * @var string|null
     */
    private ?string $privateKey = null;

    /**
     * @var string|null
     */
    private ?string $token = null;

    /**
     * @return HttpClientInterface
     */
    public function getHttpClient(): HttpClientInterface
    {
        if (null === $this->httpClient) {
            $this->httpClient = HttpClient::create();
        }

        return $this->httpClient;
    }

    public function setHttpClient(HttpClientInterface $httpClient): self
    {
        $this->httpClient = $httpClient;

        return $this;
    }

    /**
     * @return string
     *
     * @throws ParameterException
     */
    public function getAccount(): string
    {
        if (null === $this->account) {
            throw new ParameterException('Account not set');
        }

        return $this->account;
    }

    /**
     * @param string $account
     *
     * @return $this
     */
    public function setAccount(string $account): self
    {
        $this->account = $account;

        return $this;
    }

    /**
     * @return string
     *
     * @throws ParameterException
     */
    public function getUser(): string
    {
        if (null === $this->user) {
            throw new ParameterException('User not set');
        }

        return $this->user;
    }

    /**
     * @param string $user
     *
     * @return $this
     */
    public function setUser(string $user): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return string
     *
     * @throws ParameterException
     */
    public function getPublicKey(): string
    {
        if (null === $this->publicKey) {
            throw new ParameterException('Public key not set');
        }

        return $this->publicKey;
    }

    /**
     * @param string $publicKey
     *
     * @return $this
     *
     * @throws ParameterException
     */
    public function setPublicKey(string $publicKey): self
    {
        if (file_exists($publicKey)) {
            throw new ParameterException(sprintf('Public key "%s" not found', $publicKey));
        }

        $this->publicKey = $publicKey;

        return $this;
    }

    /**
     * @return string
     *
     * @throws ParameterException
     */
    public function getPrivateKey(): string
    {
        if (null === $this->privateKey) {
            throw new ParameterException('Private key not set');
        }

        return $this->privateKey;
    }

    /**
     * @param string $privateKey
     *
     * @return $this
     */
    public function setPrivateKey(string $privateKey): self
    {
        $this->privateKey = $privateKey;

        return $this;
    }

    /**
     * @return string
     *
     * @throws ParameterException
     */
    public function getToken(): string
    {
        if (null === $this->token) {
            throw new ParameterException('Token not set');
        }

        return $this->token;
    }

    /**
     * @param int $expires
     *
     * @throws ParameterException
     */
    public function setToken(int $expires = 3600): void
    {
        $account = strtoupper($this->getAccount());
        $user = strtoupper($this->getUser());
        $time = time();

        $payload = [
            'iss' => sprintf('%s.%s.%s', $account, $user, $this->getPublicKey()),
            'sub' => sprintf('%s.%s', $account, $user),
            'iat' => $time,
            'exp' => $time + ($expires * 30),
        ];

        $algorithmManager = new AlgorithmManager([
            new RS256(),
        ]);

        $jwsBuilder = new JWSBuilder($algorithmManager);

        $signature = JWKFactory::createFromKeyFile($this->getPrivateKey());

        $jws = $jwsBuilder
            ->create()
            ->withPayload(json_encode($payload))
            ->addSignature($signature, ['alg' => 'RS256'])
            ->build();

        $serializer = new CompactSerializer();

        $this->token = $serializer->serialize($jws);
    }

    /**
     * @return Service
     */
    public function getService(): Service
    {
        return new Service($this);
    }
}
