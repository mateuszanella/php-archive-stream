<?php

$zip = new ZipArchive();

$file = './archive.zip';

if ($zip->open($file) === TRUE) {
    echo "The zip archive is valid.";

    $comment = $zip->getArchiveComment();

    ! empty($comment)
        ? print("The comment is: $comment")
        : print("The comment is empty.");

    $zip->close();
} else {
    echo "The zip archive is invalid.";
}
