<?php

namespace App\Service;

class DiscountConfigService
{   
    // Конфиг мог бы задаваться из админки и лежать в базе.
    // Т.к. по заданию есть такие понятия как текущий, следующий, ввожу модификаторы current и next. Ожидаем с фронта в таком формате.
    public function getEarlyBookingDiscountConfig(): array
    {
        return [
            [
                'dateStart' => '1 next april', 
                'dateEnd' => '30 next september',
                'discounts' => [
                    [
                        'dateStart' => '-inf',
                        'dateEnd' => '30 current november',
                        'discount' => 0.07,
                    ],
                    [
                        'dateStart' => '1 current december',
                        'dateEnd' => '31 current december',
                        'discount' => 0.05,
                    ],
                    [
                        'dateStart' => '1 next january',
                        'dateEnd' => '31 next january',
                        'discount' => 0.03,
                    ],
                ]
            ],
            [
                'dateStart' => '1 current october',
                'dateEnd' => '14 next january',
                'discounts' => [
                    [
                        'dateStart' => '-inf',
                        'dateEnd' => '30 current april',
                        'discount' => 0.07,
                    ],
                    [
                        'dateStart' => '1 current may',
                        'dateEnd' => '31 current may',
                        'discount' => 0.05,
                    ],
                    [
                        'dateStart' => '1 current june',
                        'dateEnd' => '30 current june',
                        'discount' => 0.03,
                    ],
                ]
            ],
            [
                'dateStart' => '15 next january',
                'dateEnd' => '+inf',
                'discounts' => [
                    [
                        'dateStart' => '-inf',
                        'dateEnd' => '31 current august',
                        'discount' => 0.07,
                    ],
                    [
                        'dateStart' => '1 current september',
                        'dateEnd' => '30 current september',
                        'discount' => 0.05,
                    ],
                    [
                        'dateStart' => '1 current october',
                        'dateEnd' => '31 current october',
                        'discount' => 0.03,
                    ],
                ]
            ],
            [
                'dateStart' => '1 current april', 
                'dateEnd' => '30 current september',
                'discounts' => [
                    [
                        'dateStart' => '1 current january',
                        'dateEnd' => '31 current january',
                        'discount' => 0.03,
                    ],
                ]
            ]
        ];
    }

    public function getChildDiscountConfig()
    {
        return [
            ['min_age' => 0, 'max_age' => 3, 'discount' => 0.00],
            ['min_age' => 3, 'max_age' => 6, 'discount' => 0.80],
            ['min_age' => 6, 'max_age' => 12, 'discount' => 0.30, 'max_discount' => 4500],
            ['min_age' => 12, 'max_age' => 18, 'discount' => 0.10],
        ];
    }
}
