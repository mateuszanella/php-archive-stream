<?php

namespace Tests\Unit;

use PhpArchiveStream\DestinationManager;
use PhpArchiveStream\IO\Output\ArrayOutputStream;
use PhpArchiveStream\StreamManager;
use PHPUnit\Framework\TestCase;

class DestinationManagerTest extends TestCase
{
    public function test_does_not_send_http_headers_in_cli(): void
    {
        $manager = new class(new StreamManager) extends DestinationManager
        {
            protected function isCLI(): bool
            {
                return true;
            }
        };

        $stream = $manager->getStream('php://output', 'zip');

        $this->assertInstanceOf(ArrayOutputStream::class, $stream);
    }

    public function test_should_send_http_headers_for_output_destination_in_non_cli(): void
    {
        $manager = new class(new StreamManager) extends DestinationManager
        {
            protected function isCLI(): bool
            {
                return false;
            }
        };

        $this->assertTrue($manager->shouldSendHTTPHeaders('php://output'));
        $this->assertTrue($manager->shouldSendHTTPHeaders('php://stdout'));
        $this->assertFalse($manager->shouldSendHTTPHeaders('file.txt'));
    }

    public function test_should_not_send_http_headers_in_cli(): void
    {
        $manager = new class(new StreamManager) extends DestinationManager
        {
            protected function isCLI(): bool
            {
                return true;
            }
        };

        $this->assertFalse($manager->shouldSendHTTPHeaders('php://output'));
        $this->assertFalse($manager->shouldSendHTTPHeaders('php://stdout'));
    }
}
