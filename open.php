<?php

$zip = new ZipArchive();

$file = './archive.zip';

$open = $zip->open($file);

if ($open === TRUE) {
    echo "The zip archive is valid.";

    $comment = $zip->getArchiveComment();

    ! empty($comment)
        ? print("The comment is: $comment")
        : print("The comment is empty.");

    $zip->close();
} else {
    echo "The zip archive is invalid.";

    switch ($open) {
        case ZipArchive::ER_NOZIP:
            echo " - Not a zip archive.";
            break;
        case ZipArchive::ER_INCONS:
            echo " - Zip archive inconsistent.";
            break;
        case ZipArchive::ER_CRC:
            echo " - CRC error.";
            break;
        case ZipArchive::ER_MEMORY:
            echo " - Malloc failure.";
            break;
        case ZipArchive::ER_NOENT:
            echo " - No such file.";
            break;
        case ZipArchive::ER_EXISTS:
            echo " - File already exists.";
            break;
        case ZipArchive::ER_OPEN:
            echo " - Can't open file.";
            break;
        case ZipArchive::ER_TMPOPEN:
            echo " - Failure to create temporary file.";
            break;
        case ZipArchive::ER_READ:
            echo " - Read error.";
            break;
        case ZipArchive::ER_SEEK:
            echo " - Seek error.";
            break;
        default:
            echo " - Unknown error.";
            break;
    }
}
