<?php
declare(strict_types=1);

namespace Level23\Druid\Types;

enum ScanQueryResultFormat: string
{
    case NORMAL_LIST    = 'list';
    case COMPACTED_LIST = 'compactedList';
}
