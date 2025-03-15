<?php

use PhpArchiveStream\ArchiveStream;

require 'vendor/autoload.php';

try {
    if (file_exists('./archive.zip')) {
        unlink('./archive.zip');
    }

    $zip = ArchiveStream::to('./archives/archive.zip');
    $zip->addFileFromPath('composer.json', 'composer.json');
    $zip->addFileFromPath('composer.lock', 'composer.lock');
    $zip->finish();

    $tar = ArchiveStream::to('./archives/archive.tar');
    $tar->addFileFromPath('composer.json', 'composer.json');
    $tar->addFileFromPath('composer.lock', 'composer.lock');
    $tar->finish();

    echo "Archive created successfully!\n";
} catch (Exception $e) {
    echo "An error occurred: " . $e->getMessage() . "\n";

    @unlink('./archive.zip');
}
