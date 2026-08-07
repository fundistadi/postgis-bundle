<?php

declare(strict_types=1);

namespace Fundistadi\Postgis\Platform;

use Doctrine\DBAL\Driver;
use Doctrine\DBAL\Driver\Middleware;
use Doctrine\DBAL\Driver\Middleware\AbstractDriverMiddleware;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\ServerVersionProvider;

/**
 * DBAL middleware that makes a PostgreSQL connection use {@see PostGISPlatform},
 * so spatial indexes render with `USING gist`. Register it on the DBAL
 * Configuration (standalone) or via the `doctrine.middleware` tag (Symfony).
 */
final class PostGISMiddleware implements Middleware
{
    public function wrap(Driver $driver): Driver
    {
        return new class($driver) extends AbstractDriverMiddleware {
            public function getDatabasePlatform(ServerVersionProvider $versionProvider): AbstractPlatform
            {
                return new PostGISPlatform();
            }
        };
    }
}
