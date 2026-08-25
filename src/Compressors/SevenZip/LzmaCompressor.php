<?php

declare(strict_types=1);

namespace PhpArchiveStream\Compressors\SevenZip;

use PhpArchiveStream\Contracts\SevenZip\SevenZipCompressor;
use RuntimeException;
use XZEncodeContext;

/**
 * @internal
 *
 * @phpstan-consistent-constructor
 */
class LzmaCompressor implements SevenZipCompressor
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
     * Create a new LZMA1 compressor instance.
     *
     * @param  int  $dictSize  The dictionary size in bytes.
     * @param  int  $level  The compression level (0-9).
     * @param  int  $lc  The number of literal context bits.
     * @param  int  $lp  The number of literal position bits.
     * @param  int  $pb  The number of position bits.
     *
     * @throws RuntimeException If the encoder cannot be initialized.
     */
    public function __construct(
        int $dictSize = 1 << 20,
        int $level = 6,
        int $lc = 3,
        int $lp = 0,
        int $pb = 2
    ) {
        if (! function_exists('xz_encode_init')) {
            throw new RuntimeException('The xz extension is required for LZMA1 compression');
        }

        $this->dictSize = $dictSize;

        $this->context = xz_encode_init(XZ_FORMAT_RAW, [
            'filter'    => XZ_FILTER_LZMA1,
            'dict_size' => $dictSize,
            'level'     => $level,
            'lc'        => $lc,
            'lp'        => $lp,
            'pb'        => $pb,
        ]);

        if ($this->context === false) {
            throw new RuntimeException('Failed to initialize LZMA1 encoder');
        }
    }

    public static function init(array $options = []): static
    {
        return new static(
            dictSize: $options['dictSize'] ?? 1 << 20,
            level: $options['level'] ?? 6,
            lc: $options['lc'] ?? 3,
            lp: $options['lp'] ?? 0,
            pb: $options['pb'] ?? 2,
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
        return 0x030101;
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
