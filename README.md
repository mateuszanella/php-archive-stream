# PHP Archive Stream

A modular and lightweight PHP library for creating ZIP and TAR archives on-the-fly with streaming support. Perfect for generating large archives without consuming excessive memory.

## Features

- 🚀 Stream-based archive creation (low memory usage)
- 📦 Support for ZIP, TAR, TAR.GZ, TAR.BZ2, TAR.XZ and 7z formats
- 🔧 Configurable compression and chunk sizes
- 🌐 HTTP download support with proper headers
- 📁 Multiple output destinations (file, HTTP, custom streams)
- 🔌 Extensible architecture for custom formats

## Installation

```bash
composer require mateuszanella/php-archive-stream
```

## Quick Start

### Basic Usage

To get started, include the library and create an `ArchiveManager` instance:

```php
<?php

use PhpArchiveStream\ArchiveManager;

// Create a manager instance
$manager = ArchiveManager::make();
```

### Creating Archives

You can create different types of archives using the `create` method. The format is inferred from the destination extension (matching is case-insensitive):

```php
$zip = $manager->create('./archive.zip');
$tar = $manager->create('./archive.tar');
$tarGz = $manager->create('./archive.tar.gz');
$tarGz = $manager->create('./archive.tgz');   // alias for tar.gz
$tarBz2 = $manager->create('./archive.tar.bz2');
$tarBz2 = $manager->create('./archive.tbz2'); // alias for tar.bz2
$tarXz = $manager->create('./archive.tar.xz');
$tarXz = $manager->create('./archive.txz');   // alias for tar.xz
$sevenZip = $manager->create('./archive.7z');
```

> `tar.gz`, `tar.bz2`, `tar.xz` and `7z` require the `ext-zlib`, `ext-bz2`, `ext-xz` extensions respectively. See the [`suggest`](./composer.json) section in `composer.json`.

> The destination is opened for writing immediately when the archive is created — any existing file at that path is truncated at that point, before files are added.

### Adding Files

You can add files to the archive using various methods. All `addFile*()` calls and `finish()` are fluent and return the archive, so they can be chained:

```php
$archive->addFileFromPath('report.pdf', './reports/monthly.pdf');
$archive->addFileFromStream('data.json', fopen('./data.json', 'rb'));
$archive->addFileFromContentString('notes.txt', 'Important notes about the project.');
$archive->finish();
```

> When passing a stream to `addFileFromStream()`, ownership is transferred to the library — the stream is read from its current position and will be closed by the archive, so the caller must not `fclose()` it afterwards.

### HTTP Download

To stream the archive directly to the browser, you can create the archive with `php://output` as the destination:

```php
// Stream directly to browser
$zip = $manager->create('php://output', 'zip');
$zip->addFileFromPath('report.pdf', './reports/monthly.pdf');
$zip->finish();
```

### Multiple Destinations

You can specify multiple destinations for the archive by passing an array to the `create` method:

```php
// Stream the archive to the browser and save a backup on disk
$zip = $manager->create([
    'php://output',
    './archive.zip'
]);
$zip->addFileFromPath('data.json', './data.json');
$zip->finish();
```

> See more in the [Usage Documentation](./docs/1-USAGE.md).

## Configuration

You can customize the behavior of the archive manager using a configuration array. This allows you to set options like chunk sizes, compression methods, and more.

> See more in the [Configuration Reference](./docs/2-CONFIGURATION.md).

```php
$config = [
    'zip' => [
        'enableZip64' => true,
        'input' => ['chunkSize' => 1048576], // 1MB chunks
    ],
    'tar' => [
        'input' => ['chunkSize' => 512], // 512B chunks
    ]
];

$manager = ArchiveManager::make($config);
```

## Documentation

For detailed documentation, configuration options, and advanced usage, see the [documentation](./docs/) folder:

- [Configuration Reference](./docs/2-CONFIGURATION.md)
- [Advanced Usage](./docs/1-USAGE.md)
- [Architecture Overview](./docs/3-ARCHITECTURE.md)
- [Extending the Library](./docs/4-EXTENDING.md)

## Requirements

- PHP 8.3 or higher

## Testing

```bash
composer install
composer test     # run PHPUnit
composer analyse  # run PHPStan (static analysis)
composer lint     # run Pint (code style)
```

## Security

If you discover a security vulnerability within this package, please open a private report through the GitHub security advisory feature rather than a public issue. All security vulnerabilities will be promptly addressed.

## Versioning

This project follows [Semantic Versioning](https://semver.org/). Public, stable APIs are marked with the `@api` annotation in the source. Anything not marked `@api` is `@internal` and may change without notice between releases.

Deprecated features, when any, are announced in the release notes and follow standard semver release cycles before removal. No backwards-compatible API is removed except in a major release.

## License

MIT License. See [LICENSE](LICENSE) for details.

## Contact

For questions, issues, or contributions, please open an issue on the [GitHub repository](https://github.com/mateuszanella/php-archive-stream), or email me at [mateusblk1@gmail.com](mailto:mateusblk1@gmail.com).
