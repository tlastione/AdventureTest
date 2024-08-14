<?php

namespace App\DTO;

use App\Exception\InvalidBirthDateException;
use App\Exception\InvalidDateFormatException;
use App\Exception\InvalidPaymentDateException;
use Symfony\Component\Validator\Constraints as Assert;

class TravelCalculationDTO
{   
    #[Assert\Type(type: 'integer', message: 'The value {{ value }} is not a valid {{ type }}.')]
    #[Assert\Positive(message: 'The base cost must be a positive number.')]
    public int $baseCost;

    public \DateTime $birthDate;

    public \DateTime $travelStartDate;

    public ?\DateTime $paymentDate;

    public function __construct(int $baseCost, string $birthDate, ?string $travelStartDate = null, ?string $paymentDate = null)
    {
        $this->baseCost = $baseCost;
        $this->birthDate = $this->validateAndCreateDate($birthDate, 'birthDate');
        $this->travelStartDate = $travelStartDate ? $this->validateAndCreateDate($travelStartDate, 'travelStartDate') : new \DateTime();
        $this->paymentDate = $paymentDate ? $this->validateAndCreateDate($paymentDate, 'paymentDate') : null;

        if ($this->birthDate > $this->travelStartDate) {
            throw new InvalidBirthDateException;
        }
        
        //Предполагаю, что дата оплаты не может быть позже, чем дата тура
        if ($this->paymentDate > $this->travelStartDate) {
            throw new InvalidPaymentDateException;
        }
    }

    //Можно было бы сделать кастомный валидатор
    private function validateAndCreateDate(string $dateString, string $fieldName): \DateTime
    {   
        $date = \DateTime::createFromFormat('d.m.Y', $dateString);
        if (!$date || $date->format('d.m.Y') !== $dateString) {
            throw new InvalidDateFormatException($fieldName, $dateString);
        }
        return $date;
    }
}
