<?php
declare(strict_types=1);

namespace Level23\Druid\InputFormats;

class KafkaInputFormat implements InputFormatInterface
{
    protected InputFormatInterface $valueFormat;

    protected ?InputFormatInterface $keyFormat;

    /**
     * @var array<string,string>|null
     */
    protected ?array $headerFormat;

    protected ?string $headerColumnPrefix;

    protected ?string $keyColumnName;

    protected ?string $timestampColumnName;

    protected ?string $topicColumnName;

    protected ?string $offsetColumnName;

    protected ?string $partitionColumnName;

    /**
     * @param InputFormatInterface      $valueFormat           Input format used to parse the Kafka record value.
     * @param InputFormatInterface|null $keyFormat             Input format used to parse the Kafka record key. When
     *                                                         omitted the key is not parsed into columns.
     * @param array<string,string>|null $headerFormat          Header decoder spec, e.g. ['type' => 'string',
     *                                                         'encoding' => 'UTF-8'].
     *                                                         When omitted Kafka headers are not exposed.
     * @param string|null               $headerColumnPrefix    Prefix for header columns.
     * @param string|null               $keyColumnName         Column name for the parsed key.
     * @param string|null               $timestampColumnName   Column name for the Kafka timestamp.
     * @param string|null               $topicColumnName       Column name for the Kafka topic.
     * @param string|null               $offsetColumnName      Column name for the Kafka record offset. Requires Druid 36
     *                                                         or higher.
     * @param string|null               $partitionColumnName   Column name for the Kafka partition number. Requires
     *                                                         Druid 36 or higher.
     *
     * @see https://druid.apache.org/docs/latest/ingestion/data-formats#kafka
     */
    public function __construct(
        InputFormatInterface $valueFormat,
        ?InputFormatInterface $keyFormat = null,
        ?array $headerFormat = null,
        ?string $headerColumnPrefix = null,
        ?string $keyColumnName = null,
        ?string $timestampColumnName = null,
        ?string $topicColumnName = null,
        ?string $offsetColumnName = null,
        ?string $partitionColumnName = null
    ) {
        $this->valueFormat         = $valueFormat;
        $this->keyFormat           = $keyFormat;
        $this->headerFormat        = $headerFormat;
        $this->headerColumnPrefix  = $headerColumnPrefix;
        $this->keyColumnName       = $keyColumnName;
        $this->timestampColumnName = $timestampColumnName;
        $this->topicColumnName     = $topicColumnName;
        $this->offsetColumnName    = $offsetColumnName;
        $this->partitionColumnName = $partitionColumnName;
    }

    /**
     * @return array<string,string|array<mixed>>
     */
    public function toArray(): array
    {
        $result = [
            'type'        => 'kafka',
            'valueFormat' => $this->valueFormat->toArray(),
        ];

        if ($this->keyFormat !== null) {
            $result['keyFormat'] = $this->keyFormat->toArray();
        }

        if ($this->headerFormat !== null) {
            $result['headerFormat'] = $this->headerFormat;
        }

        if ($this->headerColumnPrefix !== null) {
            $result['headerColumnPrefix'] = $this->headerColumnPrefix;
        }

        if ($this->keyColumnName !== null) {
            $result['keyColumnName'] = $this->keyColumnName;
        }

        if ($this->timestampColumnName !== null) {
            $result['timestampColumnName'] = $this->timestampColumnName;
        }

        if ($this->topicColumnName !== null) {
            $result['topicColumnName'] = $this->topicColumnName;
        }

        if ($this->offsetColumnName !== null) {
            $result['offsetColumnName'] = $this->offsetColumnName;
        }

        if ($this->partitionColumnName !== null) {
            $result['partitionColumnName'] = $this->partitionColumnName;
        }

        return $result;
    }
}
