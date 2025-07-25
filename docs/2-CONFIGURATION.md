# Configuration Reference

The ArchiveManager accepts a configuration array that allows you to customize various aspects of archive creation.

## Default Configuration

The default configuration is as follows:

```php
$defaultConfig = [
    /**
     * The factory class used to define which stream is used 
     * for each output on each archive type with the default
     * archive implementations.
     */
    'streamFactory' => StreamFactory::class,
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

### Stream Factory

```php
'streamFactory' => CustomStreamFactory::class
```

Specifies the class used to identify which `WriteStream` will be used for the given format in the default archive implementations. 

Classes must implement the `PhpArchiveStream\Contracts\StreamFactory.php` interface, and will throw a `RuntimeException` otherwise.

### ZIP Configuration

#### Enable ZIP64

Enables the default usage of the ZIP64 format when creating a ZIP archive.

```php
'zip' => [  
    'enableZip64' => true, // Default: true
]
```

> This enables the creation of archives larger than 4GB or with more than 65535 files.

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

## Runtime Configuration

You may also modify configuration at runtime:

```php
$manager = new ArchiveManager;

// Get configuration instance
$config = $manager->config();

// Modify specific values using dot notation
$config->set('zip.enableZip64', false);
$config->set('tar.input.chunkSize', 2048);

// Get values using dot notation
$chunkSize = $config->get('zip.input.chunkSize', 1048576);
```
