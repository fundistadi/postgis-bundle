<?php

declare(strict_types=1);

namespace FundiStadi\PostGISBundle\ORM\Functions;

/**
 * DQL: ST_Multi(g) — promotes a geometry to its multi-variant (Polygon →
 * MultiPolygon), matching typed multi* columns.
 */
final class StMulti extends AbstractSpatialFunction
{
    protected function functionName(): string
    {
        return 'ST_Multi';
    }
}
