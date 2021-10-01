<?php

declare(strict_types=1);

namespace WillemVerspyck\SnowflakeService;

use DateTime;
use DateTimeInterface;
use Exception;

/**
 * Result
 */
final class Result
{
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
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Set id
     *
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
     * Set count
     *
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
     * Set page
     *
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
     * Set pageCount
     *
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
     * Get fields
     *
     * @return array|null
     */
    public function getFields(): ?array
    {
        return $this->fields;
    }

    /**
     * Set fields
     *
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
     * Get data
     *
     * @return array|null
     */
    public function getData(): ?array
    {
        return $this->data;
    }

    /**
     * Set data
     *
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
}
