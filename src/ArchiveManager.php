<?php

class ArchiveManager
{
    // Registry for drivers mapped by file extension.
    protected $drivers = [];

    // Global or default configuration options.
    protected $config = [];

    public function __construct(array $config = [])
    {
        // Merge user config with defaults
        $this->config = array_merge([
            'default_output_stream_factory' => function ($path, $options) {
                return new DefaultOutputStream($path);
            },
            // You could add more global defaults here.
        ], $config);

        // Register default drivers.
        $this->registerDefaults();
    }

    protected function registerDefaults()
    {
        // ZIP driver: supports both ZipWriter and Zip64Writer.
        $this->drivers['.zip'] = function ($path, array $options) {
            // Decide which writer to use based on options (e.g., zip64 support).
            $useZip64 = $options['zip64'] ?? false;
            $outputStream = $this->createOutputStream($path, $options);

            $writer = $useZip64
                ? new Zip64Writer($outputStream)
                : new ZipWriter($outputStream);

            // Create and return the archive instance.
            return new ZipArchive($writer, $options);
        };

        // TAR driver.
        $this->drivers['.tar'] = function ($path, array $options) {
            // Tar uses a single writer but may use a custom output stream.
            $outputStream = $this->createOutputStream($path, $options);
            $writer = new TarWriter($outputStream);
            return new TarArchive($writer, $options);
        };

        // TARGZ driver.
        $this->drivers['.tar.gz'] = function ($path, array $options) {
            // Similar to TAR but could have extra steps for compression.
            $outputStream = $this->createOutputStream($path, $options);
            $writer = new TarWriter($outputStream);
            // You might wrap the writer or add extra configuration for gzipping.
            return new TarGzArchive($writer, $options);
        };
    }

    // Allow users to register additional drivers.
    public function registerDriver(string $extension, callable $factory)
    {
        $this->drivers[$extension] = $factory;
    }

    // Public method to create an archive based on the filename.
    public function createArchive(string $filename, array $options = [])
    {
        $extension = $this->extractExtension($filename);

        if (!isset($this->drivers[$extension])) {
            throw new \Exception("Unsupported archive type for extension: {$extension}");
        }

        // Merge driver-specific options with global config if needed.
        $mergedOptions = array_merge($this->config, $options);
        return call_user_func($this->drivers[$extension], $filename, $mergedOptions);
    }

    // A helper method to extract the correct extension.
    protected function extractExtension(string $filename)
    {
        // Example: Prioritize multi-part extensions ('.tar.gz') over simple ones.
        if (preg_match('/\.tar\.gz$/i', $filename)) {
            return '.tar.gz';
        }
        return strtolower(strrchr($filename, '.'));
    }

    // Helper to create the output stream, allowing for custom factories.
    protected function createOutputStream(string $path, array $options)
    {
        // If a custom factory is provided in options, use it.
        if (isset($options['output_stream_factory']) && is_callable($options['output_stream_factory'])) {
            return call_user_func($options['output_stream_factory'], $path, $options);
        }
        // Fallback to the default output stream factory.
        $factory = $this->config['default_output_stream_factory'];
        return call_user_func($factory, $path, $options);
    }
}
