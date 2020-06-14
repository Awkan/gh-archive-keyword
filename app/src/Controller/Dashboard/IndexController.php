<?php

declare(strict_types=1);

namespace App\Controller\Dashboard;

use App\DTO\DashboardDTO;
use App\Http\RequestExtractor;
use App\Repository\CommitRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

class IndexController extends AbstractController
{
    private RequestExtractor $requestExtractor;
    private CommitRepository $commitRepository;

    public function __construct(RequestExtractor $requestExtractor, CommitRepository $commitRepository)
    {
        $this->requestExtractor = $requestExtractor;
        $this->commitRepository = $commitRepository;
    }

    public function __invoke(): JsonResponse
    {
        $queryParams = $this->requestExtractor->getQueryParams();

        // TODO : Verify that queryParams are valid (e.g. Date format) @see https://github.com/Awkan/gh-archive-keyword/issues/9

        $dto = new DashboardDTO();
        $dto->setNbCommits($this->commitRepository->countBy($queryParams));

        return $this->json($dto);
    }
}
