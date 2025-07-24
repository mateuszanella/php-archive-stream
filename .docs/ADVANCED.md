# Advanced Usage

This document covers advanced usage patterns and techniques for PHP Archive Stream.

## Multiple Output Destinations

You can create archives that write to multiple destinations simultaneously:

```php
$manager = new ArchiveManager();

// Write to multiple files
$archive = $manager->create([
    './backup/daily.zip',
    './archive/monthly.zip',
    '/remote/storage/backup.zip'
]);

$archive->addFileFromPath('database.sql', './dumps/database.sql');
$archive->finish();
```

## Custom Stream Factories

Create custom stream factories to handle special output types:

```php
use PhpArchiveStream\Contracts\StreamFactory as StreamFactoryContract;
use PhpArchiveStream\Contracts\IO\WriteStream;

class CustomStreamFactory implements StreamFactoryContract
{
    public static function make(string $extension, $stream): WriteStream
    {
        return match ($extension) {
            'zip' => new CustomZipStream($stream),
            'tar' => new CustomTarStream($stream),
            default => throw new InvalidArgumentException("Unsupported: {$extension}"),
        };
    }
}

// Use custom factory
$config = ['streamFactory' => CustomStreamFactory::class];
$manager = new ArchiveManager($config);
```

## Large File Handling

For very large files, optimize chunk sizes and use streaming:

```php
$manager = new ArchiveManager([
    'zip' => [
        'input' => ['chunkSize' => 8388608], // 8MB chunks
    ]
]);

$archive = $manager->create('./large-backup.zip');

// Add large files
$archive->addFileFromPath('large-database.sql', './dumps/10gb-database.sql');
$archive->addFileFromPath('large-video.mp4', './media/4k-video.mp4');

$archive->finish();
```

## Custom Archive Types

Register custom archive types and aliases:

```php
$manager = new ArchiveManager();

// Register new archive type
$manager->register('custom', function ($destination, $config) {
    $stream = $this->destination->getStream($destination, 'custom');
    return new CustomArchive(new CustomWriter($stream));
});

// Register alias
$manager->alias('c', 'custom');

// Use custom type
$archive = $manager->create('./archive.custom');
// or use alias
$archive = $manager->create('./archive.c');
```

## Memory-Efficient Processing

Process large amounts of data with minimal memory usage:

```php
$manager = new ArchiveManager([
    'zip' => [
        'input' => ['chunkSize' => 65536], // 64KB chunks for memory efficiency
    ]
]);

$archive = $manager->create('php://output');

// Process files from directory without loading all into memory
$directory = new RecursiveDirectoryIterator('./large-directory');
$iterator = new RecursiveIteratorIterator($directory);

foreach ($iterator as $file) {
    if ($file->isFile()) {
        $relativePath = str_replace('./large-directory/', '', $file->getPathname());
        $archive->addFileFromPath($relativePath, $file->getPathname());
    }
}

$archive->finish();
```

## Database Streaming

Stream data directly from database queries:

```php
// Assume PDO connection
$stmt = $pdo->prepare("SELECT id, data FROM large_table");
$stmt->execute();

$archive = $manager->create('./database-export.zip');

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $filename = "record_{$row['id']}.json";
    $content = json_encode($row);
    
    $archive->addFileFromContentString($filename, $content);
}

$archive->finish();
```

## HTTP Streaming with Progress

Stream archives to HTTP with progress tracking:

```php
class ProgressTracker
{
    private int $totalFiles = 0;
    private int $processedFiles = 0;
    
    public function setTotal(int $total): void
    {
        $this->totalFiles = $total;
    }
    
    public function increment(): void
    {
        $this->processedFiles++;
        $percentage = ($this->processedFiles / $this->totalFiles) * 100;
        error_log("Progress: {$percentage}%");
    }
}

$tracker = new ProgressTracker();
$files = glob('./data/*');
$tracker->setTotal(count($files));

// Set appropriate headers
header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="export.zip"');

$archive = $manager->create('php://output');

foreach ($files as $file) {
    $archive->addFileFromPath(basename($file), $file);
    $tracker->increment();
}

$archive->finish();
```

## Error Handling and Recovery

Implement robust error handling:

```php
try {
    $archive = $manager->create('./backup.zip');
    
    $files = ['file1.txt', 'file2.txt', 'missing-file.txt'];
    
    foreach ($files as $file) {
        try {
            if (file_exists($file)) {
                $archive->addFileFromPath(basename($file), $file);
            } else {
                // Log missing file but continue
                error_log("Warning: File not found: {$file}");
                continue;
            }
        } catch (Exception $e) {
            error_log("Error adding file {$file}: " . $e->getMessage());
            continue;
        }
    }
    
    $archive->finish();
    echo "Archive created successfully!\n";
    
} catch (Exception $e) {
    echo "Failed to create archive: " . $e->getMessage() . "\n";
    
    // Cleanup partial files if needed
    if (file_exists('./backup.zip')) {
        unlink('./backup.zip');
    }
}
```
