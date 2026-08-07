<?php

declare(strict_types=1);

namespace Fundistadi\Postgis\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;

/** `geometry(Polygon, 4326)`. */
final class PolygonType extends GeometryType
{
    public const string NAME = 'polygon';

    protected function defaultGeometryType(): string
    {
        return 'POLYGON';
    }

    public function getMappedDatabaseTypes(AbstractPlatform $platform): array
    {
        return [];
    }
}
