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

namespace FundiStadi\PostGISBundle\Tests\Unit\Types;

use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Types\Type;
use FundiStadi\PostGISBundle\Types\MultiPolygonType;
use FundiStadi\PostGISBundle\Types\PointType;
use FundiStadi\PostGISBundle\Types\PolygonType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class TypedSubTypesTest extends TestCase
{
    /**
     * @param class-string<Type> $typeClass
     */
    #[DataProvider('subTypeProvider')]
    public function testDeclarationCarriesTheShapeTypmod(string $name, string $typeClass, string $expectedDeclaration): void
    {
        if (!Type::hasType($name)) {
            Type::addType($name, $typeClass);
        }

        $type = Type::getType($name);
        $platform = new PostgreSQLPlatform();

        self::assertSame($expectedDeclaration, $type->getSQLDeclaration([], $platform));

        // Sub-types must not remap the base 'geometry' DB type: introspection maps it
        // to the generic type first, then the schema manager refines via the typmod.
        self::assertSame([], $type->getMappedDatabaseTypes($platform));
    }

    /**
     * @return iterable<string, array{string, class-string<Type>, string}>
     */
    public static function subTypeProvider(): iterable
    {
        yield 'point' => [PointType::NAME, PointType::class, 'geometry(POINT,4326)'];
        yield 'polygon' => [PolygonType::NAME, PolygonType::class, 'geometry(POLYGON,4326)'];
        yield 'multipolygon' => [MultiPolygonType::NAME, MultiPolygonType::class, 'geometry(MULTIPOLYGON,4326)'];
    }
}
