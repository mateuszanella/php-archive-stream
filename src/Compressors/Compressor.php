<?php

namespace PhpArchiveStream\Compressors;

interface Compressor
{
    public static function zipBitFlag(): int;

    public static function init(): static;

    public function compress(string $data): string;

    public function finish(): string;
}
