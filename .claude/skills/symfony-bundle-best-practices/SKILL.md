---
name: symfony-bundle-best-practices
description: Symfony reusable-bundle best practices (naming, structure, composer, services, CI). Apply whenever creating/renaming classes, services, config, docs, or CI in this bundle.
---

# Symfony Reusable Bundle Best Practices

Condensed from https://symfony.com/doc/current/bundles/best_practices.html — binding for this repo unless the user explicitly opts out of a rule.

## Naming

- Bundle class: `<Vendor><Name>Bundle`, StudlyCaps, name descriptive and short (max two words). Example: namespace `Acme\BlogBundle` → class `AcmeBlogBundle`. For us: vendor `FundiStadi`.
- Bundle alias: lowercase underscore version of the class name minus `Bundle` (e.g. `acme_blog`). Used as the prefix for **all** routes, services, and parameters. If the auto-derived alias (Container::underscore) is not what you want, set `protected string $extensionAlias` explicitly on the bundle class.
- Public repository name: this org's convention is repo name = the kebab-case package short name, so the GitHub URL and the Packagist name line up exactly (`fundistadi/postgis-bundle`). The vendor prefix would be redundant inside the org. (Deliberate deviation from the official doc's "repo = bundle class name".)
- composer.json `name`: `vendor/<name>-bundle` — drop the vendor prefix from the class name, kebab-case, append `-bundle` (`AcmeBlogBundle` → `acme/blog-bundle`).
- Exception classes go in an `Exception` sub-namespace; event-dispatcher classes end in `Listener`.

## Directory structure

```
<bundle>/
├── assets/           # source assets (.scss, .ts, Stimulus)
├── config/           # routes, services, doctrine XML mapping, validation, serialization
├── docs/index.md     # MANDATORY documentation entry point
├── public/           # compiled web assets
├── src/              # PSR-4 root; Command/, Controller/, DependencyInjection/, EventListener/, Entity/ ...
├── templates/        # Twig only, no main layout
├── tests/
├── translations/     # XLIFF, domain = bundle name (AcmeBlog.en.xlf), never override other bundles
├── LICENSE           # full license text (MIT typical)
└── README.md         # description, install steps (Flex + manual config/bundles.php), examples
```

- Max 2 levels of subdirectories under `src/`.
- Bundle dir is read-only at runtime; write temp files to the host app's cache/log.
- Reference resources by physical path (`__DIR__.'/config/services.xml'`), never `@AcmeBlogBundle/...` logical paths.
- Do not embed third-party PHP/JS/CSS libraries — depend on them via Composer.

## composer.json

- `"type": "symfony-bundle"` (lets Flex auto-enable), `"license"` = valid SPDX id, PSR-4 `autoload` → `src/`, `autoload-dev` → `tests/`.
- Follow Semantic Versioning. Register on Packagist. Provide a Flex recipe for any setup beyond enabling the bundle.

## Services & configuration

- Define services **explicitly** — no autowiring/autoconfiguration inside the bundle.
- Service ids: `<alias>.service_name`. Internal/private services not meant for app use: prefix with a dot (`.acme_blog.logger`) so they're hidden from `debug:container`.
- For services apps should type-hint, add an alias from the interface/class to the service id.
- Parameters: `<alias>.section.setting`. Use semantic config (Configuration class / configure()) for anything non-trivial.
- Doctrine mapping for bundle-shipped entities: XML in `config/doctrine/` so apps can override (not possible with attributes).

## TDD (binding for this repo)

- **Test-first, always.** No production code without a failing test that demands it: write the test, watch it fail (red), implement, watch it pass (green), refactor.
- Pure logic (SQL declarations, value conversion, DQL `getSql()`) → table-driven unit tests in `tests/Unit/`, no kernel, no DB. Database behaviour (round-trips, index creation, schema diffs, live `ST_*` calls) → `tests/Integration/` against real PostGIS.
- Refactors and renames ship on the existing green suite: run it before and after; never change tests and production code in the same step.
- Coverage gaps in already-shipped code are the one test-after case: add tests that pin current behaviour, without touching production code in the same commit.
- Green gate before every commit: `composer cs:fix`, then `composer check`.

## Tests & CI

- PHPUnit, in `tests/`, runnable with plain `phpunit`; ship `phpunit.dist.xml`; ≥95% code coverage; no `AllTests.php`.
- CI matrix must cover: lowest deps (`composer update --prefer-lowest`), every supported PHP version, every supported major Symfony version (pin via `SYMFONY_REQUIRE` + Flex).
- Set `SYMFONY_DEPRECATIONS_HELPER=max[direct]=0` (and `disabled=1` on the prefer-lowest job).
- Cache `$HOME/.composer/cache/files`, never `vendor/`.

## Documentation

- Full PHPDoc on all public classes/methods.
- `docs/index.md` is the mandatory entry point; README links to it and shows standardized install instructions (Flex one-liner + manual `config/bundles.php` snippet, version constraint when not targeting latest).