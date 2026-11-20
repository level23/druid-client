<?php
declare(strict_types=1);

namespace Level23\Druid\Types;

/**
 * Enum ThetaSketchFunction
 *
 * The set operations which can be used in a thetaSketchSetOp post aggregator.
 *
 * @package Level23\Druid\Types
 */
enum ThetaSketchFunction: string
{
    case UNION     = 'UNION';
    case INTERSECT = 'INTERSECT';
    case NOT       = 'NOT';
}
