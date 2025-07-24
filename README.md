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

```php
<?php

use PhpArchiveStream\ArchiveManager;

$manager = new ArchiveManager();

// Create a ZIP archive
$zip = $manager->create('./archive.zip');
$zip->addFileFromPath('readme.txt', './README.md');
$zip->addFileFromContentString('hello.txt', 'Hello World!');
$zip->finish();

// Create a TAR.GZ archive
$tarGz = $manager->create('./archive.tar.gz'); // Can also be .tgz
$tarGz->addFileFromPath('composer.json', './composer.json');
$tarGz->finish();
```

### HTTP Download

```php
// Stream directly to browser
$zip = $manager->create('php://output');
$zip->addFileFromPath('report.pdf', './reports/monthly.pdf');
$zip->finish();
```

### Multiple Destinations

```php
// Stream to be browser and save a backup on disk
$zip = $manager->create([
    'php://output',
    './archive.zip'
]);
$zip->addFileFromPath('data.json', './data.json');
$zip->finish();
```

## Configuration

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

For detailed documentation, configuration options, and advanced usage, see the [docs/](./docs/) folder:

- [Configuration Reference](./docs/configuration.md)
- [Advanced Usage](./docs/advanced-usage.md)
- [Extending the Library](./docs/extending.md)
- [Architecture Overview](./docs/architecture.md)

## Requirements

- PHP 8.1 or higher

## License

MIT License. See [LICENSE](LICENSE) for details.
