<?php

declare(strict_types=1);

namespace App\Utils\Reader;

interface ReaderInterface
{
    /**
     * Read data pointed by given location.
     *
     * @param string $location The location of the resource to read (uri or path)
     *
     * @return mixed Readed data
     */
    public function read(string $location);
}
