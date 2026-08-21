<?php

namespace PhpArchiveStream\Compressors;

use PhpArchiveStream\Contracts\Compressor;
use PhpArchiveStream\Contracts\SevenZip\Coder;
use RuntimeException;
use XZEncodeContext;

class Lzma2Compressor implements Coder, Compressor
{
    /**
     * The XZ encode context.
     *
     * @var XZEncodeContext
     */
    protected $context;

    /**
     * The dictionary size in bytes used by the encoder.
     */
    protected int $dictSize;

    /**
     * Create a new LZMA2 compressor instance.
     *
     * @param  int  $dictSize  The dictionary size in bytes.
     * @param  int  $level  The compression level (0-9).
     *
     * @throws RuntimeException If the encoder cannot be initialized.
     */
    public function __construct(int $dictSize = 1 << 20, int $level = 6)
    {
        if (! function_exists('xz_encode_init')) {
            throw new RuntimeException('The xz extension is required for LZMA2 compression');
        }

        $this->dictSize = $dictSize;

        $this->context = xz_encode_init(XZ_FORMAT_RAW, [
            'filter'    => XZ_FILTER_LZMA2,
            'dict_size' => $dictSize,
            'level'     => $level,
        ]);

        if ($this->context === false) {
            throw new RuntimeException('Failed to initialize LZMA2 encoder');
        }
    }

    public static function init(array $options = []): static
    {
        return new static(
            dictSize: $options['dictSize'] ?? 1 << 20,
            level: $options['level'] ?? 6,
        );
    }

    public function compress(string $data): string
    {
        $data = xz_encode_add($this->context, $data);

        if ($data === false) {
            throw new RuntimeException('Failed to compress data');
        }

        return $data;
    }

    public function finish(): string
    {
        $data = xz_encode_finish($this->context);

        if ($data === false) {
            throw new RuntimeException('Failed to finish compression');
        }

        return $data;
    }

    public function getMethodId(): int
    {
        return 0x21;
    }

    public function getProperties(): string
    {
        $properties = xz_encode_get_properties($this->context);

        if ($properties === false) {
            throw new RuntimeException('Failed to read encoder properties');
        }

        return $properties;
    }
}
