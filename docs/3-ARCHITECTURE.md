# Architecture Overview

This document provides an overview of the **PHP Archive Stream** library architecture and its core components.

## High-Level Architecture

As a general overview, the architecture can be visualized as follows:

> This should become an image in the future, but for now just text is fine.

```
┌─────────────────┐    ┌────────────────────┐    ┌─────────────────┐
│   Application   │───▶│   ArchiveManager   │───▶│     Archive     │
│                 │    │                    │    │    (Zip/Tar)    │
└─────────────────┘    └────────────────────┘    └─────────────────┘
                                 │                       │
                                 ▼                       ▼
                       ┌────────────────────┐    ┌─────────────────┐
                       │ DestinationManager │    │     Writer      │
                       │                    │    │   (ZipWriter\   │
                       └────────────────────┘    │   TarWriter)    │
                                 │               └─────────────────┘
                                 ▼                   │           │
                        ┌──────────────────┐         │           │
                        │  StreamFactory   │         ▼           ▼
                        │                  │    ┌─────────┐ ┌─────────┐
                        └──────────────────┘    │ Input   │ │ Output  │
                                                │ Stream  │ │ Stream  │
                                                └─────────┘ └─────────┘
```

## The Archive Manager Class

As seen in the [Usage Reference](./USAGE.md), the `ArchiveManager` is the central component that manages archive formats and their configurations. It interacts with the `DestinationManager` to handle file destinations.

When extending library functionality, you can register new archive formats or aliases using the `ArchiveManager`. The `Archive` interface is implemented by the archive classes such as `Zip` and `Tar`, which handle the specifics of each archive format.

> See the [Extending the Library](./4-EXTENDING.md) section for more details on how to extend the library with custom archive formats.

## The Archive Class

The `Archive` classes implement the `Archive` contract and provide the main interface to interact with different archive formats. For example, the `Zip` class handles ZIP archives, and has more methods specific to ZIP files, while the `Tar` class handles TAR archives, and is more simplistic in nature.

### Tar

The `Tar` class is a basic implementation of the `Archive` interface, providing methods to create and manipulate TAR archives. It does not support advanced features like compression or encryption.

```php
class Tar implements Archive
{
    public function setDefaultReadChunkSize(int $chunkSize): void
    public function addFileFromPath(string $fileName, string $filePath): void
    public function addFileFromStream(string $fileName, $stream): void
    public function addFileFromContentString(string $fileName, string $fileContents): void
    public function finish(): void
}
```

### Zip

The `Zip` class contains a similar set of methods, but also includes additional functionality for handling ZIP-specific features such as compression:

```php
class Zip implements Archive
{
    public function setDefaultCompressor(string $compressor): void // Not present in the Archive interface
    public function setDefaultReadChunkSize(int $chunkSize): void
    public function addFileFromPath(string $fileName, string $filePath): void
    public function addFileFromStream(string $fileName, $stream): void
    public function addFileFromContentString(string $fileName, string $fileContents): void
    public function finish(): void
}
```

## The Writer Class

As a pattern of the library, you may see that all of the `Archive` classes have an implementation of a `Writer` in their constructor. `Writer` classes are responsible for generating the raw archive data and managing `ReadStreams` and `WriteStreams`.

With this implementation, the `Archive` classes function as a simple and intuitive interface for the manipulation of archives, allowing for easy swap of archive formats without changing the application logic. Such implementation can be seen in the `Zip` class, where the `ZipWriter` or `Zip64Writer` is used to handle the specifics of ZIP file creation.

The `Writer` interface is simplistic in nature, and provides only two methods:

```php
interface Writer
{
    public function addFile(ReadStream $stream, string $fileName): void;
    public function finish(): void;
}
```

Every writer also has access to the `WriteStream` object, and should send the raw data through that implementation.

In this fashion, it is the responsability of the `Archive` classes to handle the creation of the `WriteStream` and `ReadStream` objects and pass them to the `Writer` implementation.

## The ReadStream Class

The `ReadStream` class represents a stream of data from a file that will be added to the archive. Note that the reading of the byte stream returns a `Generator` pattern, allowing for efficient memory usage when reading large files, with a configurable chunk size.

```php
interface ReadStream
{
    public static function open(string $path, int $chunkSize): self;
    public static function fromStream($stream, int $chunkSize): self;
    public static function fromString(string $contents, int $chunkSize): self;
    public function close(): void;
    public function read(): Generator;
    public function size(): int;
}
```

## The WriteStream Class

The `WriteStream` class represents how the raw archive data is written to the destination. It provides methods to write data to the stream, and is used by the `Writer` implementations to output the final archive file.

This interface has more implementations that the ReadStream, which we will see in more detail.

```php
interface WriteStream
{
    public function close(): void;
    public function write(string $s): int;
    public function getBytesWritten(): int;
}
```

At this stage, there are 4 implementations of the `WriteStream` interface:
- `OutputStream`: Basic implementation of a stream with `fopen` and `fwrite` methods, used for writing to files or standard output;
- `GzOutputStream`: An implementation that compresses the data using Gzip, suitable for writing compressed archives (`TarGz`);
- `ArrayOutputStream`: An implementation that contains an array of `WriteStream` objects, allowing for multiple destinations to be written to simultaneously;
- `HttpHeaderWriteStream`: A decorator for a `WriteStream` that contains a header array, and outputs the headers before writing the data.

## Benefits of the Architecture

The decision to use the `Writer` and `WriteStream` interfaces provides several benefits, as it allows for easy extension and customization of the library, aswell as a clear separation of concerns.

This way, the means as to which the raw data is created, read and written is completely abstracted from the application logic, allowing for extensive modularity of archive formats without changing the core application code.
