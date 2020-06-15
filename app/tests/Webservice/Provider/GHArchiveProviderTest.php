<?php

declare(strict_types=1);

namespace App\Tests\Webservice\Provider;

use App\Utils\Reader\ReaderInterface;
use App\Webservice\Provider\GHArchiveProvider;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;

class GHArchiveProviderTest extends TestCase
{
    /** @var ObjectProphecy|ReaderInterface */
    private ObjectProphecy $readerProphecy;

    private GHArchiveProvider $sut;

    protected function setUp(): void
    {
        $this->readerProphecy = $this->prophesize(ReaderInterface::class);

        $this->sut = new GHArchiveProvider(
            $this->readerProphecy->reveal()
        );
    }

    /**
     * Test fetch.
     */
    public function testFetch(): void
    {
        $year = '2020';
        $month = '06';
        $day = '15';
        $hour = '22';
        $path = \sprintf(GHArchiveProvider::TEMPLATE_PATH_GET_ARCHIVE, $year, $month, $day, $hour);
        $uri = GHArchiveProvider::BASE_URI . $path;
        $readedData = ['foo', 'bar'];

        $this->readerProphecy->read($uri)->shouldBeCalled()->willReturn($readedData);

        $this->assertEquals(
            $readedData,
            $this->sut->fetch($year, $month, $day, $hour)
        );
    }
}
