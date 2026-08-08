<?php

declare(strict_types=1);

namespace FundiStadi\PostGISBundle\ORM\Functions;

/**
 * DQL: ST_Area(g) — area of a geometry. On geometry the unit is planar
 * (squared CRS units — degrees² for EPSG:4326!); wrap in Geography() for
 * geodesic square metres: ST_Area(Geography(t.geom)).
 */
final class StArea extends AbstractSpatialFunction
{
    protected function functionName(): string
    {
        return 'ST_Area';
    }
}
