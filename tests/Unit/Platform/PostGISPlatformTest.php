<?php

declare(strict_types=1);

namespace FundiStadi\PostGISBundle\Tests\Unit\Platform;

use Doctrine\DBAL\Schema\Index;
use FundiStadi\PostGISBundle\Platform\PostGISPlatform;
use PHPUnit\Framework\TestCase;

final class PostGISPlatformTest extends TestCase
{
    private PostGISPlatform $platform;

    protected function setUp(): void
    {
        $this->platform = new PostGISPlatform();
    }

    public function testSpatialIndexIsCreatedUsingGist(): void
    {
        $index = new Index('idx_geom', ['geom'], false, false, ['spatial']);

        $sql = $this->platform->getCreateIndexSQL($index, 'spatial_thing');

        self::assertSame('CREATE INDEX idx_geom ON spatial_thing USING gist (geom)', $sql);
    }

    public function testRegularIndexKeepsDefaultAccessMethod(): void
    {
        $index = new Index('idx_name', ['name']);

        $sql = $this->platform->getCreateIndexSQL($index, 'spatial_thing');

        self::assertSame('CREATE INDEX idx_name ON spatial_thing (name)', $sql);
    }
}
