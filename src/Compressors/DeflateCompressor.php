<?php

namespace PhpArchiveStream\Compressors;

use PhpArchiveStream\Contracts\Compressor;
use RuntimeException;

class DeflateCompressor implements Compressor
{
    /**
     * @var \DeflateContext The deflate context resource.
     */
    protected $context;

    /**
     * Constructor for the DeflateCompressor class.
     *
     * @param  int  $level The compression level (0-9), where 0 is no compression and 9 is maximum compression.
     * @throws RuntimeException If the deflate context cannot be initialized.
     */
    public function __construct(int $level = 6)
    {
        $this->context = deflate_init(ZLIB_ENCODING_RAW, [
            'level' => $level
        ]);

        if ($this->context === false) {
            throw new RuntimeException('Failed to initialize deflate context');
        }
    }

    public static function zipBitFlag(): int
    {
        return 0x08;
    }

    public static function init(): static
    {
        return new static;
    }

    public function compress(string $data): string
    {
        $data = deflate_add($this->context, $data, ZLIB_NO_FLUSH);

        if ($data === false) {
            throw new RuntimeException('Failed to compress data');
        }

        return $data;
    }

    public function finish(): string
    {
        $data = deflate_add($this->context, '', ZLIB_FINISH);

        if ($data === false) {
            throw new RuntimeException('Failed to finish compression');
        }

        return $data;
    }
}
