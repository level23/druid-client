<?php
declare(strict_types=1);

namespace Level23\Druid\Responses;

interface ResponseInterface
{
    public function raw(): array;

    public function data(): array;
}
