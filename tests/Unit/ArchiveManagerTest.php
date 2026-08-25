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
        foreach (['test.tgz', 'test.tbz2', 'test.txz'] as $file) {
            if (file_exists($file)) {
                unlink($file);
            }
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

    public function test_tbz2_alias(): void
    {
        if (! function_exists('bzopen')) {
            $this->markTestSkipped('The bz2 extension is required for this test');
        }

        $manager = ArchiveManager::make();

        $archive = $manager->create('test.tbz2');

        $this->assertInstanceOf(Tar::class, $archive);
        $this->assertFileExists('test.tbz2');

        $archive->finish();
    }

    public function test_txz_alias(): void
    {
        if (! in_array('compress.lzma', stream_get_wrappers(), true)) {
            $this->markTestSkipped('The compress.lzma:// stream wrapper is required for this test');
        }

        $manager = ArchiveManager::make();

        $archive = $manager->create('test.txz');

        $this->assertInstanceOf(Tar::class, $archive);
        $this->assertFileExists('test.txz');

        $archive->finish();
    }
}
