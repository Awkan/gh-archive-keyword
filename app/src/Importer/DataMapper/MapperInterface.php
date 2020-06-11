<?php

declare(strict_types=1);

namespace App\Importer\DataMapper;

interface MapperInterface
{
    public const EVENT_PUSH = 'PushEvent';

    public const CONTEXT_IMPORT_DATE = 'import_date';

    /**
     * Map provided data into object
     * 
     * @param mixed $data
     * @param string $type The data type event
     * @return array|object A list or a single mapped object
     */
    public function map($data, string $type, array $context = []);
}