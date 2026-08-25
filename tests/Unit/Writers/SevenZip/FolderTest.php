<?php

declare(strict_types=1);

namespace Tests\Unit\Writers\SevenZip;

use PhpArchiveStream\Writers\SevenZip\Records\Folder;
use PHPUnit\Framework\TestCase;

class FolderTest extends TestCase
{
    public function test_lzma2_folder(): void
    {
        $folder = Folder::generate([
            ['methodId' => 0x21, 'properties' => "\x10"],
        ]);

        $this->assertSame('0121210110', bin2hex($folder));
    }

    public function test_lzma1_folder(): void
    {
        $folder = Folder::generate([
            ['methodId' => 0x030101, 'properties' => "\x5d\x00\x00\x10\x00"],
        ]);

        $this->assertSame('0123030101055d00001000', bin2hex($folder));
    }
}
