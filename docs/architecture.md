# Architecture Overview

This document provides an overview of the PHP Archive Stream library architecture and its core components.

## High-Level Architecture

```
┌─────────────────┐    ┌──────────────────┐    ┌─────────────────┐
│   Application   │───▶│  ArchiveManager  │───▶│   Archive       │
│                 │    │                  │    │  (Zip/Tar)      │
└─────────────────┘    └──────────────────┘    └─────────────────┘
                                │                        │
                                ▼                        ▼
                       ┌──────────────────┐    ┌─────────────────┐
                       │ DestinationManager│    │     Writer      │
                       │                  │    │  (ZipWriter/    │
                       └──────────────────┘    │   TarWriter)    │
                                │              └─────────────────┘
                                ▼                        │
                       ┌──────────────────┐              ▼
                       │  StreamFactory   │    ┌─────────────────┐
                       │                  │    │   OutputStream  │
                       └──────────────────┘    │                 │
                                               └─────────────────┘
```

## Core Components

### 1. ArchiveManager

The main entry point and factory for creating archives.

**Responsibilities:**
- Archive type registration and resolution
- Configuration management
- Driver instantiation
- Extension alias management

**Key Methods:**
- `create()` - Creates archive instances
- `register()` - Registers custom archive types
- `alias()` - Creates extension aliases

### 2. Archive Implementations

Concrete implementations of the `Archive` interface.

**Current Implementations:**
- `Zip` - ZIP archive support
- `Tar` - TAR archive support (including TAR.GZ)

**Responsibilities:**
- File addition from various sources
- Chunk size management
- Writer coordination

### 3. Writer System

Handles the actual archive format writing.

**Writer Types:**
- `ZipWriter` - Standard ZIP format
- `Zip64Writer` - ZIP64 format for large archives
- `TarWriter` - TAR format with optional compression

**Responsibilities:**
- Format-specific header/footer generation
- File entry writing
- Compression handling

### 4. Stream System

Manages input and output streaming.

**Stream Types:**
- `InputStream` - Reads from files, strings, or streams
- `OutputStream` - Basic output stream
- `GzOutputStream` - Gzip-compressed output
- `ArrayOutputStream` - Multiple destination output
- `HttpHeaderWriteStream` - HTTP header injection

### 5. Configuration System

Manages library configuration with dot notation support.

**Features:**
- Hierarchical configuration
- Runtime modification
- Default value merging
- Type-safe access

## Data Flow

### Archive Creation Flow

1. **Request Processing**
   ```
   Application → ArchiveManager::create(destination)
   ```

2. **Extension Resolution**
   ```
   DestinationManager::extractCommonExtension()
   ArchiveManager → resolve driver and aliases
   ```

3. **Stream Creation**
   ```
   DestinationManager::getStream()
   StreamFactory::make() → create appropriate streams
   ```

4. **Archive Instantiation**
   ```
   Driver factory function → create Archive + Writer
   ```

### File Addition Flow

1. **Input Stream Creation**
   ```
   Archive::addFileFromPath()
   InputStream::open() → create input stream
   ```

2. **Data Processing**
   ```
   Writer::addFile()
   Read chunks → Process → Write to output stream
   ```

3. **Format Writing**
   ```
   Writer → generate headers/entries
   OutputStream → write to destination(s)
   ```

## Design Patterns

### Factory Pattern
- `ArchiveManager` acts as a factory for archive instances
- `StreamFactory` creates appropriate stream types
- Dynamic driver registration enables extensibility

### Strategy Pattern
- Different `Writer` implementations for different formats
- Configurable compression strategies
- Pluggable stream factories

### Decorator Pattern
- `HttpHeaderWriteStream` decorates output streams
- `GzOutputStream` adds compression to base streams
- Multiple layers of stream processing

### Template Method Pattern
- Base `Archive` interface defines common operations
- Concrete implementations customize specific behaviors
- Writer system follows format-specific templates

## Extension Points

### Custom Archive Types

```php
// Register new archive format
$manager->register('custom', function($destination, $config) {
    $stream = $destinationManager->getStream($destination, 'custom');
    return new CustomArchive(new CustomWriter($stream));
});
```

### Custom Stream Factories

```php
class CustomStreamFactory implements StreamFactoryContract
{
    public static function make(string $extension, $stream): WriteStream
    {
        // Custom stream creation logic
    }
}
```

### Custom Writers

```php
class CustomWriter implements Writer
{
    public function addFile(InputStream $stream, string $filename): void
    {
        // Custom format writing logic
    }
}
```

## Error Handling Strategy

### Exception Hierarchy
- `InvalidArgumentException` - Configuration/parameter errors
- `RuntimeException` - Stream/IO errors
- Format-specific exceptions for archive errors

### Recovery Mechanisms
- Graceful degradation for missing files
- Stream error handling and cleanup
- Partial archive recovery options

## Performance Considerations

### Memory Management
- Streaming architecture prevents memory exhaustion
- Configurable chunk sizes for memory/performance tuning
- Lazy loading of file data

### I/O Optimization
- Buffered stream operations
- Minimal file system calls
- Efficient compression algorithms

### Scalability
- Support for multiple output destinations
- Parallel processing capabilities
- Large file handling (ZIP64, streaming TAR)
