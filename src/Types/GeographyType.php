<?php

declare(strict_types=1);

namespace FundiStadi\PostGIS\Types;

/**
 * PostGIS `geography` column (spheroidal calculations), exchanged as GeoJSON text.
 *
 * Identical contract to {@see GeometryType}; only the storage keyword and the write
 * cast differ (GeoJSON parses to geometry, then casts to geography).
 */
final class GeographyType extends GeometryType
{
    public const string NAME = 'geography';

    protected function columnType(): string
    {
        return 'geography';
    }

    protected function fromGeoJsonSql(string $sqlExpr): string
    {
        return \sprintf('ST_GeomFromGeoJSON(%s)::geography', $sqlExpr);
    }
}
