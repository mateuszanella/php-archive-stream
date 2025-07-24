# Extending the Library

This guide shows you how to extend PHP Archive Stream with custom archive formats, stream types, and other functionality.

## Custom Archive Formats

### Creating a Custom Archive Type

To add support for a new archive format, you need to:

1. Implement the `Archive` interface
2. Create a corresponding `Writer` implementation
3. Register the new format with the `ArchiveManager`

#### Example: RAR Archive Support

```php
use PhpArchiveStream\Contracts\Archive;
use PhpArchiveStream\Contracts\Writers\Writer;
use PhpArchiveStream\IO\Input\InputStream;

class RarArchive implements Archive
{
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

#### Creating a RAR Writer

```php
use PhpArchiveStream\Contracts\Writers\Writer;
use PhpArchiveStream\Contracts\IO\WriteStream;
use PhpArchiveStream\IO\Input\InputStream;

class RarWriter implements Writer
{
    public function __construct(
        protected WriteStream $outputStream
    ) {}

    public function addFile(InputStream $inputStream, string $filename): void
    {
        // Write RAR-specific headers
        $this->writeFileHeader($filename, $inputStream->getSize());
        
        // Write file content
        while (!$inputStream->isEof()) {
            $chunk = $inputStream->read();
            $this->outputStream->write($chunk);
        }
        
        $inputStream->close();
    }

    public function finish(): void
    {
        // Write RAR archive footer
        $this->writeArchiveFooter();
        $this->outputStream->close();
    }

    private function writeFileHeader(string $filename, int $size): void
    {
        // RAR format-specific header implementation
        $header = $this->buildRarHeader($filename, $size);
        $this->outputStream->write($header);
    }

    private function writeArchiveFooter(): void
    {
        // RAR format-specific footer implementation
        $footer = $this->buildRarFooter();
        $this->outputStream->write($footer);
    }

    // Additional RAR-specific methods...
}
```

#### Registering the Custom Format

```php
$manager = new ArchiveManager();

// Register RAR support
$manager->register('rar', function (string|array $destination, Config $config) {
    $defaultChunkSize = $config->get('rar.input.chunkSize', 1048576);
    $headers = $config->get('rar.headers', []);

    $outputStream = $this->destination->getStream($destination, 'rar', $headers);

    return new RarArchive(
        new RarWriter($outputStream),
        $defaultChunkSize
    );
});

// Create RAR archive
$rar = $manager->create('./archive.rar');
$rar->addFileFromPath('file.txt', './file.txt');
$rar->finish();
```

## Custom Stream Factories

Create custom stream factories for specialized output handling:

```php
use PhpArchiveStream\Contracts\StreamFactory as StreamFactoryContract;
use PhpArchiveStream\Contracts\IO\WriteStream;

class CloudStreamFactory implements StreamFactoryContract
{
    public static function make(string $extension, $stream): WriteStream
    {
        // Handle cloud storage streams
        if (str_starts_with(stream_get_meta_data($stream)['uri'], 's3://')) {
            return new S3OutputStream($stream);
        }

        // Handle encrypted streams
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

// Use custom stream factory
$config = ['streamFactory' => CloudStreamFactory::class];
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

// Use with ZIP archives
class CustomZip extends Zip
{
    public function setCustomCompressor(): void
    {
        if ($this->writer instanceof ZipWriter) {
            $this->writer->setDefaultCompressor(LzmaCompressor::class);
        }
    }
}
```

## Plugin System

Create a plugin system for easy extensibility:

```php
abstract class ArchivePlugin
{
    abstract public function register(ArchiveManager $manager): void;
    abstract public function getName(): string;
    abstract public function getVersion(): string;
}

class SevenZipPlugin extends ArchivePlugin
{
    public function register(ArchiveManager $manager): void
    {
        $manager->register('7z', function ($destination, $config) {
            // 7-Zip implementation
            return new SevenZipArchive(/* ... */);
        });

        $manager->alias('7zip', '7z');
    }

    public function getName(): string
    {
        return '7-Zip Support';
    }

    public function getVersion(): string
    {
        return '1.0.0';
    }
}

// Plugin manager
class PluginManager
{
    private array $plugins = [];

    public function register(ArchivePlugin $plugin): void
    {
        $this->plugins[$plugin->getName()] = $plugin;
    }

    public function loadAll(ArchiveManager $manager): void
    {
        foreach ($this->plugins as $plugin) {
            $plugin->register($manager);
        }
    }
}

// Usage
$pluginManager = new PluginManager();
$pluginManager->register(new SevenZipPlugin());

$manager = new ArchiveManager();
$pluginManager->loadAll($manager);
```

## Configuration Extensions

Extend the configuration system:

```php
class ExtendedConfig extends Config
{
    public function getCompressionLevel(string $format): int
    {
        return $this->get("{$format}.compression.level", 6);
    }

    public function setCompressionLevel(string $format, int $level): void
    {
        $this->set("{$format}.compression.level", $level);
    }

    public function isEncryptionEnabled(string $format): bool
    {
        return $this->get("{$format}.encryption.enabled", false);
    }
}

// Use extended configuration
$config = new ExtendedConfig([
    'zip' => [
        'compression' => ['level' => 9],
        'encryption' => ['enabled' => true, 'method' => 'AES-256'],
    ]
]);
```

## Testing Custom Extensions

Write tests for your custom extensions:

```php
use PHPUnit\Framework\TestCase;

class RarArchiveTest extends TestCase
{
    public function testCanCreateRarArchive(): void
    {
        $manager = new ArchiveManager();
        $manager->register('rar', function ($destination, $config) {
            return new RarArchive(new RarWriter($stream));
        });

        $tempFile = tempnam(sys_get_temp_dir(), 'test_rar_');
        
        $archive = $manager->create($tempFile);
        $archive->addFileFromContentString('test.txt', 'Hello World');
        $archive->finish();

        $this->assertFileExists($tempFile);
        $this->assertGreaterThan(0, filesize($tempFile));

        unlink($tempFile);
    }
}
```

This extensibility system allows you to:
- Add support for new archive formats
- Implement custom compression algorithms
- Create specialized output destinations
- Build plugin systems for modular functionality
- Extend configuration capabilities
- Integrate with external services and APIs
