<?php

declare(strict_types=1);

namespace PhpArchiveStream\Contracts;

interface Compressor
{
    /**
     * Create a new compressor instance from the given options.
     *
     * @param  array<string, mixed>  $options  Compressor-specific options.
     * @return static A new instance of the compressor.
     */
    public static function init(array $options = []): static;

    /**
     * Compress the given data.
     *
     * @param  string  $data  The data to compress.
     * @return string The compressed data.
     */
    public function compress(string $data): string;

    /**
     * Finish the compression process and return the final compressed data.
     *
     * @return string The final compressed data.
     */
    public function finish(): string;
}
