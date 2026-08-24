# Configuration Reference

The ArchiveManager accepts a configuration array that allows you to customize various aspects of archive creation.

## Default Configuration

The default configuration is as follows:

```php
$defaultConfig = [
    'zip' => [
        /**
         * Enables ZIP64 support for large archives.
         */
        'enableZip64' => true,
        'input' => ['chunkSize' => 1048576], // 1MB
        'headers' => [
            'Content-Type' => 'application/zip',
            'Content-Disposition' => 'attachment; filename="archive.zip"',
            'Content-Transfer-Encoding' => 'binary',
            'Pragma' => 'public',
            'Cache-Control' => 'public, must-revalidate',
            'Connection' => 'Keep-Alive',
        ],
    ],
    'tar' => [
        'input' => ['chunkSize' => 1048576], // 1MB
        'headers' => [
            'Content-Type' => 'application/x-tar',
            'Content-Disposition' => 'attachment; filename="archive.tar"',
            'Content-Transfer-Encoding' => 'binary',
            'Pragma' => 'public',
            'Cache-Control' => 'public, must-revalidate',
            'Connection' => 'Keep-Alive',
        ],
    ],
    'targz' => [
        'input' => ['chunkSize' => 1048576], // 1MB
        'headers' => [
            'Content-Type' => 'application/x-tar',
            'Content-Disposition' => 'attachment; filename="archive.tar.gz"',
            'Content-Transfer-Encoding' => 'binary',
            'Pragma' => 'public',
            'Cache-Control' => 'public, must-revalidate',
            'Connection' => 'Keep-Alive',
        ],
    ],
];
```

## Configuration Options

### Stream Manager

Streams are resolved by an injectable `StreamManager` rather than through configuration. To customize how a destination resource is wrapped for a given format, inject a configured `StreamManager` (or register custom stream builders on it) into the `ArchiveManager` constructor.

```php
use PhpArchiveStream\ArchiveManager;
use PhpArchiveStream\ConfigManager;
use PhpArchiveStream\StreamManager;
use PhpArchiveStream\DestinationManager;

$streams = new StreamManager;

// Override or add stream builders, e.g.:
// $streams->register('zip', fn ($destination) => new CustomOutputStream($destination));

$manager = new ArchiveManager(new ConfigManager, new DestinationManager($streams));
```

See the [Extending the Library](./4-EXTENDING.md) reference for registering custom stream builders.

### ZIP Configuration

#### Enable ZIP64

Enables the default usage of the ZIP64 format when creating a ZIP archive.

```php
'zip' => [  
    'enableZip64' => true, // Default: true
]
```

> This enables the creation of archives larger than 4GB or with more than 65535 files.

#### Compressor

```php
'zip' => [
    'compressor' => PhpArchiveStream\Compressors\Zip\DeflateCompressor::class, // Default
    'compressorOptions' => ['level' => 9], // Default: []
]
```

Sets the default compressor class used for ZIP entries and the options forwarded
to its `init()` factory. The class must implement
`PhpArchiveStream\Contracts\Zip\ZipCompressor`. The `compressorOptions`
array is passed verbatim to `init()` (e.g. `DeflateCompressor` accepts a `level`).

#### Input Chunk Size

```php
'zip' => [
    'input' => ['chunkSize' => 2097152], // 2MB chunks
]
```

Controls how much data is read from source files at once. Larger chunks can improve performance at the cost of memory usage.

#### HTTP Headers

```php
'zip' => [
    'headers' => [
        'Content-Type' => 'application/zip',
        'Content-Disposition' => 'attachment; filename="custom.zip"',
        'X-Custom-Header' => 'custom-value',
    ],
]
```

Defines custom HTTP headers sent when streaming to `php://output` or `php://stdout`.

### 7Z Configuration

#### Streaming Strategy

The 7z format writes a signature header at the start of the archive that references the metadata header written at the end, so it cannot be streamed to a non-seekable destination without buffering. The `7z.streaming` option controls how packed data is written:

```php
'7z' => [
    'streaming' => 'auto', // 'auto' | 'seek' | 'spool'
]
```

- `auto` (default): streams packed data directly to the output when it is seekable (e.g. a local file), otherwise wraps the destination in a `SpoolWriteStream`.
- `seek`: forces the streaming path, throwing an exception if the output stream is not seekable.
- `spool`: forces the spooling path regardless of the output stream.

This option is consumed by the `StreamManager`, which decides whether to wrap non-seekable destinations in a `SpoolWriteStream`. The `SevenZipWriter` itself is agnostic to this decision, requiring only a seekable output stream. Both strategies produce byte-identical archives; the choice is purely a memory/disk trade-off for the destination in use.

#### Compressor

```php
'7z' => [
    'compressor' => PhpArchiveStream\Compressors\SevenZip\Lzma2Compressor::class, // Default
    'compressorOptions' => ['dictSize' => 1 << 20, 'level' => 9], // Default: []
]
```

Sets the default compressor class used for 7z entries and the options forwarded to
its `init()` factory. The class must implement
`PhpArchiveStream\Contracts\SevenZip\SevenZipCompressor`. The `compressorOptions`
array is passed verbatim to `init()`.

## Runtime Configuration

You may also modify configuration at runtime:

```php
$manager = ArchiveManager::make();

// Get configuration instance
$config = $manager->config();

// Modify specific values using dot notation
$config->set('zip.enableZip64', false);
$config->set('tar.input.chunkSize', 2048);

// Get values using dot notation
$chunkSize = $config->get('zip.input.chunkSize', 1048576);
```
