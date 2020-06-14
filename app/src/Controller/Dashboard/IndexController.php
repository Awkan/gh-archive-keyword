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
        $criteria = $this->requestExtractor->getQueryParams();

        $dto = new DashboardDTO();
        $dto->setNbCommits($this->commitRepository->countBy($criteria));

        return $this->json($dto);
    }
}
