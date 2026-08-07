<?php

declare(strict_types=1);

namespace Fundistadi\Postgis\ORM\Functions;

/**
 * DQL: ST_GeomFromGeoJSON(text) — builds a geometry (SRID 4326) from GeoJSON text.
 */
final class StGeomFromGeoJson extends AbstractSpatialFunction
{
    protected function functionName(): string
    {
        return 'ST_GeomFromGeoJSON';
    }
}
