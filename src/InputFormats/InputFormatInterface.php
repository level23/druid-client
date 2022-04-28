<?php

declare(strict_types=1);

namespace Level23\Druid\InputFormats;

interface InputFormatInterface
{
    /**
     * Return the InputFormat so that it can be used in a druid query.
     *
     * @return array<string,string|array<mixed>|bool|int>
     */
    public function toArray(): array;
}