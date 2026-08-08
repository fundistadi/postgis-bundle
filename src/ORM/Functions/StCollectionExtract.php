<?php

declare(strict_types=1);

namespace FundiStadi\PostGISBundle\ORM\Functions;

/**
 * DQL: ST_CollectionExtract(g, type) — keeps only components of the given type
 * from a collection (1 = points, 2 = lines, 3 = polygons).
 */
final class StCollectionExtract extends AbstractSpatialFunction
{
    protected function functionName(): string
    {
        return 'ST_CollectionExtract';
    }

    protected function minArgs(): int
    {
        return 2;
    }
}
