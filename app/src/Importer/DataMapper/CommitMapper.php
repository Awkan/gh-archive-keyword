<?php

declare(strict_types=1);

namespace App\Importer\DataMapper;

use App\Entity\Commit;

class CommitMapper implements MapperItemInterface
{
    /**
     * {@inheritdoc}
     *
     * @return Commit[]
     */
    public function map($data, string $type, array $context = []): array
    {
        $commitsToMap = $data['payload']['commits'];
        $createdAt = $context[self::CONTEXT_IMPORT_DATE] ?? null;

        $objects = [];

        foreach ($commitsToMap as $item) {
            // TODO : create object via __construct or Commit::createFromArchive($sha, $message)
            $commit = new Commit();
            $commit->setSha($item['sha'] ?? null);
            $commit->setMessage($item['message'] ?? null);
            $commit->setCreatedAt($createdAt);
            $objects[] = $commit;
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
