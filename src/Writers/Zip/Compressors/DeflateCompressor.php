<?php

namespace PhpArchiveStream\Writers\Zip\Compressors;

use PhpArchiveStream\Writers\Zip\Compressors\Compressor;
use RuntimeException;

class DeflateCompressor implements Compressor
{
    protected $context;

    public function __construct(int $level = 6)
    {
        $this->context = deflate_init(ZLIB_ENCODING_RAW, [
            'level' => $level
        ]);

        if ($this->context === false) {
            throw new RuntimeException('Failed to initialize deflate context');
        }
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

    public static function bitFlag(): int
    {
        return 0x08;
    }
}
