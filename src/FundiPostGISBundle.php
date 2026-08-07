<?php

declare(strict_types=1);

namespace FundiStadi\PostGIS;

use FundiStadi\PostGIS\ORM\Functions\StAsGeoJson;
use FundiStadi\PostGIS\ORM\Functions\StGeomFromGeoJson;
use FundiStadi\PostGIS\ORM\Functions\StIntersects;
use FundiStadi\PostGIS\Platform\PostGISMiddleware;
use FundiStadi\PostGIS\Schema\PostGISSchemaManagerFactory;
use FundiStadi\PostGIS\Schema\SpatialSchemaListener;
use FundiStadi\PostGIS\Types\GeographyType;
use FundiStadi\PostGIS\Types\GeometryType;
use FundiStadi\PostGIS\Types\MultiPolygonType;
use FundiStadi\PostGIS\Types\PointType;
use FundiStadi\PostGIS\Types\PolygonType;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

/**
 * Enables PostGIS for a Symfony/Doctrine app with zero manual wiring: spatial types
 * (generic + typed sub-types), `ST_*` DQL functions, the `USING gist` platform
 * middleware, the typmod-aware schema manager (clean diffs), and the auto-GiST
 * schema listener. Just add the bundle.
 */
final class FundiPostGISBundle extends AbstractBundle
{
    private const SCHEMA_MANAGER_FACTORY_ID = 'fundi_postgis.schema_manager_factory';

    public function prependExtension(ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $builder->prependExtensionConfig('doctrine', [
            'dbal' => [
                'types' => [
                    GeometryType::NAME => GeometryType::class,
                    GeographyType::NAME => GeographyType::class,
                    MultiPolygonType::NAME => MultiPolygonType::class,
                    PointType::NAME => PointType::class,
                    PolygonType::NAME => PolygonType::class,
                ],
                // Map the base PostGIS DB types back to a Doctrine type for
                // introspection; the schema manager then refines to the sub-type.
                'mapping_types' => [
                    'geometry' => GeometryType::NAME,
                    'geography' => GeographyType::NAME,
                ],
                'schema_manager_factory' => self::SCHEMA_MANAGER_FACTORY_ID,
            ],
            'orm' => [
                'dql' => [
                    'string_functions' => [
                        'ST_AsGeoJSON' => StAsGeoJson::class,
                        'ST_GeomFromGeoJSON' => StGeomFromGeoJson::class,
                    ],
                    'numeric_functions' => [
                        'ST_Intersects' => StIntersects::class,
                    ],
                ],
            ],
        ]);
    }

    /**
     * @param array<string, mixed> $config
     */
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $services = $container->services();

        $services->set(self::SCHEMA_MANAGER_FACTORY_ID, PostGISSchemaManagerFactory::class);

        $services->set('fundi_postgis.middleware', PostGISMiddleware::class)
            ->tag('doctrine.middleware');

        $services->set('fundi_postgis.schema_listener', SpatialSchemaListener::class)
            ->tag('doctrine.event_listener', ['event' => 'postGenerateSchema']);
    }
}
