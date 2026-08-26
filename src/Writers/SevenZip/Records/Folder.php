<?php

declare(strict_types=1);

namespace PhpArchiveStream\Writers\SevenZip\Records;

use PhpArchiveStream\Binary\VariableUInt64;

/**
 * @internal
 */
class Folder
{
    /**
     * Generate the binary representation of a folder (a list of coders).
     *
     * Each coder is described by an array containing a "methodId" integer and
     * the raw "properties" bytes. Only simple coders (one input stream, one
     * output stream) with properties are supported.
     *
     * @param  array<int, array{methodId: int, properties: string}>  $coders
     */
    public static function generate(array $coders): string
    {
        $result = VariableUInt64::encode(count($coders));

        foreach ($coders as $coder) {
            $idBytes = self::methodIdBytes($coder['methodId']);
            $idSize = strlen($idBytes);
            $properties = $coder['properties'];

            // Bits 0-3: CodecIdSize, bit 5: there are attributes (properties).
            $flags = ($idSize & 0x0F) | 0x20;

            $result .= chr($flags)
                .$idBytes
                .VariableUInt64::encode(strlen($properties))
                .$properties;
        }

        return $result;
    }

    /**
     * Encode a method ID as its minimal big-endian byte sequence.
     */
    protected static function methodIdBytes(int $methodId): string
    {
        $bytes = '';

        do {
            $bytes = chr($methodId & 0xFF).$bytes;
            $methodId >>= 8;
        } while ($methodId > 0);

        return $bytes;
    }
}
