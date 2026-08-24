# TODO

A backlog of improvements to bring `php-archive-stream` up to production-library standards (Spatie/Symfony level). Ordered by impact.

## 1. Strictness & static analysis (highest signal)

- [ ] Add `declare(strict_types=1);` to all `src/` and `tests/` files.
- [ ] Add PHPStan (`larastan`/`phpstan` at max level) as a dev dependency + `composer analyse` script.
- [ ] Add Rector as a dev dependency + script.
- [ ] Add Infection (mutation testing) or at least a coverage threshold in `phpunit.xml`.
- [ ] Fix the type smells PHPStan will surface:
  - [ ] Type the `$stream` param in `Archive::addFileFromStream()` (`src/Archives/Zip.php:64`, `Tar`, `SevenZip`).
  - [ ] Fix `StreamManager::openWith()` `@return resource|bool` (`src/StreamManager.php:134`).

## 2. Correctness / UX fixes (bugs a reviewer would block on)

- [ ] Stop truncating the destination at `create()` — open lazily or at `finish()` (`StreamManager::openWith` runs `'wb'` eagerly).
- [ ] Fix `InputStream::size()` returning `0` for non-regular streams (pipes/HTTP) → corrupt TAR/7z archives (`src/IO/Input/InputStream.php:84`).
- [ ] Fix `addFileFromStream()` stream ownership + no-rewind semantics (double-close with the documented `fclose()`).
- [ ] Make the default `Content-Disposition` filename reflect the real destination name instead of hardcoded `archive.zip` (`src/ConfigManager.php:16`).

## 3. Exceptions

- [ ] Introduce a base `ArchiveStreamException` + a small `Exceptions\` hierarchy.
- [ ] Replace generic `\Exception` in `ArchiveManager::create()`/`alias()` (`src/ArchiveManager.php:85,105`) with specific exceptions.

## 4. API ergonomics

- [ ] Make `addFile*()` and `finish()` fluent (return `$this`) to allow chaining.
- [ ] Add `addDirectory()` / recursive directory support.
- [ ] Support case-insensitive extension matching (`Archive.ZIP` → zip).
- [ ] Split `Utils` (`src/Utils.php`) into focused value objects; review `checksum()` byte-indexing on binary data.

## 5. Packaging & metadata

- [ ] Add `composer.json` fields: `keywords`, `support`, `suggest` (`ext-zlib`, `ext-xz`), `extra`/branch-alias.
- [ ] Add `composer.json` scripts: `test`, `analyse`, `format`.
- [ ] Create the missing files referenced by `.gitattributes`: `CHANGELOG.md`, `CONTRIBUTING.md`.
- [ ] Add `SECURITY.md`, `CODE_OF_CONDUCT.md`, and GitHub issue templates.
- [ ] Add Dependabot config.
- [ ] Matrix-test PHP 8.3 + 8.4 in CI, and add a static-analysis job (`phpstan`) + `composer normalize` to the workflow.
- [ ] Decide on `composer.lock` (libraries typically gitignore it).
- [ ] README: add badges, Testing / Security / Credits sections.

## 6. Documentation

- [ ] Add inline `@api` / `@internal` annotations to mark the public API surface.
- [ ] Document resource-ownership rules for `addFileFromStream()`.
- [ ] Add a deprecation/semver policy note.
