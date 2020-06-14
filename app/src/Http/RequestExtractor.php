<?php

declare(strict_types=1);

namespace App\Http;

use Symfony\Component\HttpFoundation\RequestStack;

class RequestExtractor
{
    private RequestStack $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    /**
     * Get every query params contained in master request.
     *
     * @return string[]
     */
    public function getQueryParams(): array
    {
        $request = $this->requestStack->getMasterRequest();

        return $request->query->all();
    }
}
