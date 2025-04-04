<?php

use PhpArchiveStream\ArchiveManager;

require 'vendor/autoload.php';

try {
    @unlink('./archives/archive.zip');
    @unlink('./archives/archive.tar');
    @unlink('./archives/archive.tar.gz');

    $manager = new ArchiveManager;

    $zip = $manager->create('./archives/archive.zip');
    $zip->addFileFromPath('composer.json', 'composer.json');
    $zip->addFileFromPath('composer.lock', 'composer.lock');
    $zip->finish();

    $tar = $manager->create('./archives/archive.tar');
    $tar->addFileFromPath('composer.json', 'composer.json');
    $tar->addFileFromPath('composer.lock', 'composer.lock');
    $tar->finish();

    $tarGz = $manager->create('./archives/archive.tar.gz');
    $tarGz->addFileFromPath('composer.json', 'composer.json');
    $tarGz->addFileFromPath('composer.lock', 'composer.lock');
    $tarGz->finish();

    echo "Archive created successfully!\n";
} catch (Exception $e) {
    echo "An error occurred: " . $e->getMessage() . "\n";
}
