<?php

declare(strict_types=1);

namespace WillemVerspyck\SnowflakeService;

use DateTime;
use DateTimeInterface;
use Exception;
use WillemVerspyck\SnowflakeService\Exception\ResultException;

final class Result
{
    /**
     * @var Service
     */
    private Service $service;

    /**
     * @var string
     */
    private string $id;

    /**
     * @var int|null
     */
    private ?int $total = null;

    /**
     * @var int|null
     */
    private ?int $page = null;

    /**
     * @var int|null
     */
    private ?int $pageTotal = null;

    /**
     * @var array|null
     */
    private ?array $fields = null;

    /**
     * @var array|null
     */
    private ?array $data = null;

    /**
     * @var DateTimeInterface|null
     */
    private ?DateTimeInterface $timestamp = null;

    /**
     * @var bool
     */
    private bool $executed;

    /**
     * @param Service $service
     */
    public function __construct(Service $service)
    {
        $this->service = $service;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     *
     * @return $this
     */
    public function setId(string $id): self
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getTotal(): ?int
    {
        return $this->total;
    }

    /**
     * @param int $total
     *
     * @return $this
     */
    public function setTotal(int $total): self
    {
        $this->total = $total;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getPage(): ?int
    {
        return $this->page;
    }

    /**
     * @param int $page
     *
     * @return $this
     */
    public function setPage(int $page): self
    {
        $this->page = $page;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getPageTotal(): ?int
    {
        return $this->pageTotal;
    }

    /**
     * @param int $pageTotal
     *
     * @return $this
     */
    public function setPageTotal(int $pageTotal): self
    {
        $this->pageTotal = $pageTotal;

        return $this;
    }

    /**
     * @return array|null
     */
    public function getFields(): ?array
    {
        return $this->fields;
    }

    /**
     * @param array $fields
     *
     * @return $this
     */
    public function setFields(array $fields): self
    {
        $this->fields = $fields;

        return $this;
    }


    /**
     * @return array|null
     */
    public function getData(): ?array
    {
        $fields = $this->getFields();

        if (null === $this->data || null === $fields) {
            return null;
        }

        $translate = new Translate();
        $translate->setFields($fields);

        return array_map([$translate, 'getData'], $this->data);
    }

    /**
     * @return array|null
     */
    public function getDataRaw(): ?array
    {
        return $this->data;
    }

    /**
     * @param array $data
     *
     * @return $this
     */
    public function setData(array $data): self
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @return DateTimeInterface|null
     */
    public function getTimestamp(): ?DateTimeInterface
    {
        return $this->timestamp;
    }

    /**
     * @param int $timestamp
     *
     * @return $this
     *
     * @throws Exception
     */
    public function setTimestamp(int $timestamp): self
    {
        $this->timestamp = new DateTime();
        $this->timestamp->setTimestamp((int) ($timestamp / 1000));

        return $this;
    }

    /**
     * @return bool
     */
    public function isExecuted(): bool
    {
        return $this->executed;
    }

    /**
     * @param bool $executed
     *
     * @return $this
     */
    public function setExecuted(bool $executed): self
    {
        $this->executed = $executed;

        return $this;
    }

    /**
     * @return bool
     */
    public function getPaginationFirst(): bool
    {
        return $this->getPagination(1);
    }

    /**
     * @return bool
     */
    public function getPaginationPrevious(): bool
    {
        return $this->getPagination($this->getPage() - 1);
    }

    /**
     * @return bool
     */
    public function getPaginationNext(): bool
    {
        return $this->getPagination($this->getPage() + 1);
    }

    /**
     * @return bool
     */
    public function getPaginationLast(): bool
    {
        return $this->getPagination($this->getPageTotal());
    }

    /**
     * @param int $page
     *
     * @return bool
     */
    public function getPagination(int $page): bool
    {
        if (false === $this->isExecuted()) {
            return false;
        }

        if ($page < 0 || $page > $this->getPageTotal()) {
            return false;
        }

        $data = $this->service->getStatement($this->getId(), $page);

        if (false === array_key_exists('data', $data)) {
            throw new ResultException('Object "data" not found');
        }

        $this->setData($data['data']);
        $this->setPage($page);

        return true;
    }
}
