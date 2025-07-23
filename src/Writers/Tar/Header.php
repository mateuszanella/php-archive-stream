<?php

namespace PhpArchiveStream\Writers\Tar;

use PhpArchiveStream\Utils;

class Header
{
    /**
     * Generate a TAR header for a file.
     */
    public static function generate(string $filename, string $prefix, int $filesize): string
    {
        $_filename = mb_str_pad($filename, 100, "\0");                                 // File name (up to 100 characters, null-padded)
        $_filePermissions = mb_str_pad('0000777', 8, '0', STR_PAD_LEFT);                      // File mode/permissions (8 bytes, left-padded)
        $_ownerId = mb_str_pad('0000000', 8, '0', STR_PAD_LEFT);                      // Owner ID (8 bytes, left-padded)
        $_groupId = mb_str_pad('0000000', 8, '0', STR_PAD_LEFT);                      // Group ID (8 bytes, left-padded)
        $_fileSize = mb_str_pad(sprintf('%011o', $filesize), 12, '0', STR_PAD_LEFT);   // File size (12 bytes, octal)
        $_modificationTime = mb_str_pad(sprintf('%011o', time()), 12, '0', STR_PAD_LEFT);      // Modification time (12 bytes, octal)
        $_checksum = str_repeat(' ', 8);                                            // Checksum (8 spaces, to be replaced later)
        $_typeFlag = '0';                                                           // Type flag ('0' for file, '5' for directory)
        $_linkName = mb_str_pad('', 100, "\0");                                        // Link name (not used)
        $_ustar = mb_str_pad('ustar', 6, "\0");                                     // UStar indicator
        $_ustarVersion = '00';                                                          // UStar version
        $_ownerUserName = mb_str_pad('', 32, "\0");                                         // Owner user name (32 bytes, null-padded)
        $_ownerGroupName = mb_str_pad('', 32, "\0");                                         // Owner group name (32 bytes, null-padded)
        $_deviceMajor = mb_str_pad('0000000', 8, '0', STR_PAD_LEFT);                      // Device major number (8 bytes)
        $_deviceMinor = mb_str_pad('0000000', 8, '0', STR_PAD_LEFT);                      // Device minor number (8 bytes)
        $_prefix = mb_str_pad($prefix, 155, "\0");                                   // Prefix (used for long file names)
        $_padding = mb_str_pad('', 12, "\0");                                         // Padding to complete 512 bytes

        $header = $_filename
            .$_filePermissions
            .$_ownerId
            .$_groupId
            .$_fileSize
            .$_modificationTime
            .$_checksum
            .$_typeFlag
            .$_linkName
            .$_ustar
            .$_ustarVersion
            .$_ownerUserName
            .$_ownerGroupName
            .$_deviceMajor
            .$_deviceMinor
            .$_prefix
            .$_padding;

        $checksum = Utils::checksum($header, 512);

        $header = substr_replace($header, sprintf("%06o\0 ", $checksum), 148, 8);

        return $header;
    }
}
