# PHP Archive Stream

A modular and lightweight PHP library for creating ZIP and TAR archives on-the-fly with streaming support. Perfect for generating large archives without consuming excessive memory.

## Features

- 🚀 Stream-based archive creation (low memory usage)
- 📦 Support for ZIP, TAR, and TAR.GZ formats
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
$manager = new ArchiveManager;
```

### Creating Archives

You can create different types of archives (ZIP, TAR, TAR.GZ) using the `create` method:

```php
$zip = $manager->create('./archive.zip');
$tar = $manager->create('./archive.tar');
$tarGz = $manager->create('./archive.tar.gz');
$tarGz = $manager->create('./archive.tgz');
```

### Adding Files

You can add files to the archive using various methods:

```php
$archive->addFileFromPath('report.pdf', './reports/monthly.pdf');
$archive->addFileFromStream('data.json', fopen('./data.json', 'rb'));
$archive->addFileFromContentString('notes.txt', 'Important notes about the project.');
```

### Finishing the Archive

To finalize the archive and write it to the destination, call the `finish` method:

```php
$archive->finish();
```

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

$manager = new ArchiveManager($config);
```

## Documentation

For detailed documentation, configuration options, and advanced usage, see the [documentation](./docs/) folder:

- [Configuration Reference](./docs/2-CONFIGURATION.md)
- [Advanced Usage](./docs/1-USAGE.md)
- [Architecture Overview](./docs/3-ARCHITECTURE.md)
- [Extending the Library](./docs/4-EXTENDING.md)

## Requirements

- PHP 8.3 or higher

## License

MIT License. See [LICENSE](LICENSE) for details.

## Contact

For questions, issues, or contributions, please open an issue on the [GitHub repository](https://github.com/mateuszanella/php-archive-stream), or email me at [mateusblk1@gmail.com](mailto:mateusblk1@gmail.com).
