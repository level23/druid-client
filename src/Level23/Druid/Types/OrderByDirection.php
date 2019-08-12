<?php
declare(strict_types=1);

namespace Level23\Druid\Types;

use MyCLabs\Enum\Enum;

/**
 * Class OrderByDirection
 *
 * @method static self ASC()
 * @method static self DESC()
 *
 * @package Level23\Druid\Types
 */
class OrderByDirection extends Enum
{
    private const ASC  = 'ascending';
    private const DESC = 'descending';
}