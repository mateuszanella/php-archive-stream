<?php

namespace PhpArchiveStream\Concerns;

use InvalidArgumentException;

trait ParsesPaths
{
    /**
     * List of wrappers that should be ignored when extracting the extension.
     */
    protected static array $ignoredWrappers = [
        'php',
        'zlib',
        'rar',
    ];

    /**
     * Extracts the extension from a given path.
     *
     * @param  string  $path  The path to extract the extension from.
     * @return string|null The extracted extension or null if it cannot be determined.
     *
     * @throws InvalidArgumentException If the extension cannot be determined.
     */
    public function extractExtension(string $path): ?string
    {
        $parsedPath = parse_url($path);

        // If the path is a URL, we need to check if it has a
        // scheme and if it is one of the ignored wrappers.
        // In this case we return null to allow for the possibility
        // of other destinations containing a usable extension.
        if (isset($parsedPath['scheme']) && in_array($parsedPath['scheme'], self::$ignoredWrappers)) {
            return null;
        }

        $extension = pathinfo($path, PATHINFO_EXTENSION);

        // If the file is gzipped, we must get the previous
        // extension and append the gz extension to it.
        if ($extension === 'gz') {
            $parts = explode('.', $path);

            // Remove the .gz extension from the end of the array
            array_pop($parts);

            $newPath = implode('.', $parts);

            $extension = pathinfo($newPath, PATHINFO_EXTENSION);

            // Anything beyond this point should be ignored and fail
            // if the extension does not exist or is something bizarre.
            if (empty($extension)) {
                throw new InvalidArgumentException("Could not determine the extension for the path: {$path}");
            }

            $extension .= '.gz';
        }

        return $extension;
    }
}
