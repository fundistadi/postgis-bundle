<?php

declare(strict_types=1);

namespace FundiStadi\PostGISBundle;

use FundiStadi\PostGISBundle\EventListener\SpatialSchemaListener;
use FundiStadi\PostGISBundle\ORM\Functions\Geography;
use FundiStadi\PostGISBundle\ORM\Functions\StArea;
use FundiStadi\PostGISBundle\ORM\Functions\StAsGeoJson;
use FundiStadi\PostGISBundle\ORM\Functions\StCollectionExtract;
use FundiStadi\PostGISBundle\ORM\Functions\StGeomFromGeoJson;
use FundiStadi\PostGISBundle\ORM\Functions\StIntersects;
use FundiStadi\PostGISBundle\ORM\Functions\StMakeValid;
use FundiStadi\PostGISBundle\ORM\Functions\StMulti;
use FundiStadi\PostGISBundle\ORM\Functions\StSimplifyPreserveTopology;
use FundiStadi\PostGISBundle\ORM\Functions\StUnion;
use FundiStadi\PostGISBundle\Platform\PostGISMiddleware;
use FundiStadi\PostGISBundle\Schema\PostGISSchemaManagerFactory;
use FundiStadi\PostGISBundle\Types\GeographyType;
use FundiStadi\PostGISBundle\Types\GeometryType;
use FundiStadi\PostGISBundle\Types\MultiPolygonType;
use FundiStadi\PostGISBundle\Types\PointType;
use FundiStadi\PostGISBundle\Types\PolygonType;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

/**
 * Enables PostGIS for a Symfony/Doctrine app with zero manual wiring: spatial types
 * (generic + typed sub-types), `ST_*` DQL functions, the `USING gist` platform
 * middleware, the typmod-aware schema manager (clean diffs), and the auto-GiST
 * schema listener. Just add the bundle.
 */
final class FundiStadiPostGISBundle extends AbstractBundle
{
    protected string $extensionAlias = 'fundi_stadi_post_gis';

    // Leading dot: internal services, hidden from `debug:container` (Symfony
    // bundle best practice for services not meant to be used by the app).
    private const SCHEMA_MANAGER_FACTORY_ID = '.fundi_stadi_post_gis.schema_manager_factory';

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
                        'Geography' => Geography::class,
                        'ST_AsGeoJSON' => StAsGeoJson::class,
                        'ST_CollectionExtract' => StCollectionExtract::class,
                        'ST_GeomFromGeoJSON' => StGeomFromGeoJson::class,
                        'ST_MakeValid' => StMakeValid::class,
                        'ST_Multi' => StMulti::class,
                        'ST_SimplifyPreserveTopology' => StSimplifyPreserveTopology::class,
                        'ST_Union' => StUnion::class,
                    ],
                    'numeric_functions' => [
                        'ST_Area' => StArea::class,
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

        $services->set('.fundi_stadi_post_gis.middleware', PostGISMiddleware::class)
            ->tag('doctrine.middleware');

        $services->set('.fundi_stadi_post_gis.schema_listener', SpatialSchemaListener::class)
            ->tag('doctrine.event_listener', ['event' => 'postGenerateSchema']);
    }
}
