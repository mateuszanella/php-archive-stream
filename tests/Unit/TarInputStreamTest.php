<?php

use LaravelFileStream\Writers\Tar\InputStream;
use PHPUnit\Framework\TestCase;

class TarInputStreamTest extends TestCase
{
    public function testWriteDoesNotPadWhenMultipleOf512()
    {
        $stream = fopen('php://memory', 'wb');
        $inputStream = new InputStream($stream);

        $inputStream->write(str_repeat('a', 512));
        fseek($stream, 0);
        $content = fread($stream, 512);

        $this->assertEquals(512, strlen($content));
        $this->assertEquals(str_repeat('a', 512), $content);
    }

    public function testWritePadsMultipleWritesCorrectly()
    {
        $stream = fopen('php://memory', 'wb');
        $inputStream = new InputStream($stream);

        $inputStream->write(str_repeat('a', 500));
        $inputStream->write(str_repeat('b', 20));
        fseek($stream, 0);
        $content = fread($stream, 1024);

        $this->assertEquals(1024, strlen($content));
        $this->assertEquals(str_repeat('a', 500) . str_repeat("\0", 12) . str_repeat('b', 20) . str_repeat("\0", 492), $content);
    }

    public function testOpenSuccessfullyOpensFile()
    {
        $inputStream = InputStream::open('php://memory');

        $this->assertInstanceOf(InputStream::class, $inputStream);
    }
}
