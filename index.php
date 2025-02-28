<?php

require 'vendor/autoload.php';

use PhpArchiveStream\Writers\Tar\Tar;

try {
    if (file_exists('./archive.tar')) {
        unlink('./archive.tar');
    }

    $tar = new Tar('./archive.tar');

    $tar->addFileFromContentString('./file1.txt');
    $tar->addFileFromContentString('./file2.txt');

    $tar->save();

    echo "Tar archive created successfully.";
} catch (Exception $e) {
    echo "An error occurred: " . $e->getMessage();
}
