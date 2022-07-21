<?php
declare(strict_types=1);

namespace Level23\Druid\InputSources;

class GoogleCloudInputSource extends CloudInputSource
{
    protected function getCloudType(): string
    {
        return 'google';
    }
}