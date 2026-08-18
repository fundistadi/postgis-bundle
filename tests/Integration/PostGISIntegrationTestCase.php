<?php

declare(strict_types=1);

/*
 * This file is part of the FundiStadi PostGIS Bundle.
 *
 * (c) Ezekiel Mjema <https://github.com/eemjema>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FundiStadi\PostGISBundle\Tests\Integration;

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
use FundiStadi\PostGISBundle\EventListener\SpatialSchemaListener;
use FundiStadi\PostGISBundle\ORM\Functions\Geography;
use FundiStadi\PostGISBundle\ORM\Functions\StArea;
use FundiStadi\PostGISBundle\ORM\Functions\StAsGeoJson;
use FundiStadi\PostGISBundle\ORM\Functions\StCollectionExtract;
use FundiStadi\PostGISBundle\ORM\Functions\StDWithin;
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
        $ormConfig->addCustomStringFunction('Geography', Geography::class);
        $ormConfig->addCustomStringFunction('ST_AsGeoJSON', StAsGeoJson::class);
        $ormConfig->addCustomStringFunction('ST_CollectionExtract', StCollectionExtract::class);
        $ormConfig->addCustomNumericFunction('ST_DWithin', StDWithin::class);
        $ormConfig->addCustomStringFunction('ST_GeomFromGeoJSON', StGeomFromGeoJson::class);
        $ormConfig->addCustomStringFunction('ST_MakeValid', StMakeValid::class);
        $ormConfig->addCustomStringFunction('ST_Multi', StMulti::class);
        $ormConfig->addCustomStringFunction('ST_SimplifyPreserveTopology', StSimplifyPreserveTopology::class);
        $ormConfig->addCustomStringFunction('ST_Union', StUnion::class);
        $ormConfig->addCustomNumericFunction('ST_Area', StArea::class);
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
