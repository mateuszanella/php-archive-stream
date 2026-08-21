<?php

namespace Tests\Unit;

use PhpArchiveStream\ArchiveManager;
use PhpArchiveStream\Archives\Tar;
use PhpArchiveStream\ConfigManager;
use PhpArchiveStream\DestinationManager;
use PhpArchiveStream\StreamManager;
use PHPUnit\Framework\TestCase;

class ArchiveManagerTest extends TestCase
{
    protected function tearDown(): void
    {
        if (file_exists('test.tgz')) {
            unlink('test.tgz');
        }
    }

    public function test_archive_manager_initialization(): void
    {
        $manager = ArchiveManager::make();

        $this->assertInstanceOf(ArchiveManager::class, $manager);
        $this->assertInstanceOf(ConfigManager::class, $manager->config());
    }

    public function test_make_returns_a_configured_manager(): void
    {
        $manager = ArchiveManager::make([
            'zip' => ['enableZip64' => false],
        ]);

        $this->assertInstanceOf(ArchiveManager::class, $manager);
        $this->assertFalse($manager->config()->get('zip.enableZip64'));
    }

    public function test_exposes_its_collaborators(): void
    {
        $manager = ArchiveManager::make();

        $this->assertInstanceOf(ConfigManager::class, $manager->config());
        $this->assertInstanceOf(DestinationManager::class, $manager->destination());
        $this->assertInstanceOf(StreamManager::class, $manager->stream());
        $this->assertSame($manager->destination()->stream(), $manager->stream());
    }

    public function test_alias(): void
    {
        $manager = ArchiveManager::make();

        $manager->alias('tgz', 'tar.gz');

        $archive = $manager->create('test.tgz', 'tgz');

        $this->assertInstanceOf(Tar::class, $archive);
        $this->assertFileExists('test.tgz');

        $archive->finish();
    }
}
