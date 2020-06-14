<?php

declare(strict_types=1);

namespace App\Tests\Importer\DataMapper;

use App\Exception\MapperNotFoundException;
use App\Importer\DataMapper\ChainMapper;
use App\Importer\DataMapper\MapperInterface;
use App\Importer\DataMapper\MapperItemInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;

class ChainMapperTest extends TestCase
{
    /** @var ObjectProphecy|MaperItemInterface */
    private ObjectProphecy $mapper1Prophecy;
    /** @var ObjectProphecy|MaperItemInterface */
    private ObjectProphecy $mapper2Prophecy;

    private ChainMapper $sut;

    protected function setUp(): void
    {
        $this->mapper1Prophecy = $this->prophesize(MapperItemInterface::class);
        $this->mapper2Prophecy = $this->prophesize(MapperItemInterface::class);

        $this->sut = new ChainMapper([
            $this->mapper1Prophecy->reveal(),
            $this->mapper2Prophecy->reveal(),
        ]);
    }

    /**
     * Test class inheritence.
     */
    public function testInheritence(): void
    {
        $this->assertInstanceOf(MapperInterface::class, $this->sut);
    }

    /**
     * Test map
     * Since first `mappers` supports, we only use him and not the other(s).
     */
    public function testMapOnFirstMapper(): void
    {
        $data = '{"dummy": "json"}';
        $type = 'dummy-type';
        $mapper1supports = true;
        $mapper1mapping = 'dummy-object';

        // First mapper supports...
        $this->mapper1Prophecy->supports($data, $type)->shouldBeCalled()->willReturn($mapper1supports);
        $this->mapper1Prophecy->map($data, $type, Argument::type('array'))->shouldBeCalled()->willReturn($mapper1mapping);

        // Then the other is not used
        $this->mapper2Prophecy->supports(Argument::cetera())->shouldNotBeCalled();
        $this->mapper2Prophecy->map(Argument::cetera())->shouldNotBeCalled();

        $this->assertEquals(
            $mapper1mapping,
            $this->sut->map($data, $type)
        );
    }

    /**
     * Test map
     * Since first `mappers` not supports data, we try to use another.
     */
    public function testMapOnOtherMapper(): void
    {
        $data = '{"dummy": "json"}';
        $type = 'dummy-type';
        $mapper1supports = false;
        $mapper2supports = true;
        $mapper2mapping = 'dummy-object';

        // First mapper not supports...
        $this->mapper1Prophecy->supports($data, $type)->shouldBeCalled()->willReturn($mapper1supports);
        $this->mapper1Prophecy->map(Argument::cetera())->shouldNotBeCalled();

        // Then the other is used and supports
        $this->mapper2Prophecy->supports($data, $type)->shouldBeCalled()->willReturn($mapper2supports);
        $this->mapper2Prophecy->map($data, $type, Argument::type('array'))->shouldBeCalled()->willReturn($mapper2mapping);

        $this->assertEquals(
            $mapper2mapping,
            $this->sut->map($data, $type)
        );
    }

    /**
     * Test map
     * No mapper supports data.
     */
    public function testMapWhenNoMappersSupportData(): void
    {
        $this->expectException(MapperNotFoundException::class);

        $data = '{"dummy": "json"}';
        $type = 'dummy-type';

        $this->mapper1Prophecy->supports($data, $type)->shouldBeCalled()->willReturn(false);
        $this->mapper2Prophecy->supports($data, $type)->shouldBeCalled()->willReturn(false);

        $this->sut->map($data, $type);
    }
}
