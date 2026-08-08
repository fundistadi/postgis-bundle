# PostGIS for Symfony!

[![CI](https://github.com/fundistadi/postgis-bundle/actions/workflows/ci.yml/badge.svg)](https://github.com/fundistadi/postgis-bundle/actions/workflows/ci.yml)
[![Latest Version](https://img.shields.io/packagist/v/fundistadi/postgis-bundle.svg)](https://packagist.org/packages/fundistadi/postgis-bundle)
[![Total Downloads](https://img.shields.io/packagist/dt/fundistadi/postgis-bundle.svg)](https://packagist.org/packages/fundistadi/postgis-bundle)
[![License](https://img.shields.io/packagist/l/fundistadi/postgis-bundle.svg)](LICENSE)

This bundle gives Doctrine ORM 3 / DBAL 4 first-class **PostGIS** support: spatial column
types exchanged as **GeoJSON**, `ST_*` DQL functions, **automatic GiST indexing**, and
**typed geometry columns with churn-free migrations** — no hand-written spatial SQL,
no configuration. Enable the bundle and go.

> Part of [FundiStadi](https://github.com/fundistadi) — open-source tooling for web, ops & data.

## Why

Doctrine ships no spatial types, and PostGIS stores every shape as the base `geometry`
type — so the usual approaches either hand-write DDL or fight `migrations:diff` churn.
This bundle handles all of it: declare a column, get a GiST-indexed, GeoJSON-friendly,
optionally shape-constrained column with clean diffs.

## Install

Applications using [Symfony Flex](https://symfony.com/doc/current/setup.html):

```console
composer require fundistadi/postgis-bundle
```

Applications without Symfony Flex — after requiring the package, enable the bundle:

```php
// config/bundles.php
return [
    // ...
    FundiStadi\PostGISBundle\FundiStadiPostGISBundle::class => ['all' => true],
];
```

That one line registers the spatial types, the DB-type mappings, the `ST_*` DQL functions,
the `USING gist` platform middleware, the typmod-aware schema manager, and the auto-GiST
schema listener. Nothing else to configure.

## A taste

```php
use Doctrine\ORM\Mapping as ORM;

class Area
{
    // Generic geometry — accepts any shape, SRID 4326, diff-clean.
    #[ORM\Column(type: 'geometry')]
    public ?string $footprint = null;   // GeoJSON string in, GeoJSON string out

    // Typed — PostGIS enforces the shape via the typmod, still diff-clean.
    #[ORM\Column(type: 'multipolygon')]
    public ?string $boundary = null;    // geometry(MultiPolygon,4326)
}
```

```php
$em->createQuery(
    'SELECT COUNT(a.id) FROM App\Entity\Area a
     WHERE ST_Intersects(a.boundary, ST_GeomFromGeoJSON(:poly)) = true'
)->setParameter('poly', $geoJsonPolygon)->getSingleScalarResult();
```

Column types: `geometry`, `geography`, `point`, `polygon`, `multipolygon`.
DQL functions: `ST_AsGeoJSON`, `ST_GeomFromGeoJSON`, `ST_Intersects`.
Every geometry/geography column gets a `USING gist` index in generated migrations, automatically.

## Documentation

Read the documentation at [docs/index.md](docs/index.md) — including
[generic vs. typed geometry columns](docs/geometry-columns.md) and how typed columns
stay `migrations:diff`-clean.

## Requirements

- PHP **8.4+** · Symfony **7.3+ / 8**
- `doctrine/dbal` **^4**, `doctrine/orm` **^3.5**, `doctrine/doctrine-bundle` **^3**
- PostgreSQL with the **PostGIS** extension

## Contributing

See [CONTRIBUTING.md](CONTRIBUTING.md). CI enforces the standard (php-cs-fixer, PHPStan max,
PHPUnit against real PostGIS, lowest→newest dependency matrix) on every pull request.

## Credits

- [Ezekiel Mjema](https://github.com/eemjema)
- [All Contributors](https://github.com/fundistadi/postgis-bundle/graphs/contributors)

## License

MIT License (MIT): see the [LICENSE](LICENSE) file for more details.