<?php

declare(strict_types=1);

namespace App\Import\Mapper;

use App\Exception\MapperNotFoundException;

class ChainMapper implements MapperInterface
{
    /** @var MapperItemInterface[] */
    private array $mappers;

    /**
     * Constructor.
     *
     * @param iterable<MapperItemInterface> $mappers
     */
    public function __construct(iterable $mappers)
    {
        foreach ($mappers as $mapper) {
            $this->addMapper($mapper);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function map($data, string $type, array $context = [])
    {
        foreach ($this->mappers as $mapper) {
            if (false === $mapper->supports($data, $type)) {
                continue;
            }

            return $mapper->map($data, $type, $context);
        }

        throw new MapperNotFoundException('No mapper was found for type ' . $type);
    }

    /**
     * Add a mapper to the chain.
     */
    private function addMapper(MapperItemInterface $mapper): void
    {
        $this->mappers[] = $mapper;
    }
}
