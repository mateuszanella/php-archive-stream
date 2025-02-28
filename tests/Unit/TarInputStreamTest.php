<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use PhpArchiveStream\Exceptions\CouldNotOpenStreamException;
use PhpArchiveStream\Writers\Tar\InputStream;

/**
 * @covers \PhpArchiveStream\Writers\Tar\InputStream
 */
class TarInputStreamTest extends TestCase
{
    private $testFilePath;

    protected function setUp(): void
    {
        $this->testFilePath = tempnam(sys_get_temp_dir(), 'test');
        file_put_contents($this->testFilePath, str_repeat('A', 1024)); // Create a test file with 1024 'A' characters
    }

    protected function tearDown(): void
    {
        if (file_exists($this->testFilePath)) {
            unlink($this->testFilePath);
        }
    }

    public function testOpen()
    {
        $inputStream = InputStream::open($this->testFilePath);
        $this->assertInstanceOf(InputStream::class, $inputStream);
        $inputStream->close();
    }

    public function testOpenThrowsException()
    {
        $this->expectException(CouldNotOpenStreamException::class);
        InputStream::open('/invalid/path/to/file');
    }

    public function testClose()
    {
        $inputStream = InputStream::open($this->testFilePath);
        $inputStream->close();
        $this->assertTrue(true); // If no exception is thrown, the test passes
    }

    public function testRead()
    {
        $inputStream = InputStream::open($this->testFilePath);
        $chunks = iterator_to_array($inputStream->read());
        $inputStream->close();

        // Filter out any empty chunks that might be read at the end
        $chunks = array_filter($chunks, fn($chunk) => !empty($chunk));

        $this->assertCount(2, $chunks); // 1024 bytes / 512 bytes per chunk = 2 chunks
        $this->assertEquals(str_repeat('A', 512), $chunks[0]);
        $this->assertEquals(str_repeat('A', 512), $chunks[1]);
    }
}
