# Usage Reference

The ArchiveManager provides a flexible way to create and manage archives in various formats. This document outlines the basic usage patterns, including creating archives, adding files, and configuring the manager.

## Creating the Manager Instance

To start using the library, you need to create an instance of the `ArchiveManager` class:

```php
use PhpArchiveStream\ArchiveManager;

// Create a manager instance
$manager = new ArchiveManager;
```

### Creating the `ArchiveManager` with Predefined Configuration

You can also create the `ArchiveManager` with a predefined configuration:

```php
use PhpArchiveStream\ArchiveManager;

// Create a manager instance with predefined configuration
$manager = new ArchiveManager([
    'zip' => [
        'enableZip64' => true, // Enable ZIP64 format
        'input' => ['chunkSize' => 2097152] // 2MB chunks
    ],
    ...
]);
```

> If you wish to see all available configuration options, see the [Configuration Reference](./CONFIGURATION.md).

## Creating Archives

To create an archive, you can use the `create` method of the `ArchiveManager`. This method accepts the desired destination path, and an optional format string.

```php
/**
 * Create a new archive instance.
 *
 * @param  string|array<string>  $destination
 */
public function create(string|array $destination, ?string $extension = null): Archive
```

The first argument is the destination path where the archive will be created, this can be either a `string` or an `array` of paths.

> Each destination can be any streamable path, and does accept custom streams such as the default AWS or GCP bucket wrappers (`s3` or `gcs`).

This makes it convenient to create archives in various locations, such as local filesystems, cloud storage, or even directly to the browser.

```php
// Creating a ZIP archive on an S3 bucket
$manager->create('s3://path/to/file1.zip');
```

When creating an archive with an array of paths, the library saves the current state of the archive, and simply sends the data through each stream, making the process lightweight and convenient when dealing with simultaneous streams of multiple files.

```php
// Creating two ZIP archives, one in the filesystem and one in an S3 bucket
$manager->create([
    's3://path/to/file.zip', 
    'path/to/local/file.zip'
]);
```

The second argument is the format of the archive, which is optional. If not provided, the library will attempt to determine the format based on the destination path.

```php
// Specifying the format explicitly
$manager->create('php://output', 'zip');
```

### Streaming an archive to the browser

You may also wish to stream an archive directly to the browser. This can be done by passing `php://output` or `php://stdout` as the destination path. Note that in this case, the format **MUST** be specified, as the library cannot determine it from the destination path alone:

> Notes on streaming a file to the browser:
> - Make sure to pay attention to the HTTP headers in the configuration, and ensure they are correctly set for your use case;
> - In the event that headers have already been sent, the library **WILL NOT** attempt to send them again, and will simply stream the archive data, which may lead to unexpected results in the browser. It is the developer's responsibility to ensure that headers are sent correctly before streaming the archive.

```php
// Streaming a ZIP archive directly to the browser
$manager->create('php://output', 'zip');
```

You may also want to stream the archive to the browser, and save it somewhere at the same time:

```php
// Streaming a ZIP archive to the browser while also saving it to a file
$manager->create([
    'php://output',
    's3://path/to/archive.zip'
]);
```

Note that this time, we don't have to specify the format, as the library is able to determine it based on the destination paths.

### How the library determines the archive format

The process is relatively simple: the library checks for the file extension of the path.

If the user provides the extension, the library will use it and apply it to the archive. This allows for the user to possibly create an archive with a custom extension, such as `.myzip` or `.mytar`. The developer is responsible for ensuring that the extension matches the format of the archive being created.

```php
// `output.something` will be created as a ZIP archive
$manager->create('output.something', 'zip');
```

If the user omits the extension string, the library will then guess the extension through the `DestinationManager` class. 

It starts this process by plucking all of the extensions and compiles them to an array. If there is more than one unique extension, the library will throw an exception. In the same fashion, if the library cannot determine the extension from the path (such as when using `php://output`), it will also throw an exception.

#### Examples of valid paths:

```php
// This is valid, since the extension can be extracted
$manager->create('archive.zip');

// This is also valid, since the extension has been specified
$manager->create('php://output', 'zip');

// This is valid, since the extension can be extracted from the first path
$manager->create([
    'archive.zip',
    'php://output'
]);

// This may also be valid, since the zip extension will be used
$manager->create([
    'archive.unknown',
    'archive.unknown2'
], 'zip');
```

#### Examples of invalid paths:

```php
// This is invalid, since the extension cannot be extracted
$manager->create('php://output');

// This is invalid, since multiple extensions are present
$manager->create([
    'archive.zip',
    'php://output',
    'archive.tar'
]);
```

