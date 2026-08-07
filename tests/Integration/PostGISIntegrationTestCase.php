<?php

declare(strict_types=1);

namespace FundiStadi\PostGIS\Tests\Integration;

use Doctrine\Common\EventManager;
use Doctrine\DBAL\Configuration as DbalConfiguration;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Tools\DsnParser;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMSetup;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\Tools\ToolEvents;
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
use PHPUnit\Framework\TestCase;

/**
 * Base for tests that run against a real PostGIS database. Set DATABASE_URL to
 * enable them; otherwise they skip. Builds a standalone EntityManager wired with
 * the middleware (PostGISPlatform), the spatial types, the ST_* DQL functions,
 * and the schema listener — i.e. everything the bundle wires in an app.
 */
abstract class PostGISIntegrationTestCase extends TestCase
{
    protected EntityManagerInterface $em;

    protected function setUp(): void
    {
        $url = getenv('DATABASE_URL');
        if (!\is_string($url) || '' === $url) {
            self::markTestSkipped('Set DATABASE_URL to run PostGIS integration tests.');
        }

        if (!Type::hasType(GeometryType::NAME)) {
            Type::addType(GeometryType::NAME, GeometryType::class);
        }
        if (!Type::hasType(GeographyType::NAME)) {
            Type::addType(GeographyType::NAME, GeographyType::class);
        }
        foreach ([
            MultiPolygonType::NAME => MultiPolygonType::class,
            PointType::NAME => PointType::class,
            PolygonType::NAME => PolygonType::class,
        ] as $name => $class) {
            if (!Type::hasType($name)) {
                Type::addType($name, $class);
            }
        }

        $ormConfig = ORMSetup::createAttributeMetadataConfiguration([__DIR__.'/Fixtures'], true);
        $ormConfig->enableNativeLazyObjects(true); // PHP 8.4+ native lazy objects (no proxy-gen dep)
        $ormConfig->addCustomStringFunction('ST_AsGeoJSON', StAsGeoJson::class);
        $ormConfig->addCustomStringFunction('ST_GeomFromGeoJSON', StGeomFromGeoJson::class);
        $ormConfig->addCustomNumericFunction('ST_Intersects', StIntersects::class);

        $dbalConfig = new DbalConfiguration();
        $dbalConfig->setMiddlewares([new PostGISMiddleware()]);
        $dbalConfig->setSchemaManagerFactory(new PostGISSchemaManagerFactory());

        $params = (new DsnParser(['postgres' => 'pdo_pgsql', 'postgresql' => 'pdo_pgsql']))->parse($url);
        $connection = DriverManager::getConnection($params, $dbalConfig);

        // The base geometry/geography DB types must map to a Doctrine type for
        // introspection; the SchemaManager then refines to the typed sub-type.
        $platform = $connection->getDatabasePlatform();
        $platform->registerDoctrineTypeMapping('geometry', GeometryType::NAME);
        $platform->registerDoctrineTypeMapping('geography', GeographyType::NAME);

        $eventManager = new EventManager();
        $eventManager->addEventListener([ToolEvents::postGenerateSchema], new SpatialSchemaListener());

        $this->em = new EntityManager($connection, $ormConfig, $eventManager);

        $tool = new SchemaTool($this->em);
        $classes = $this->em->getMetadataFactory()->getAllMetadata();
        $tool->dropSchema($classes);
        $tool->createSchema($classes);
    }

    protected function tearDown(): void
    {
        if (!isset($this->em)) {
            return;
        }
        $tool = new SchemaTool($this->em);
        $tool->dropSchema($this->em->getMetadataFactory()->getAllMetadata());
        $this->em->getConnection()->close();
    }
}
