<?php

namespace PhpArchiveStream\Writers\Zip\Compressors;

interface Compressor
{
    public static function bitFlag(): int;

    public static function init(): static;

    public function compress(string $data): string;

    public function finish(): string;
}
