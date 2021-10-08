<?php

declare(strict_types=1);

namespace WillemVerspyck\SnowflakeService;

use DateTime;
use DateTimeInterface;
use DateTimeZone;
use Exception;
use WillemVerspyck\SnowflakeService\Exception\TranslateException;

/**
 * Class Translate
 */
class Translate
{
    /**
     * @var array
     */
    private array $fields = [];

    /**
     * @return array
     */
    public function getFields(): array
    {
        if (count($this->fields) === 0) {
            throw new ConvertException('Fields not set');
        }
        
        return $this->fields;
    }
    
    /**
     * @param array $fields
     *
     * @throws TranslateException
     */
    public function setFields(array $fields): void
    {
        foreach ($fields as $field) {
            foreach (['name', 'type', 'scale'] as $name) {
                if (false === array_key_exists($name, $field)) {
                    throw new TranslateException('Fields not found');
                }
            }
        }

        $this->fields = $fields;
    }

    /**
     * @param array $data
     *
     * @return array
     *
     * @throws TranslateException
     */
    public function getData(array $data): array
    {
        $content = [];
        
        array_shift($data); // Remove Sequence ID
        
        foreach ($this->getFields() as $index => $field) {
            $fieldName = $field['name'];

            switch ($field['type']) {
                case 'binary':
                case 'text':
                    $content[$fieldName] = $data[$index];

                    break;
                case 'boolean':
                    $content[$fieldName] = $this->getBoolean($data[$index]);

                    break;
                case 'date':
                    $content[$fieldName] = $this->getDate($data[$index]);

                    break;
                case 'fixed':
                    $content[$fieldName] = $this->getFixed($data[$index], $field['scale']);

                    break;
                case 'time':
                case 'timestamp_ltz':
                case 'timestamp_ntz':
                    $content[$fieldName] = $this->getTime($data[$index]);

                    break;
                case 'timestamp_tz':
                    $content[$fieldName] = $this->getTimeWithTimezone($data[$index]);

                    break;
                default:
                    throw new TranslateException(sprintf('Type "%s" not found', $field['type']));
            }
        }

        return $content;
    }

    /**
     * @param string|null $value
     *
     * @return bool|null
     */
    private function getBoolean(?string $value): ?bool
    {
        if ('0' === $value) {
            return false;
        }

        if ('1' === $value) {
            return true;
        }

        return null;
    }

    /**
     * @param string|null $value
     *
     * @return DateTimeInterface|null
     */
    private function getDate(?string $value): ?DateTimeInterface
    {
        if (null === $value) {
            return null;
        }

        $date = new DateTime('1970-01-01 00:00:00');
        $date->modify(sprintf('+%d days', $value));

        return $date;
    }

    /**
     * @param string|null $value
     * @param int         $scale
     *
     * @return float|int|null
     */
    private function getFixed(?string $value, int $scale) // When only PHP8 support: float|int|null
    {
        if (null === $value) {
            return null;
        }

        if (0 === $scale) {
            return (int) $value;
        }

        return (float) $value;
    }

    /**
     * @param string|null $value
     *
     * @return DateTimeInterface|null
     *
     * @throws Exception
     */
    private function getTime(?string $value): ?DateTimeInterface
    {
        if (null === $value) {
            return null;
        }

        if (1 === preg_match('/^([0-9]+\.[0-9]{6})[0-9]{3}/is', $value, $matches)) {
            $timezone = new DateTimeZone('+0000');

            $date = new DateTime(sprintf('@%f', $matches[1]));
            $date->setTimezone($timezone);

            return $date;
        }

        return null;
    }

    /**
     * @param string|null $value
     *
     * @return DateTimeInterface|null
     *
     * @throws Exception
     */
    private function getTimeWithTimezone(?string $value): ?DateTimeInterface
    {
        if (null === $value) {
            return null;
        }

        if (1 === preg_match('/^([0-9]+\.[0-9]{6})[0-9]{3}\s([0-9]{1,4})/is', $value, $matches)) {
            $timezone = new DateTimeZone(sprintf('+%02d:%02d', floor($matches[2] / 60), $matches[2] % 2));

            $date = new DateTime(sprintf('@%f', $matches[1]));
            $date->setTimezone($timezone);

            return $date;
        }

        return null;
    }
}
