<?php

namespace App\Service;

use DateTime;
use App\DTO\TravelCalculationDTO;
use App\Service\DiscountConfigService;

class TravelCostCalculator
{
    private array $childDiscountConfig; 
    private array $earlyBookingConfig; 

    public function __construct(
        private DiscountConfigService $configProvider,
    ){
        $this->childDiscountConfig = $configProvider->getChildDiscountConfig();
        $this->earlyBookingConfig = $configProvider->getEarlyBookingDiscountConfig();
    }

    public function calculateFinalCost(TravelCalculationDTO $dto): int
    {   
        $baseCost = $dto->baseCost;
        $age = $this->calculateAge($dto->birthDate, $dto->travelStartDate);
        $сhildDiscountValue = $this->calculateChildDiscountValue($age, $baseCost, $this->childDiscountConfig);
        $discountedCost = $baseCost - $сhildDiscountValue;

        $earlyBookingDiscountValue = 0;
        if ($dto->paymentDate) {
            $earlyBookingDiscountValue = $this->calculateEarlyBookingDiscountValue($dto->travelStartDate, $dto->paymentDate, $discountedCost, $this->earlyBookingConfig);
        }

        return $discountedCost - $earlyBookingDiscountValue;
    }

    private function calculateAge(DateTime $birthDate, DateTime $travelStartDate): ?int
    {
        return $birthDate?->diff($travelStartDate)->y;
    }

    private function calculateChildDiscountValue(int $age, int $baseCost, array $config): int
    {
        foreach ($config as $rule) {
            if ($age >= $rule['min_age'] && $age < $rule['max_age']) {
                return min((int) round($baseCost * $rule['discount']), $rule['max_discount'] ?? PHP_INT_MAX);
            }
        }
        return 0;
    }

    public function calculateEarlyBookingDiscountValue(DateTime $travelStartDate, DateTime $paymentDate, int $discountedCost, array $config): int
    {
        $discounts = []; 
        $referenceYear = (int)$paymentDate->format('Y');
        
        foreach ($config as $rule) {
            if ($this->isBetween($travelStartDate, $rule['dateStart'], $rule['dateEnd'], $referenceYear)) {
                foreach ($rule['discounts'] as $discountData) {
                    if ($this->isBetween($paymentDate, $discountData['dateStart'], $discountData['dateEnd'], $referenceYear)) {
                        $currentDiscountAmount = (int) round(min($discountedCost * $discountData['discount'], 1500));
                        $discounts[] = $currentDiscountAmount;
                    }
                }
            }
        }
        return !empty($discounts) ? max($discounts) : 0;
    }

    private function isBetween(DateTime $date, string $dateDefinitionStart, string $dateDefinitionEnd, int $referenceYear): bool 
    {   
        $startDate = ($dateDefinitionStart === '-inf') ? null : $this->parseDateString($dateDefinitionStart, $referenceYear);
        $endDate = ($dateDefinitionEnd === '+inf') ? null : $this->parseDateString($dateDefinitionEnd, $referenceYear);

        return ($startDate === null || $date >= $startDate) && ($endDate === null || $date <= $endDate);
    }

    private function parseDateString(string $dateString, int $referenceYear): ?DateTime
    {
        list($day, $yearModifier, $month) = explode(' ', strtolower(trim($dateString)));
        
        $date = DateTime::createFromFormat('Y F j', sprintf('%d %s %d', $referenceYear, $month, $day));
    
        if (!$date) {
            return null; 
        }
    
        if ($yearModifier === 'next' && $date < new DateTime()) {
            $date->modify('+1 year');
        }
    
        return $date;
    }
}

