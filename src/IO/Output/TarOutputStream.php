<?php

namespace PhpArchiveStream\IO\Output;

use PhpArchiveStream\Contracts\IO\WriteStream;
use PhpArchiveStream\Exceptions\CouldNotOpenStreamException;
use PhpArchiveStream\Exceptions\CouldNotWriteToStreamException;

class TarOutputStream implements WriteStream
{
    protected $stream;

    protected int $bytesWritten = 0;

    public function __construct($stream)
    {
        $this->stream = $stream;
    }

    public function close(): void
    {
        fclose($this->stream);
    }

    public function write(string $s): int
    {
        $paddedData = $s;
        $paddingSize = 512 - (strlen($s) % 512);

        if ($paddingSize < 512) {
            $paddedData .= str_repeat("\0", $paddingSize);
        }

        $bytesWritten = fwrite($this->stream, $paddedData);
        if ($bytesWritten === false) {
            throw new CouldNotWriteToStreamException;
        }

        $this->bytesWritten += $bytesWritten;

        return $bytesWritten;
    }
    public function getBytesWritten(): int
    {
        return $this->bytesWritten;
    }

    protected function pad(int $bytesWritten): void
    {
        $remainder = $bytesWritten % 512;
        if ($remainder) {
            $padding = 512 - $remainder;
            $bytesWritten = fwrite($this->stream, str_repeat("\0", $padding));

            if ($bytesWritten === false) {
                throw new CouldNotWriteToStreamException;
            }
        }
    }
}
