<?php

namespace Tests\Unit;

use InvalidArgumentException;
use PhpArchiveStream\Writers\WriterFactory;
use PHPUnit\Framework\TestCase;

class WriterFactoryTest extends TestCase
{
    // public function testToReturnsZipWriter()
    // {
    //     $app = $this->createMock(Application::class);
    //     $app->method('make')->willReturn(new Zip);

    //     $writer = WriterFactory::to('file.zip', $app);

    //     $this->assertInstanceOf(Zip::class, $writer);
    // }

    // public function testToReturnsTarWriter()
    // {
    //     $app = $this->createMock(Application::class);
    //     $app->method('make')->willReturn(new Tar);

    //     $writer = WriterFactory::to('file.tar', $app);

    //     $this->assertInstanceOf(Tar::class, $writer);
    // }

    public function testToThrowsExceptionForUnknownExtension()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Writer for extension unknown not found');

        WriterFactory::to('file.unknown');
    }
}
