<?php

use PhpArchiveStream\Archives\Zip;
use PhpArchiveStream\Writers\Zip\Records\EndOfCentralDirectoryRecord;

require 'vendor/autoload.php';

try {
    if (file_exists('./archive.zip')) {
        unlink('./archive.zip');
    }

    $zip = Zip::create('./archive.zip');

    $zip->addFileFromPath('file1.txt', './file1.txt');
    // $zip->addFileFromPath('file2.txt', './file2.txt');

    $zip->finish();

    echo "Archive created successfully!";
} catch (Exception $e) {
    echo "An error occurred: " . $e->getMessage();

    @unlink('./archive.zip');
}
