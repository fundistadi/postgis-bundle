<?php

declare(strict_types=1);

namespace FundiStadi\Postgis\ORM\Functions;

/**
 * DQL: ST_AsGeoJSON(geom [, maxdecimaldigits [, options]]) — GeoJSON text of a geometry.
 */
final class StAsGeoJson extends AbstractSpatialFunction
{
    protected function functionName(): string
    {
        return 'ST_AsGeoJSON';
    }

    protected function maxArgs(): int
    {
        return 3;
    }
}
