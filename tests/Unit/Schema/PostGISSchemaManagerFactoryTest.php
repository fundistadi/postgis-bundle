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
