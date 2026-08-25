<?php

namespace Tests\Unit\Compressors;

use PhpArchiveStream\Compressors\SevenZip\Lzma2Compressor;
use PhpArchiveStream\Compressors\SevenZip\LzmaCompressor;
use PHPUnit\Framework\TestCase;

class LzmaCompressorTest extends TestCase
{
    protected function setUp(): void
    {
        if (! function_exists('xz_encode_init')) {
            $this->markTestSkipped('The xz extension is not available.');
        }
    }

    public function test_lzma2_round_trips(): void
    {
        $data = str_repeat('The quick brown fox jumps over the lazy dog.', 100);

        $compressor = Lzma2Compressor::init();
        $packed = $compressor->compress($data).$compressor->finish();

        $this->assertSame(0x21, $compressor->getMethodId());
        $this->assertSame("\x10", $compressor->getProperties());

        $decoder = xz_decode_init(XZ_FORMAT_RAW, ['filter' => XZ_FILTER_LZMA2, 'dict_size' => 1 << 20]);
        $decoded = xz_decode_add($decoder, $packed).xz_decode_finish($decoder);

        $this->assertSame($data, $decoded);
    }

    public function test_lzma1_round_trips(): void
    {
        $data = str_repeat('The quick brown fox jumps over the lazy dog.', 100);

        $compressor = LzmaCompressor::init();
        $packed = $compressor->compress($data).$compressor->finish();

        $this->assertSame(0x030101, $compressor->getMethodId());
        $this->assertSame("\x5d\x00\x00\x10\x00", $compressor->getProperties());

        $decoder = xz_decode_init(XZ_FORMAT_RAW, [
            'filter'    => XZ_FILTER_LZMA1,
            'dict_size' => 1 << 20,
            'lc'        => 3,
            'lp'        => 0,
            'pb'        => 2,
        ]);
        $decoded = xz_decode_add($decoder, $packed).xz_decode_finish($decoder);

        $this->assertSame($data, $decoded);
    }
}
