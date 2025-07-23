<?php

namespace PhpArchiveStream\IO\Output;

use PhpArchiveStream\Contracts\IO\WriteStream;

class HttpHeaderWriteStream implements WriteStream
{
    /**
     * The underlying stream to write to.
     */
    protected WriteStream $stream;

    /**
     * Whether headers have already been sent.
     */
    protected bool $hasSentHeaders = false;

    /**
     * The HTTP headers to send before writing to the stream.
     *
     * @var array<string, string>
     */
    protected array $headers;

    /**
     * The HttpHeaderWriteStream is a decorator class that wraps an
     * existing output stream and handles sending HTTP headers
     * before writing to the stream.
     *
     * @param  WriteStream  $stream  The underlying stream to write to.
     * @param  array<string, string>  $headers  The HTTP headers to send.
     */
    public function __construct(WriteStream $stream, array $headers = [])
    {
        $this->stream = $stream;
        $this->headers = $headers;
    }

    public function write(string $s): int
    {
        if ($this->shouldSendHeaders()) {
            $this->sendHeaders();
        }

        return $this->stream->write($s);
    }

    public function close(): void
    {
        $this->stream->close();
    }

    public function getBytesWritten(): int
    {
        return $this->stream->getBytesWritten();
    }

    /**
     * Send the HTTP headers if they haven't been sent yet.
     */
    protected function sendHeaders(): void
    {
        foreach ($this->headers as $header) {
            header($header);
        }

        $this->hasSentHeaders = true;
    }

    /**
     * Check if headers should be sent.
     */
    protected function shouldSendHeaders(): bool
    {
        return ! $this->hasSentHeaders
            && ! headers_sent()
            && php_sapi_name() !== 'cli';
    }
}
