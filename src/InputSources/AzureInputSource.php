<?php
declare(strict_types=1);

namespace Level23\Druid\InputSources;

class AzureInputSource extends CloudInputSource
{
    protected function getCloudType(): string
    {
        return 'azure';
    }
}