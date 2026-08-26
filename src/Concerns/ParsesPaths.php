<?php

declare(strict_types=1);

namespace PhpArchiveStream\Concerns;

use InvalidArgumentException;

/**
 * @internal
 */
trait ParsesPaths
{
    /**
     * List of wrappers that should be ignored when extracting the extension.
     *
     * @var array<int, string>
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

        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        $compoundExtensions = ['gz', 'bz2', 'xz'];

        if (in_array($extension, $compoundExtensions, true)) {
            $compoundExtension = $extension;

            $parts = explode('.', $path);

            array_pop($parts);

            $newPath = implode('.', $parts);

            $extension = strtolower(pathinfo($newPath, PATHINFO_EXTENSION));

            if (empty($extension)) {
                throw new InvalidArgumentException("Could not determine the extension for the path: {$path}");
            }

            $extension .= '.'.$compoundExtension;
        }

        return $extension;
    }
}
