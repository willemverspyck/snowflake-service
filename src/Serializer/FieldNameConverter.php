<?php

declare(strict_types=1);

namespace WillemVerspyck\SnowflakeService\Serializer;

use Symfony\Component\Serializer\NameConverter\NameConverterInterface;

/**
 * FieldNameConverter
 */
class FieldNameConverter implements NameConverterInterface
{
    /**
     * @var array
     */
    private array $fields = [];

    /**
     * @param array $fields
     */
    public function __construct(array $fields)
    {
        $this->fields = $fields;
    }

    /**
     * @inheritDoc
     */
    public function normalize(string $propertyName): string
    {
        $fields = array_flip($this->fields);

        if (array_key_exists($propertyName, $fields)) {
            return $fields[$propertyName];
        }

        return $propertyName;
    }

    /**
     * @inheritDoc
     */
    public function denormalize(string $propertyName): string
    {
        if (array_key_exists($propertyName, $this->fields)) {
            return $this->fields[$propertyName];
        }

        return $propertyName;
    }
}
