<?php

declare(strict_types=1);

namespace Fundistadi\Postgis\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;

/**
 * `geometry(MultiPolygon, 4326)` — PostGIS enforces the shape via the typmod.
 * Diffs stay clean thanks to {@see \Fundistadi\Postgis\Schema\PostGISSchemaManager}.
 */
final class MultiPolygonType extends GeometryType
{
    public const string NAME = 'multipolygon';

    protected function defaultGeometryType(): string
    {
        return 'MULTIPOLYGON';
    }

    /** Only the generic `geometry` type maps the DB type; the SchemaManager refines. */
    public function getMappedDatabaseTypes(AbstractPlatform $platform): array
    {
        return [];
    }
}
