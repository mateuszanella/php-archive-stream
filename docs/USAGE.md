# Usage Reference

The ArchiveManager provides a flexible way to create and manage archives in various formats. This document outlines the basic usage patterns, including creating archives, adding files, and configuring the manager.

## Creating the manager instance

To start using the library, you need to create an instance of the `ArchiveManager` class.:

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

To create an archive, you can use the `create` method of the ArchiveManager. This method accepts the desired destination path, and an optional format string.

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

The second argument is the format of the archive, which is optional. If not provided, the library will attempt to determine the format based on the destination path.

```php
// Specifying the format explicitly
$manager->create('php://output', 'zip');
```

When creating an archive with an array of paths, the library saves the current state of the archive, and simply sends the data through each stream, making the process lightweight and convenient when dealing with simultaneous streams of multiple files.

```php
// Creating two ZIP archives, one in the filesystem and one in an S3 bucket
$manager->create([
    's3://path/to/file.zip', 
    'path/to/local/file.zip'
]);
```

### Streaming an archive to the browser

You may also wish to stream an archive directly to the browser. This can be done by passing `php://output` or `php://stdout` as the destination path. Note that in this case, the format **MUST** be specified, as the library cannot determine it from the destination path alone:

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
