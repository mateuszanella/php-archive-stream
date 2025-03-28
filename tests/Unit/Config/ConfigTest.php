<?php

namespace Tests\Unit\Config;

use PhpArchiveStream\Config;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    public function testSetItems(): void
    {
        $items = [
            'key' => 'value',
            'key1' => 1,
            'key2' => null,
            'key3' => [
                'nested',
                'nested1',
                'nested2',

                'nested3' => [
                    'nested4',
                    'nested5',
                    'nested6' => 'value'
                ]
            ]
        ];

        Config::setItems($items);

        $this->assertSame($items, Config::all());
    }

    public function testGet(): void
    {
        $items = [
            'key' => 'value',
            'key1' => 1,
            'key2' => null,
            'key3' => [
                'nested',
                'nested1',
                'nested2',

                'nested3' => [
                    'nested4',
                    'nested5',
                    'nested6' => 'value'
                ]
            ]
        ];

        Config::setItems($items);

        $this->assertSame('value', Config::get('key'));
        $this->assertSame(1, Config::get('key1'));
        $this->assertNull(Config::get('key2'));
        $this->assertSame('nested', Config::get('key3.0'));
        $this->assertSame('nested1', Config::get('key3.1'));
        $this->assertSame('nested2', Config::get('key3.2'));
        $this->assertSame('nested4', Config::get('key3.nested3.0'));
        $this->assertSame('nested5', Config::get('key3.nested3.1'));
        $this->assertSame('value', Config::get('key3.nested3.nested6'));
        $this->assertNull(Config::get('key4'));
        $this->assertSame('default', Config::get('key4', 'default'));
        $this->assertSame(null, Config::get('a.value.that.does.not.exist'));
    }

    public function testSet(): void
    {
        Config::set('key', 'value');
        Config::set('key1', 1);
        Config::set('key2', null);
        Config::set('key3.nested', 'nested');
        Config::set('key3.nested1', 'nested1');
        Config::set('key3.nested2', 'nested2');
        Config::set('key3.nested3.nested4', 'nested4');
        Config::set('key3.nested3.nested5', 'nested5');
        Config::set('key3.nested3.nested6', 'value');

        $this->assertSame('value', Config::get('key'));
        $this->assertSame(1, Config::get('key1'));
        $this->assertNull(Config::get('key2'));
        $this->assertSame('nested', Config::get('key3.nested'));
        $this->assertSame('nested1', Config::get('key3.nested1'));
        $this->assertSame('nested2', Config::get('key3.nested2'));
        $this->assertSame('nested4', Config::get('key3.nested3.nested4'));
        $this->assertSame('nested5', Config::get('key3.nested3.nested5'));
        $this->assertSame('value', Config::get('key3.nested3.nested6'));
    }
}
