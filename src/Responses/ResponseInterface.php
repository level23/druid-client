<?php
declare(strict_types=1);

namespace Level23\Druid\Responses;

interface ResponseInterface
{
    public function getRawResponse() : array;

    public function getResponse() : array;
}
