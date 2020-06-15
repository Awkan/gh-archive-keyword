<?php

declare(strict_types=1);

namespace App\Import\Mapper\Item;

use App\Entity\Commit;
use App\Import\Mapper\MapperInterface;
use App\Import\Mapper\MapperItemInterface;

class CommitMapper implements MapperItemInterface
{
    /**
     * {@inheritdoc}
     * Map data in order to create commit(s) objet(s).
     *
     * @return Commit[]
     */
    public function map($data, string $type, array $context = []): array
    {
        $commitsToMap = $data['payload']['commits'];
        $createdAt = $context[self::CONTEXT_IMPORT_DATE] ?? null;

        $objects = [];

        foreach ($commitsToMap as $item) {
            $objects[] = new Commit(
                $item['sha'] ?? null,
                $item['message'] ?? null,
                $createdAt
            );
        }

        return $objects;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($data, string $type): bool
    {
        return MapperInterface::EVENT_PUSH === $type
            && \is_array($data)
            && isset($data['payload']['commits'])
        ;
    }
}