## Registering an Alias for an Extension

You can register an alias for an archive format by using the `alias` method. This allows you to use a custom name for an existing format.

```php
/**
 * Register a new driver alias.
 */
public function alias(string $alias, string $extension): void
```

For example, there may be many naming conventions for gunzipped TAR files, such as `tar.gz`, `tgz`, or `tar.gzip`.

In this case, the library registers an alias as follows:

```php
$manager->alias('tgz', 'tar.gz');
```

You may use this function to register any aliases you wish.

## Retrieving the Configuration Instance

You can retrieve the configuration instance from the `ArchiveManager` using the `config` method.

```php
$manager->config();
```

> If you wish to see all available configuration options, see the [Configuration Reference](./CONFIGURATION.md).

## Registering New Archive Formats

You can register new archive formats by using the `register` method. This allows you to extend the library with custom archive formats, or override existing ones.

```php
/**
 * Register a new driver.
 *
 * @param  callable(string|array<string>, \PhpArchiveStream\Config): Archive  $factory
 */
public function register(string $extension, callable $factory): void
```

The `register` method accepts the extension of the archive format and a factory function that returns an instance of the `Archive` class.

The Closure should accept the destination path and the current configuration instance as parameters, and return an instance that implements the `Archive` interface.

Example of how the library registers the `zip` archive format:

```php
use PhpArchiveStream\ArchiveManager;

$manager = new ArchiveManager;

$manager->register('zip', function (string|array $destination, Config $config) {
    $useZip64 = $config->get('zip.enableZip64', true);
    $defaultChunkSize = $config->get('zip.input.chunkSize', 1048576);

    $headers = $config->get('zip.headers');

    $outputStream = $this->destination->getStream($destination, 'zip', $headers);

    return new Zip(
        $useZip64
            ? new Zip64Writer($outputStream)
            : new ZipWriter($outputStream),
        $defaultChunkSize
    );
});
```

This approach allows for a high degree of flexibility, enabling you to create custom archive formats that suit your specific needs. Feel free to share new implementations with the community, as they may be useful to others as well.

> If you want a more comprehensive guide on how to extend these modules, see the [Extending the Library](./EXTENDING.md) reference.

## Using Archives

The `create` method returns an instance of the `Archive` class, which provides methods to add files and finish the archive.

The `Archive` class represents a specific archive format, such as ZIP or TAR, and provides methods to manipulate the archive.

### Adding Files to the Archive

The classes provide three methods to add files to the archive:

- `addFileFromPath(string $fileName, string $filePath)`: Adds a file from a streamable valid filepath.
- `addFileFromStream(string $fileName, resource $stream)`: Adds a file from a stream resource.
- `addFileFromString(string $fileName, string $content)`: Adds a file from a string.

```php
// Adding a file from a path
$archive->addFileFromPath('file.txt', '/path/to/file.txt');

// Adding a file from a stream
$stream = fopen('/path/to/file.txt', 'r');
$archive->addFileFromStream('file.txt', $stream);
fclose($stream);

// Adding a file from a string
$archive->addFileFromString('file.txt', 'File content goes here.');
```

### Setting the Default Read Chunk Size at runtime

You can set the default read chunk size for the archive using the `setDefaultReadChunkSize(int $chunkSize)` method. This is useful for dinamically controlling how much data is read from the source files when adding them to the archive.

```php
$archive->setDefaultReadChunkSize(8192); // Will start reading files in 8KB chunks
```

### Zip-Specific Features

The `Zip` class provides additional methods for ZIP-specific features, such as setting the active compression algorithm:

```php
// Setting the default compressor for ZIP archives
$zipArchive->setDefaultCompressor(PhpArchiveStream\Compressor\DeflateCompressor::class);
```

This method sets the current compression algorithm being used by the archive.

Currently, the library supports the following compressors:

- `PhpArchiveStream\Compressor\DeflateCompressor`: The default compressor, which uses the DEFLATE algorithm.
- `PhpArchiveStream\Compressor\StoreCompressor`: Uses the STORE algorithm, which does not compress the data.

The function allows you to set custom compressors as well, as long as they implement the `Compressor` interface.

> If you want a more comprehensive guide on how to extend these modules, see the [Extending the Library](./EXTENDING.md) reference. 

### Finishing the Archive

To finish the archive creation, you must call the `finish()` method on the `Archive` instance. This method finalizes the archive and writes it to the destination stream.

```php
$archive->finish();
```

Note that after calling this method, the archive is considered complete, and you cannot add more files to it. Trying to do so will result in an exception being thrown.
