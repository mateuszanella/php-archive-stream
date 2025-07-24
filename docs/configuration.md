# Configuration Reference

The ArchiveManager accepts a configuration array that allows you to customize various aspects of archive creation.

## Default Configuration

```php
$defaultConfig = [
    'streamFactory' => StreamFactory::class,
    'zip' => [
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

### Stream Factory

```php
'streamFactory' => CustomStreamFactory::class
```

Specifies the class used to create streams for different output types. Must implement `StreamFactoryContract`.

### ZIP Configuration

#### Enable ZIP64

```php
'zip' => [
    'enableZip64' => true, // Default: true
]
```

Enables ZIP64 format support for archives larger than 4GB or with more than 65535 files.

#### Input Chunk Size

```php
'zip' => [
    'input' => ['chunkSize' => 2097152], // 2MB chunks
]
```

Controls how much data is read from source files at once. Larger chunks can improve performance but use more memory.

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

Custom HTTP headers sent when streaming to `php://output` or `php://stdout`.

### TAR Configuration

Similar to ZIP configuration but for TAR archives:

```php
'tar' => [
    'input' => ['chunkSize' => 512], // Smaller chunks for TAR
    'headers' => [
        'Content-Type' => 'application/x-tar',
        'Content-Disposition' => 'attachment; filename="backup.tar"',
    ],
]
```

### TAR.GZ Configuration

```php
'targz' => [
    'input' => ['chunkSize' => 1048576],
    'headers' => [
        'Content-Type' => 'application/gzip',
        'Content-Disposition' => 'attachment; filename="compressed.tar.gz"',
    ],
]
```

## Runtime Configuration

You can also modify configuration at runtime:

```php
$manager = new ArchiveManager();

// Get configuration instance
$config = $manager->config();

// Modify specific values
$config->set('zip.enableZip64', false);
$config->set('tar.input.chunkSize', 2048);

// Get values
$chunkSize = $config->get('zip.input.chunkSize', 1048576);
```

## Performance Tuning

### Chunk Size Guidelines

- **Small files (< 1MB)**: Use smaller chunks (64KB - 256KB)
- **Large files (> 10MB)**: Use larger chunks (1MB - 4MB)
- **Memory constrained**: Use smaller chunks
- **Performance critical**: Use larger chunks

### Example Performance Configuration

```php
$performanceConfig = [
    'zip' => [
        'input' => ['chunkSize' => 4194304], // 4MB for better performance
    ],
    'tar' => [
        'input' => ['chunkSize' => 2097152], // 2MB
    ],
];

$manager = new ArchiveManager($performanceConfig);
```
