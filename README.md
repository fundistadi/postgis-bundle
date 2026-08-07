# fundi-postgis

First-class **PostGIS** support for **Doctrine ORM 3 / DBAL 4** on **Symfony** — spatial
types exchanged as GeoJSON, `ST_*` DQL functions, **automatic GiST indexing**, and
**typed geometry columns with churn-free migrations**. Enable the bundle and go.

> Part of the [Fundistadi](https://github.com/fundistadi) toolset.

## Why

Doctrine ships no spatial types, and PostGIS stores every shape as the base `geometry`
type — so the usual approaches either hand-write DDL or fight `migrations:diff` churn.
`fundi-postgis` handles all of it: declare a column, get a GiST-indexed, GeoJSON-friendly,
optionally shape-constrained column with clean diffs.

## Requirements

- PHP **8.4+**
- `doctrine/dbal` **^4**, `doctrine/orm` **^3**, `doctrine/doctrine-bundle` **^3**
- Symfony **7.3+ / 8**
- PostgreSQL with the **PostGIS** extension

## Install

```bash
composer require fundistadi/fundi-postgis
```

Enable the bundle:

```php
// config/bundles.php
return [
    // ...
    FundiStadi\PostGIS\FundiPostGISBundle::class => ['all' => true],
];
```

That one line registers the spatial types, the DB-type mappings, the `ST_*` DQL functions,
the `USING gist` platform middleware, the typmod-aware schema manager, and the auto-GiST
schema listener. Nothing else to configure.

## Usage

### Columns

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

Values are plain **GeoJSON strings** both ways:

```php
$area->boundary = '{"type":"MultiPolygon","coordinates":[[[[36,-4],[37,-4],[37,-3],[36,-3],[36,-4]]]]}';
$em->persist($area);
$em->flush();
// $reloaded->boundary === '{"type":"MultiPolygon", ... }'
```

Available column types: `geometry`, `geography`, `point`, `polygon`, `multipolygon`
(more shape sub-types are trivial to add — see `src/Types`).

### Spatial queries (DQL)

```php
$em->createQuery(
    'SELECT COUNT(a.id) FROM App\Entity\Area a
     WHERE ST_Intersects(a.boundary, ST_GeomFromGeoJSON(:poly)) = true'
)->setParameter('poly', $geoJsonPolygon)->getSingleScalarResult();
```

Registered functions: `ST_AsGeoJSON`, `ST_GeomFromGeoJSON`, `ST_Intersects`.

### Automatic GiST indexes

Every geometry/geography column gets a `USING gist` index automatically in the generated
schema and migrations — no hand-written DDL.

## Generic vs. typed columns

| | `geometry` | `multipolygon` (typed) |
|---|---|---|
| SRID enforced | ✅ | ✅ |
| Shape enforced by the DB | ❌ (any shape) | ✅ `geometry(MultiPolygon,4326)` |
| `migrations:diff` clean | ✅ | ✅ (typmod-aware schema manager) |

See [`docs/geometry-columns.md`](docs/geometry-columns.md) for the details.

## Contributing

See [CONTRIBUTING.md](CONTRIBUTING.md). CI enforces the standard (php-cs-fixer, PHPStan max,
PHPUnit against real PostGIS) on every pull request.

## License

MIT.
