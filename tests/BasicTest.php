<?php

namespace Tests;

use PHPUnit\Framework\TestCase;

class BasicTest extends TestCase
{
    public function testHelloWorld()
    {
        $this->assertEquals('Hello, World!', 'Hello, World!');
    }
}
