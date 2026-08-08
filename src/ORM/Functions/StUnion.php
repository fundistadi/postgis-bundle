<?php

declare(strict_types=1);

namespace FundiStadi\PostGISBundle\ORM\Functions;

/**
 * DQL: ST_Union(g) — aggregate union of a set of geometries (dissolve), e.g.
 *   SELECT ST_Union(t.geom) FROM … t GROUP BY t.category
 * (Also accepts the two-argument pairwise form.).
 */
final class StUnion extends AbstractSpatialFunction
{
    protected function functionName(): string
    {
        return 'ST_Union';
    }

    protected function maxArgs(): int
    {
        return 2;
    }
}
