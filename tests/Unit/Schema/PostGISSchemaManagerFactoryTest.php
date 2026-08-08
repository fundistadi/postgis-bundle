<?php

declare(strict_types=1);

namespace FundiStadi\PostGISBundle\Tests\Unit\Schema;

use Doctrine\DBAL\DriverManager;
use FundiStadi\PostGISBundle\Schema\PostGISSchemaManager;
use FundiStadi\PostGISBundle\Schema\PostGISSchemaManagerFactory;
use PHPUnit\Framework\TestCase;

final class PostGISSchemaManagerFactoryTest extends TestCase
{
    public function testCreatesTypmodAwareSchemaManagerForPostgres(): void
    {
        // serverVersion pins the platform, so no connection is ever opened.
        $connection = DriverManager::getConnection([
            'driver' => 'pdo_pgsql',
            'serverVersion' => '17',
        ]);

        $schemaManager = new PostGISSchemaManagerFactory()->createSchemaManager($connection);

        self::assertInstanceOf(PostGISSchemaManager::class, $schemaManager);
    }
}
