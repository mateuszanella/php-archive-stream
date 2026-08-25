# Extending the Library

This guide shows you how to extend PHP Archive Stream with custom archive formats, stream types, and other functionalities.

> It is recommended to have read the previous sections on the architecture and usage of the library before diving into extensions.

The library is designed to be extensible, allowing you to:
- Add support for new archive formats
- Implement custom compression algorithms
- Create specialized output destinations
- Use custom configuration options
- Integrate with any stream handling system

---

> **All of the examples in this section are simplified for clarity. Feel free to share your custom implementations or improvements with the community.**

## Custom Archive Formats

### Creating a Custom Archive Type

To add support for a new archive format, you need to:

1. Implement the `Archive` interface
2. Create a corresponding `Writer` implementation
3. Register the new format with the `ArchiveManager`

#### Example: 7Z Archive Support

```php
use PhpArchiveStream\Contracts\Archive;
use PhpArchiveStream\Contracts\Writers\Writer;
use PhpArchiveStream\IO\Input\InputStream;

class SevenZipArchive implements Archive
{
    // As we are dealing with an interface, the constructor methods may be
    // anything you need, but typically you would inject a Writer instance.
    public function __construct(
        protected ?Writer $writer,
        protected int $defaultChunkSize = 4096,
    ) {}

    public function setDefaultReadChunkSize(int $chunkSize): void
    {
        $this->defaultChunkSize = $chunkSize;
    }

    public function addFileFromPath(string $fileName, string $filePath): void
    {
        $stream = InputStream::open($filePath, $this->defaultChunkSize);
        $this->writer->addFile($stream, $fileName);
    }

    public function addFileFromStream(string $fileName, $stream): void
    {
        $stream = InputStream::fromStream($stream, $this->defaultChunkSize);
        $this->writer->addFile($stream, $fileName);
    }

    public function addFileFromContentString(string $fileName, string $fileContents): void
    {
        $stream = InputStream::fromString($fileContents, $this->defaultChunkSize);
        $this->writer->addFile($stream, $fileName);
    }

    public function finish(): void
    {
        $this->writer->finish();
        $this->writer = null;
    }
}
```

#### Creating a 7Z Writer

```php
use PhpArchiveStream\Contracts\Writers\Writer;
use PhpArchiveStream\Contracts\IO\SeekableWriteStream;
use PhpArchiveStream\IO\Input\InputStream;

class SevenZipWriter implements Writer
{
    public function __construct(
        protected SeekableWriteStream $outputStream
    ) {}

    public function addFile(InputStream $inputStream, string $filename): void
    {
        // Write the raw file data
    }

    public function setDefaultCompressor(string $compressor, array $options = []): void
    {
        // Store the compressor class + options, or throw if unsupported
    }

    public function finish(): void
    {
        // Write any additional data

        // Close the output stream
        $this->outputStream->close();
    }
}
```

#### Registering the Custom Format

```php
$manager = ArchiveManager::make();

// Register 7Z support
$manager->register('7z', function (string|array $destination, ConfigManager $config) {
    $defaultChunkSize = $config->get('7z.input.chunkSize', 1048576);
    $headers = $config->get('7z.headers', []);

    // You can add any custom configuration options here
    $customConfig = $config->get('7z.custom', []);

    // The output stream may also be created as you need
    $outputStream = new SomeStream($destination, $headers, $customConfig);

    return new SevenZipArchive(
        new SevenZipWriter($outputStream),
        $defaultChunkSize
    );
});

// Create 7Z archive
$sevenZip = $manager->create('./archive.7z');
$sevenZip->addFileFromPath('file.txt', './file.txt');
$sevenZip->finish();
```

## Custom Streams

Register custom stream builders on the `StreamManager` for specialized output handling. A stream builder receives the destination plus any stream configuration, and returns a `WriteStream`. Opening the destination is the builder's responsibility so the low-level open function always matches the stream implementation.

