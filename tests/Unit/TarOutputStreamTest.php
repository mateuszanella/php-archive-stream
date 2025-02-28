<?php

namespace Tests\Unit;

use PhpArchiveStream\Writers\Tar\OutputStream;
use PhpArchiveStream\Exceptions\CouldNotOpenStreamException;
use PhpArchiveStream\Exceptions\CouldNotWriteToStreamException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \PhpArchiveStream\Writers\Tar\OutputStream
 */
class TarOutputStreamTest extends TestCase
{
    private $testFilePath;

    protected function setUp(): void
    {
        $this->testFilePath = tempnam(sys_get_temp_dir(), 'test');
    }

    protected function tearDown(): void
    {
        if (file_exists($this->testFilePath)) {
            unlink($this->testFilePath);
        }
    }

    public function testOpen()
    {
        $outputStream = OutputStream::open($this->testFilePath);
        $this->assertInstanceOf(OutputStream::class, $outputStream);
        $outputStream->close();
    }

    public function testOpenThrowsException()
    {
        $this->expectException(CouldNotOpenStreamException::class);
        OutputStream::open('/invalid/path/to/file');
    }

    public function testClose()
    {
        $outputStream = OutputStream::open($this->testFilePath);
        $outputStream->close();
        $this->assertTrue(true); // If no exception is thrown, the test passes
    }

    public function testWrite()
    {
        $outputStream = OutputStream::open($this->testFilePath);
        $outputStream->write('Hello, World!');
        $outputStream->close();

        $writtenData = file_get_contents($this->testFilePath);
        $expectedData = 'Hello, World!' . str_repeat("\0", 512 - strlen('Hello, World!'));

        $this->assertEquals($expectedData, $writtenData);
    }

    public function testWriteThrowsException()
    {
        $this->expectException(CouldNotWriteToStreamException::class);

        $outputStream = $this->getMockBuilder(OutputStream::class)
                             ->disableOriginalConstructor()
                             ->onlyMethods(['write'])
                             ->getMock();

        $outputStream->method('write')->will($this->throwException(new CouldNotWriteToStreamException));

        $outputStream->write('Hello, World!');
    }
}
