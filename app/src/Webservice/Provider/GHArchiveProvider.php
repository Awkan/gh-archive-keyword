<?php

declare(strict_types=1);

namespace App\Webservice\Provider;

use App\Utils\Reader\ReaderInterface;

class GHArchiveProvider
{
    // @YouSign : This base URI would be set thanks to DI if it had to be more flexible (e.g. some URI for PREPROD, an other for PROD, ...)
    public const BASE_URI = 'https://data.gharchive.org/';
    // Template is {YYYY}-{MM}-{DD}-{HH}.json.gz
    public const TEMPLATE_PATH_GET_ARCHIVE = '%s-%s-%s-%s.json.gz';

    private ReaderInterface $reader;

    public function __construct(ReaderInterface $reader)
    {
        $this->reader = $reader;
    }

    /**
     * Fetch GHArchive data for given date and time.
     *
     * @return mixed The fetched data
     */
    public function fetch(string $year, string $month, string $day, string $hour)
    {
        $path = \sprintf(GHArchiveProvider::TEMPLATE_PATH_GET_ARCHIVE, $year, $month, $day, $hour);
        $uri = self::BASE_URI . $path;

        return $this->reader->read($uri);
    }
}
