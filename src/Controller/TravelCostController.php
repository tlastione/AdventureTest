<?php

namespace App\Controller;

use App\DTO\TravelCalculationDTO;
use App\Service\TravelCostCalculator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/api', format: 'json' )] 
class TravelCostController extends AbstractController
{   
    public function __construct(
        private TravelCostCalculator $travelCostCalculator,
    ) {}
    
    #[Route('/calculations-cost', name: 'calculation_cost', methods: ['POST'])]
    public function calculateCost(
        #[MapRequestPayload] TravelCalculationDTO $dto
    ): Response
    {
        $finalCost = $this->travelCostCalculator->calculateFinalCost($dto);
        return $this->json([
            'final_cost' => $finalCost, 
        ], JsonResponse::HTTP_CREATED);    
    }
}
