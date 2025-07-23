<?php

namespace Tests;

use PHPUnit\Framework\TestCase;

class BasicTest extends TestCase
{
    public function test_hello_world()
    {
        $this->assertEquals('Hello, World!', 'Hello, World!');
    }
}
