<?php
declare(strict_types=1);

namespace Level23\Druid\Types;

use MyCLabs\Enum\Enum;

/**
 * Class Granularity
 * @method static self ALL()
 * @method static self NONE()
 * @method static self SECOND()
 * @method static self MINUTE()
 * @method static self FIFTEEN_MINUTE()
 * @method static self THIRTY_MINUTE()
 * @method static self HOUR()
 * @method static self DAY()
 * @method static self WEEK()
 * @method static self MONTH()
 * @method static self QUARTER()
 * @method static self YEAR()
 *
 * @package Level23\Druid\Types
 * @codeCoverageIgnore
 */
class Granularity extends Enum
{
    private const ALL            = 'all';
    private const NONE           = 'none';
    private const SECOND         = 'second';
    private const MINUTE         = 'minute';
    private const FIFTEEN_MINUTE = 'fifteen_minute';
    private const THIRTY_MINUTE  = 'thirty_minute';
    private const HOUR           = 'hour';
    private const DAY            = 'day';
    private const WEEK           = 'week';
    private const MONTH          = 'month';
    private const QUARTER        = 'quarter';
    private const YEAR           = 'year';
}