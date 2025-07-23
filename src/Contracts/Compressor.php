<?php

namespace PhpArchiveStream\Contracts;

interface Compressor
{
    /**
     * Get the Zip file format bit flag.
     *
     * @return int The bit flag used for Zip file format.
     */
    public static function zipBitFlag(): int;

    /**
     * Initialize the compressor instance.
     *
     * @return static A new instance of the compressor.
     */
    public static function init(): static;

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
