<?php

use PhpArchiveStream\Writers\Zip\Records\EndOfCentralDirectoryRecord;

require 'vendor/autoload.php';

try {
    if (file_exists('./archive.zip')) {
        unlink('./archive.zip');
    }

    $stream = fopen('./archive.zip', 'w');

    $EOCDR = EndOfCentralDirectoryRecord::generate(
        0,
        0,
        0,
        0,
        0,
        0,
        'eoijdod2ijo2iojd2jiij'
    );

    fwrite($stream, $EOCDR);

    fclose($stream);

    echo "Archive created successfully!";
} catch (Exception $e) {
    echo "An error occurred: " . $e->getMessage();

    @unlink('./archive.zip');
}
