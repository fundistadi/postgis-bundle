<?php

declare(strict_types=1);

namespace FundiStadi\PostGISBundle\ORM\Functions;

/**
 * DQL: ST_SimplifyPreserveTopology(g, tolerance) — Douglas-Peucker
 * simplification that never produces invalid or collapsed geometries.
 */
final class StSimplifyPreserveTopology extends AbstractSpatialFunction
{
    protected function functionName(): string
    {
        return 'ST_SimplifyPreserveTopology';
    }

    protected function minArgs(): int
    {
        return 2;
    }
}
