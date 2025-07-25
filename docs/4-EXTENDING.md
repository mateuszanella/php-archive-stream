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
use PhpArchiveStream\Contracts\IO\WriteStream;
use PhpArchiveStream\IO\Input\InputStream;

class SevenZipWriter implements Writer
{
    public function __construct(
        protected WriteStream $outputStream
    ) {}

    public function addFile(InputStream $inputStream, string $filename): void
    {
        // Write the raw file data
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
$manager = new ArchiveManager;

// Register 7Z support
$manager->register('7z', function (string|array $destination, Config $config) {
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

## Custom Stream Factories

Create custom stream factories for specialized output handling:

```php
use PhpArchiveStream\Contracts\StreamFactory as StreamFactoryContract;
use PhpArchiveStream\Contracts\IO\WriteStream;

class CustomStreamFactory implements StreamFactoryContract
{
    public static function make(string $extension, $stream): WriteStream
    {
        if (str_starts_with(stream_get_meta_data($stream)['uri'], 'encrypt://')) {
            return new EncryptedOutputStream($stream);
        }

        // Fall back to default streams
        return match ($extension) {
            'zip' => new OutputStream($stream),
            'tar' => new OutputStream($stream),
            'tar.gz' => new GzOutputStream($stream),
            default => throw new InvalidArgumentException("Unsupported: {$extension}"),
        };
    }
}

// Use custom stream factory for default archive creation
$config = [
    'streamFactory' => CustomStreamFactory::class
];

$manager = new ArchiveManager($config);
```

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

// Usage with custom stream factory
class DatabaseStreamFactory implements StreamFactoryContract
{
    public static function make(string $extension, $stream): WriteStream
    {
        if ($stream instanceof DatabaseOutputStream) {
            return $stream;
        }

        // Default handling
        return StreamFactory::make($extension, $stream);
    }
}
```

## Custom Compression

Add custom compression algorithms:

```php
use PhpArchiveStream\Contracts\Writers\Compressor;

class LzmaCompressor implements Compressor
{
    public function compress(string $data): string
    {
        return lzma_compress($data);
    }

    public function getMethod(): int
    {
        return 14; // LZMA compression method ID
    }

    public function getLevel(): int
    {
        return 6; // Default compression level
    }
}
```

Then use it in ZIP archives:

```php
$zip = $manager->create('./archive.zip');

$zip->setCompressor(LzmaCompressor::class);
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
