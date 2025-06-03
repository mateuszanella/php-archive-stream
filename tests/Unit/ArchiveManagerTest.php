<?php

namespace Tests\Unit;

use PhpArchiveStream\ArchiveManager;
use PhpArchiveStream\Archives\Tar;
use PhpArchiveStream\Config;
use PHPUnit\Framework\TestCase;

class ArchiveManagerTest extends TestCase
{
    protected function tearDown(): void
    {
        if (file_exists('test.tgz')) {
            unlink('test.tgz');
        }
    }

    public function testArchiveManagerInitialization(): void
    {
        $manager = new ArchiveManager;

        $this->assertInstanceOf(ArchiveManager::class, $manager);
        $this->assertInstanceOf(Config::class, $manager->config());
    }

    public function testAlias(): void
    {
        $manager = new ArchiveManager;

        $manager->alias('tgz', 'tar.gz');

        $archive = $manager->create('test.tgz', 'tgz');

        $this->assertInstanceOf(Tar::class, $archive);
        $this->assertFileExists('test.tgz');

        $archive->finish();
    }
}