The `StreamManager` is reachable through the `ArchiveManager::stream()` accessor, so you can register builders without rebuilding the manager:

```php
use PhpArchiveStream\ArchiveManager;
use PhpArchiveStream\Contracts\IO\WriteStream;
use PhpArchiveStream\IO\Output\OutputStream;

$manager = ArchiveManager::make();

$manager->stream()->register('zip', function (string $destination, array $config = []): WriteStream {
    if (str_starts_with($destination, 'encrypt://')) {
        return new EncryptedOutputStream($destination);
    }

    return new OutputStream(fopen($destination, 'wb'));
});
```

Alternatively, inject a fully customised `StreamManager` through the constructor:

```php
use PhpArchiveStream\ConfigManager;
use PhpArchiveStream\DestinationManager;
use PhpArchiveStream\StreamManager;

$streams = new StreamManager;

// ...register builders on $streams...

$manager = new ArchiveManager(new ConfigManager, new DestinationManager($streams));
```

> **Seekable destinations and 7z:** Writers that need to seek (such as `SevenZipWriter`) type-hint `SeekableWriteStream`. If a stream builder serves a `7z` destination, it must return a stream implementing that interface. For non-seekable destinations, wrap them in `PhpArchiveStream\IO\Output\SpoolWriteStream`, which buffers the archive and provides the required seeking capability — this is exactly what the default `StreamManager` does.

## Custom Output Streams

Implement custom output streams for specialized destinations:

```php
use PhpArchiveStream\Contracts\IO\WriteStream;

class DatabaseOutputStream implements WriteStream
{
    private PDO $pdo;
    private string $tableName;
    private string $columnName;
    private int $recordId;

    public function __construct(PDO $pdo, string $table, string $column, int $id)
    {
        $this->pdo = $pdo;
        $this->tableName = $table;
        $this->columnName = $column;
        $this->recordId = $id;
    }

    public function write(string $data): void
    {
        // Append data to database blob/text field
        $sql = "UPDATE {$this->tableName}
                SET {$this->columnName} = CONCAT(COALESCE({$this->columnName}, ''), ?)
                WHERE id = ?";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$data, $this->recordId]);
    }

    public function close(): void
    {
        // Optional cleanup
    }
}

// Usage with a custom stream builder
use PhpArchiveStream\StreamManager;

$streams = new StreamManager;

$streams->register('zip', function ($resource) {
    return new DatabaseOutputStream(/* ... */);
});
```

## Custom Compression

A compressor implements the format-specific `ZipCompressor` (ZIP) or
`SevenZipCompressor` (7z) interface, both of which extend the generic
`Compressor` interface. The format interface declares how the compressor is
serialized into that archive (the `compression method` field from APPNOTE 4.4.5
for ZIP, the method ID and coder properties for 7z).

The `init()` factory is the single entry point writers use to create a fresh
instance, so constructor arguments are mapped from the options array here.

```php
use PhpArchiveStream\Contracts\Zip\ZipCompressor;

class LzmaCompressor implements ZipCompressor
{
    public static function init(array $options = []): static
    {
        return new static($options['level'] ?? 6);
    }

    public function __construct(protected int $level = 6) {}

    public function compress(string $data): string
    {
        return lzma_compress($data);
    }

    public function finish(): string
    {
        return '';
    }

    public function getCompressionMethod(): int
    {
        return 14; // LZMA compression method ID
    }
}
```

Then use it in ZIP archives, passing any options through to `init()`:

```php
$zip = $manager->create('./archive.zip');

$zip->setDefaultCompressor(LzmaCompressor::class, ['level' => 9]);
```

## Configuration Extensions

As the configuration class basically wraps an associative array, you can extend it with custom options:

```php
// Set custom options
$config = $manager->config();
$config->set('custom.option', 'value');

// Get custom options
$value = $config->get('custom.option', 'default');
```
