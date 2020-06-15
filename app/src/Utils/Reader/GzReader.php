<?php

declare(strict_types=1);

namespace App\Utils\Reader;

class GzReader implements ReaderInterface
{
    /**
     * {@inheritdoc}
     *
     * @return string[] An array containing the file, one line per cell
     */
    public function read(string $location)
    {
        return \gzfile($location);
    }
}
