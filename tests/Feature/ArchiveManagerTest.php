<?php

namespace Tests\Feature;

use Exception;
use PhpArchiveStream\ArchiveManager;
use PhpArchiveStream\Archives\Tar;
use PhpArchiveStream\Archives\Zip;
use PhpArchiveStream\Config;
use PhpArchiveStream\Contracts\Archive;
use PHPUnit\Framework\TestCase;

class ArchiveManagerTest extends TestCase
{
    private string $tempDir;

    private array $tempFiles = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->tempDir = sys_get_temp_dir().'/archive_manager_test_'.uniqid();

        mkdir($this->tempDir, 0777, true);

        $this->createTestFiles();
    }

    protected function tearDown(): void
    {
        // Clean up temporary files and directory
        foreach ($this->tempFiles as $file) {
            if (file_exists($file)) {
                unlink($file);
            }
        }

        if (is_dir($this->tempDir)) {
            $this->removeDirectory($this->tempDir);
        }

        parent::tearDown();
    }

    public function test_can_create_zip_archive(): void
    {
        $manager = new ArchiveManager();
        $outputPath = $this->tempDir.'/test.zip';

        $archive = $manager->create($outputPath);

        $this->assertInstanceOf(Zip::class, $archive);
        $this->assertInstanceOf(Archive::class, $archive);
    }

    public function test_can_create_tar_archive(): void
    {
        $manager = new ArchiveManager();
        $outputPath = $this->tempDir.'/test.tar';

        $archive = $manager->create($outputPath);

        $this->assertInstanceOf(Tar::class, $archive);
        $this->assertInstanceOf(Archive::class, $archive);
    }

    public function test_can_create_tar_gz_archive(): void
    {
        $manager = new ArchiveManager();
        $outputPath = $this->tempDir.'/test.tar.gz';

        $archive = $manager->create($outputPath);

        $this->assertInstanceOf(Tar::class, $archive);
        $this->assertInstanceOf(Archive::class, $archive);
    }

    public function test_can_create_archive_with_explicit_extension(): void
    {
        $manager = new ArchiveManager();
        $outputPath = $this->tempDir.'/archive_without_extension';

        $archive = $manager->create($outputPath, 'zip');

        $this->assertInstanceOf(Zip::class, $archive);
    }

    public function test_can_use_registered_alias(): void
    {
        $manager = new ArchiveManager();
        $outputPath = $this->tempDir.'/test.tgz';

        $archive = $manager->create($outputPath);

        $this->assertInstanceOf(Tar::class, $archive);
    }

    public function test_can_register_custom_driver(): void
    {
        $manager = new ArchiveManager();

        $customDriver = function (string|array $destination, Config $config) {
            return new class implements Archive
            {
                public function setDefaultReadChunkSize(int $chunkSize): void {}

                public function addFileFromPath(string $fileName, string $filePath): void {}

                public function addFileFromStream(string $fileName, $stream): void {}

                public function addFileFromContentString(string $fileName, string $fileContents): void {}

                public function finish(): void {}
            };
        };

        $manager->register('custom', $customDriver);
        $archive = $manager->create($this->tempDir.'/test.custom');

        $this->assertInstanceOf(Archive::class, $archive);
    }

    public function test_can_register_custom_alias(): void
    {
        $manager = new ArchiveManager();

        $manager->alias('myzip', 'zip');
        $archive = $manager->create($this->tempDir.'/test.myzip');

        $this->assertInstanceOf(Zip::class, $archive);
    }

    public function test_throws_exception_for_unsupported_extension(): void
    {
        $manager = new ArchiveManager();

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Unsupported archive type for extension: unknown');

        $manager->create($this->tempDir.'/test.unknown');
    }

    public function test_throws_exception_when_aliasing_non_existent_driver(): void
    {
        $manager = new ArchiveManager();

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Unsupported archive type for extension: nonexistent');

        $manager->alias('myalias', 'nonexistent');
    }

    public function test_can_access_configuration_instance(): void
    {
        $manager = new ArchiveManager();

        $config = $manager->config();

        $this->assertInstanceOf(Config::class, $config);
    }

    public function test_can_create_manager_with_custom_configuration(): void
    {
        $customConfig = [
            'zip' => [
                'enableZip64' => false,
                'input'       => ['chunkSize' => 2048576],
            ],
        ];

        $manager = new ArchiveManager($customConfig);
        $config = $manager->config();

        $this->assertFalse($config->get('zip.enableZip64'));
        $this->assertEquals(2048576, $config->get('zip.input.chunkSize'));
    }

    public function test_configuration_merges_with_defaults(): void
    {
        $customConfig = [
            'zip' => [
                'enableZip64' => false,
            ],
        ];

        $manager = new ArchiveManager($customConfig);
        $config = $manager->config();

        // Custom value should override default
        $this->assertFalse($config->get('zip.enableZip64'));

        // Default values should still be present
        $this->assertEquals(1048576, $config->get('zip.input.chunkSize'));
        $this->assertNotNull($config->get('zip.headers'));
    }

    public function test_can_create_multiple_archives_with_same_manager(): void
    {
        $manager = new ArchiveManager();

        $zipArchive = $manager->create($this->tempDir.'/test1.zip');
        $tarArchive = $manager->create($this->tempDir.'/test2.tar');
        $tarGzArchive = $manager->create($this->tempDir.'/test3.tar.gz');

        $this->assertInstanceOf(Zip::class, $zipArchive);
        $this->assertInstanceOf(Tar::class, $tarArchive);
        $this->assertInstanceOf(Tar::class, $tarGzArchive);
    }

    public function test_can_create_archive_from_array_destination(): void
    {
        $manager = new ArchiveManager();
        $destinations = [
            $this->tempDir.'/copy1.zip',
            $this->tempDir.'/copy2.zip',
        ];

        $archive = $manager->create($destinations);

        $this->assertInstanceOf(Zip::class, $archive);
    }

    public function test_each_test_gets_new_manager_instance(): void
    {
        $manager1 = new ArchiveManager();
        $manager2 = new ArchiveManager();

        $this->assertNotSame($manager1, $manager2);
        $this->assertNotSame($manager1->config(), $manager2->config());
    }

    public function test_can_modify_configuration_after_creation(): void
    {
        $manager = new ArchiveManager();
        $config = $manager->config();

        $originalChunkSize = $config->get('zip.input.chunkSize');
        $config->set('zip.input.chunkSize', 512);

        $this->assertNotEquals($originalChunkSize, $config->get('zip.input.chunkSize'));
        $this->assertEquals(512, $config->get('zip.input.chunkSize'));
    }

    public function test_drivers_are_isolated_between_instances(): void
    {
        $manager1 = new ArchiveManager();
        $manager2 = new ArchiveManager();

        $customDriver = function (string|array $destination, Config $config) {
            return new class implements Archive
            {
                public function setDefaultReadChunkSize(int $chunkSize): void {}

                public function addFileFromPath(string $fileName, string $filePath): void {}

                public function addFileFromStream(string $fileName, $stream): void {}

                public function addFileFromContentString(string $fileName, string $fileContents): void {}

                public function finish(): void {}
            };
        };

        $manager1->register('isolated', $customDriver);

        // Manager2 should not have the custom driver
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Unsupported archive type for extension: isolated');

        $manager2->create($this->tempDir.'/test.isolated');
    }

    public function test_aliases_are_isolated_between_instances(): void
    {
        $manager1 = new ArchiveManager();
        $manager2 = new ArchiveManager();

        $manager1->alias('isolated', 'zip');

        // Manager1 should work with the alias
        $archive1 = $manager1->create($this->tempDir.'/test1.isolated');
        $this->assertInstanceOf(Zip::class, $archive1);

        // Manager2 should not have the alias
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Unsupported archive type for extension: isolated');

        $manager2->create($this->tempDir.'/test2.isolated');
    }

    private function createTestFiles(): void
    {
        $files = [
            'test1.txt'        => 'This is test file 1',
            'test2.txt'        => 'This is test file 2',
            'subdir/test3.txt' => 'This is test file 3 in subdirectory',
        ];

        foreach ($files as $relativePath => $content) {
            $fullPath = $this->tempDir.'/'.$relativePath;
            $dir = dirname($fullPath);

            if (! is_dir($dir)) {
                mkdir($dir, 0777, true);
            }

            file_put_contents($fullPath, $content);
            $this->tempFiles[] = $fullPath;
        }
    }

    private function removeDirectory(string $dir): void
    {
        $files = array_diff(scandir($dir), ['.', '..']);

        foreach ($files as $file) {
            $path = $dir.'/'.$file;
            is_dir($path) ? $this->removeDirectory($path) : unlink($path);
        }

        rmdir($dir);
    }
}
