<?php

declare(strict_types=1);

namespace FundiStadi\PostGIS\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;

/** `geometry(Point, 4326)`. */
final class PointType extends GeometryType
{
    public const string NAME = 'point';

    protected function defaultGeometryType(): string
    {
        return 'POINT';
    }

    public function getMappedDatabaseTypes(AbstractPlatform $platform): array
    {
        return [];
    }
}
