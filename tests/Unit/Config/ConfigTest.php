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

        $config = new Config();
        $config->setItems($items);

        $this->assertSame($items, $config->all());
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

        $config = new Config($items);

        $this->assertSame('value', $config->get('key'));
        $this->assertSame(1, $config->get('key1'));
        $this->assertNull($config->get('key2'));
        $this->assertSame('nested', $config->get('key3.0'));
        $this->assertSame('nested1', $config->get('key3.1'));
        $this->assertSame('nested2', $config->get('key3.2'));
        $this->assertSame('nested4', $config->get('key3.nested3.0'));
        $this->assertSame('nested5', $config->get('key3.nested3.1'));
        $this->assertSame('value', $config->get('key3.nested3.nested6'));
        $this->assertNull($config->get('key4'));
        $this->assertSame('default', $config->get('key4', 'default'));
        $this->assertSame(null, $config->get('a.value.that.does.not.exist'));
    }

    public function testSet(): void
    {
        $config = new Config();

        $config->set('key', 'value');
        $config->set('key1', 1);
        $config->set('key2', null);
        $config->set('key3.nested', 'nested');
        $config->set('key3.nested1', 'nested1');
        $config->set('key3.nested2', 'nested2');
        $config->set('key3.nested3.nested4', 'nested4');
        $config->set('key3.nested3.nested5', 'nested5');
        $config->set('key3.nested3.nested6', 'value');

        $this->assertSame('value', $config->get('key'));
        $this->assertSame(1, $config->get('key1'));
        $this->assertNull($config->get('key2'));
        $this->assertSame('nested', $config->get('key3.nested'));
        $this->assertSame('nested1', $config->get('key3.nested1'));
        $this->assertSame('nested2', $config->get('key3.nested2'));
        $this->assertSame('nested4', $config->get('key3.nested3.nested4'));
        $this->assertSame('nested5', $config->get('key3.nested3.nested5'));
        $this->assertSame('value', $config->get('key3.nested3.nested6'));
    }

    public function testDefaultGet(): void
    {
        $config = new Config();

        $this->assertNull($config->get('key'));
        $this->assertSame('default', $config->get('key', 'default'));
    }

    public function testGetDefaults(): void
    {
        $config = new Config();

        $this->assertSame($config->getDefaults(), $config->all());
    }

    public function testMergesConfigCorrectly(): void
    {
        $config = new Config([
            'zip' => [
                'enableZip64' => false,
                'input'       => [
                    'chunkSize' => 1024,
                ],
            ]
        ]);

        $all = $config->all();
        $default = $config->getDefaults();

        $this->assertArrayHasKey('zip', $all);
        $this->assertArrayHasKey('tar', $all);
        $this->assertArrayHasKey('targz', $all);

        $this->assertNotEquals($default['zip']['enableZip64'], $all['zip']['enableZip64']);
        $this->assertNotEquals($default['zip']['input']['chunkSize'], $all['zip']['input']['chunkSize']);

        $this->assertEquals($default['zip']['headers'], $all['zip']['headers']);

        $this->assertEquals($default['tar'], $all['tar']);
        $this->assertEquals($default['targz'], $all['targz']);
    }
}
