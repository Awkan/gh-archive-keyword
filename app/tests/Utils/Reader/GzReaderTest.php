<?php

declare(strict_types=1);

namespace App\Tests\Utils\Reader;

use App\Utils\Reader\GzReader;
use App\Utils\Reader\ReaderInterface;
use PHPUnit\Framework\TestCase;

class GzReaderTest extends TestCase
{
    private string $testFilePath;

    private GzReader $sut;

    protected function setUp(): void
    {
        $this->testFilePath = __DIR__ . '/../../dummy.gz';

        $this->sut = new GzReader();
    }

    /**
     * Test class inheritence.
     */
    public function testInheritence(): void
    {
        $this->assertInstanceOf(ReaderInterface::class, $this->sut);
    }

    /**
     * Test read.
     */
    public function testRead(): void
    {
        $expectedData = \gzfile($this->testFilePath);

        $this->assertEquals(
            $expectedData,
            $this->sut->read($this->testFilePath)
        );
    }
}
