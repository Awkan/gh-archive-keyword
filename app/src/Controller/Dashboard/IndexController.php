<?php

declare(strict_types=1);

namespace App\Controller\Dashboard;

use App\DTO\DashboardDTO;
use App\Repository\CommitRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

class IndexController extends AbstractController
{
    private CommitRepository $commitRepository;

    public function __construct(CommitRepository $commitRepository)
    {
        $this->commitRepository = $commitRepository;
    }

    public function __invoke(): JsonResponse
    {
        $dto = new DashboardDTO();

        $dto->setNbCommits($this->commitRepository->countBy([]));

        return $this->json($dto);
    }
}
