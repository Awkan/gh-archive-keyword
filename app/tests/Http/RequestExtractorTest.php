<?php

declare(strict_types=1);

namespace App\Tests\Http;

use App\Http\RequestExtractor;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class RequestExtractorTest extends TestCase
{
    /** @var ObjectProphecy|RequestStack */
    private ObjectProphecy $requestStackProphecy;
    private Request $request;

    private RequestExtractor $sut;

    protected function setUp(): void
    {
        $this->requestStackProphecy = $this->prophesize(RequestStack::class);
        $this->request = new Request();

        $this->sut = new RequestExtractor(
            $this->requestStackProphecy->reveal()
        );

        $this->requestStackProphecy->getMasterRequest()->willReturn($this->request);
    }

    /**
     * Test getQueryParams.
     */
    public function testGetQueryParams(): void
    {
        $queryParams = [
            'key1' => 'value1',
            'key2' => 'value2',
        ];

        $this->requestStackProphecy->getMasterRequest()->shouldBeCalled();

        foreach ($queryParams as $key => $value) {
            $this->request->query->set($key, $value);
        }

        $this->assertEquals(
            $queryParams,
            $this->sut->getQueryParams()
        );
    }
}
