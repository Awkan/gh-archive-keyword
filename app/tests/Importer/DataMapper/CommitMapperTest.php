<?php

declare(strict_types=1);

namespace App\Tests\Importer\DataMapper;

use App\Entity\Commit;
use App\Importer\DataMapper\CommitMapper;
use App\Importer\DataMapper\MapperInterface;
use App\Importer\DataMapper\MapperItemInterface;
use PHPUnit\Framework\TestCase;

class CommitMapperTest extends TestCase
{
    private CommitMapper $sut;

    protected function setUp(): void
    {
        $this->sut = new CommitMapper();
    }
    
    /**
     * Test class inheritence
     */
    public function testInheritence(): void
    {
        $this->assertInstanceOf(MapperItemInterface::class, $this->sut);
    }

    public function testMap(): void
    {
        $fullfilledCommit = [
            // Fullfilled dataset
            'sha' => '123456',
            'message' => 'dummy-message',
        ];
        $emptiestCommit = [
            // Emptiest dataset (empty right now)
        ];
        $data = [
            'payload' => [
                'commits' => [
                    $fullfilledCommit,
                    $emptiestCommit,
                ],
            ],
        ];
        $type = MapperInterface::EVENT_PUSH;
        $contextImportDate = new \DateTimeImmutable();
        $context = [
            MapperInterface::CONTEXT_IMPORT_DATE => $contextImportDate,
        ];

        $result = $this->sut->map($data, $type, $context);
        $fullfilledResult = $result[0];
        $emptiestResult = $result[1];

        // Check fullfilled
        $this->assertInstanceOf(Commit::class, $fullfilledResult);
        $this->assertEquals($fullfilledCommit['sha'], $fullfilledResult->getSha());
        $this->assertEquals($fullfilledCommit['message'], $fullfilledResult->getMessage());
        $this->assertEquals($contextImportDate, $fullfilledResult->getCreatedAt());
        // Check emptiest
        $this->assertInstanceOf(Commit::class, $emptiestResult);
        $this->assertNull($emptiestResult->getSha());
        $this->assertNull($emptiestResult->getMessage());
        $this->assertEquals($contextImportDate, $emptiestResult->getCreatedAt());
    }


    /**
     * Test supports
     */
    public function testSupports(): void
    {
        $data = [
            'payload' => [
                'commits' => [],
            ],
        ];

        $this->assertTrue($this->sut->supports($data, MapperInterface::EVENT_PUSH));
    }

    public function dataProviderNotSupports(): array
    {
        return [
            // payload->commits is not set
            [
                [],
                MapperInterface::EVENT_PUSH,
            ],
            // Not an array
            [
                'IAmNotAnArray',
                MapperInterface::EVENT_PUSH,
            ],
            // Not expected type
            [
                ['payload' => ['commits' => []]],
                'IAmNotTheExpectedType',
            ],
        ];
    }

    /**
     * Test supports
     * Provided data are not supported
     * 
     * @dataProvider dataProviderNotSupports
     */
    public function testNotSupports($data, $type): void
    {
        $this->assertFalse($this->sut->supports($data, $type));
    }
}
