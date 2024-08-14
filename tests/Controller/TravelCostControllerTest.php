<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TravelCostControllerTest extends WebTestCase
{
    /**
     * Тестирование валидных сценариев
     * @dataProvider provideValidCalculateCostScenarios
     */
    public function testCalculateCostValid($requestData, $expectedFinalCost): void
    {        
        $client = static::createClient();
        
        try {
            $jsonData = json_encode($requestData, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            $this->fail('JSON encoding failed: ' . $e->getMessage());
        }
    
        $client->request('POST', 'api/calculations-cost', [], [], ['CONTENT_TYPE' => 'application/json'], $jsonData);
        $response = $client->getResponse();
    
        try {
            $responseContent = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);
            $finalCost = $responseContent['final_cost'];
        } catch (\JsonException $e) {
            $this->fail('JSON decoding failed: ' . $e->getMessage());
        }
    
        $this->assertResponseIsSuccessful();
        $this->assertSame($expectedFinalCost, $finalCost);
    }

    /**
     * Тестирование невалидных сценариев
     * @dataProvider provideInvalidCalculateCostScenarios
     */
    public function testCalculateCostInvalid(array $requestData, string $expectedMessage, int $expectedStatusCode): void
    {
        $client = static::createClient();
    
        try {
            $jsonData = json_encode($requestData, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            $this->fail('JSON encoding failed: ' . $e->getMessage());
        }
    
        $client->request('POST', 'api/calculations-cost', [], [], ['CONTENT_TYPE' => 'application/json'], $jsonData);
    
        $this->assertResponseStatusCodeSame($expectedStatusCode);
    
        try {
            $responseContent = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            $this->fail('JSON decoding failed: ' . $e->getMessage());
        }
    
        $this->assertArrayHasKey('detail', $responseContent);
        $this->assertSame($expectedMessage, $responseContent['detail']);
    }

    public function provideValidCalculateCostScenarios(): array
    {
        return [
            //БЕЗ СКИДОК
            // Ребенок до 3х лет без каких либо скидок 
            [['baseCost' => 1000, 'birthDate' => '01.06.2022', 'travelStartDate' => '01.01.2023', 'paymentDate' => '01.12.2022'], 
            1000
            ],
            // Взрослый без каких либо скидок 
            [['baseCost' => 1000, 'birthDate' => '01.06.2000', 'travelStartDate' => '01.01.2023', 'paymentDate' => '01.12.2022'], 
            1000
            ],

            // СКИДКИ ПО ВОЗРАСТУ 
            // Ребенок до от 3х до 5 лет лет
            [['baseCost' => 1000, 'birthDate' => '01.06.2019', 'travelStartDate' => '01.01.2023', 'paymentDate' => '01.12.2022'], 
            200
            ],
            // ребенок от 6 лет до 12 
            [['baseCost' => 1000, 'birthDate' => '01.06.2016', 'travelStartDate' => '01.01.2023', 'paymentDate' => '01.12.2022'], 
            700
            ],
            // ребенок от 6 лет до 12 лет но скидка должна быть 4500
            [['baseCost' => 20000, 'birthDate' => '01.06.2016', 'travelStartDate' => '01.01.2023', 'paymentDate' => '01.12.2022'], 
            15500
            ],
            // ребенок от 12 лет до 18 
            [['baseCost' => 1000, 'birthDate' => '01.06.2008', 'travelStartDate' => '01.01.2023', 'paymentDate' => '01.12.2022'], 
            900
            ],

            // СКИДКИ ЗА РАННЕЕ БРОНИРОВАНИЕ
            // 1 апреля - 30 сентября 
            // Оплата до конца ноября текущего года. Ожидаемая скидка: 7%
            [['baseCost' => 1000, 'birthDate' => '01.06.1990', 'travelStartDate' => '01.04.2023', 'paymentDate' => '30.11.2022'],
            930
            ],
            // Оплата в декабре текущего года. Ожидаемая скидка: 5%
            [['baseCost' => 1000, 'birthDate' => '01.06.1990', 'travelStartDate' => '15.05.2023', 'paymentDate' => '15.12.2022'],
            950
            ],
            // Оплата в январе следующего года. Ожидаемая скидка: 3%
            [['baseCost' => 1000, 'birthDate' => '01.06.1990', 'travelStartDate' => '20.09.2023', 'paymentDate' => '10.01.2023'],
            970
            ],
            // 1 откября - 14 января
            // Оплата до конца апреля текущего года. Ожидаемая скидка: 7%
            [['baseCost' => 1000, 'birthDate' => '01.06.1990', 'travelStartDate' => '10.10.2022', 'paymentDate' => '29.04.2022'],
            930
            ],
            // Оплата до конца мая текущего года. Ожидаемая скидка: 5%
            [['baseCost' => 1000, 'birthDate' => '01.06.1990', 'travelStartDate' => '14.01.2023', 'paymentDate' => '15.05.2022'],
            950
            ],
            // Оплата до конца июня текущего года. Ожидаемая скидка: 3%
            [['baseCost' => 1000, 'birthDate' => '01.06.1990', 'travelStartDate' => '05.11.2022', 'paymentDate' => '25.06.2022'],
            970
            ],

            // 15 января и далее 
            // Оплата до конца августа текущего года. Ожидаемая скидка: 7%
            [['baseCost' => 1000, 'birthDate' => '01.06.1990', 'travelStartDate' => '20.01.2023', 'paymentDate' => '30.08.2022'],
            930
            ],
            // Оплата в сентябре текущего года. Ожидаемая скидка: 5%
            [['baseCost' => 1000, 'birthDate' => '01.06.1990', 'travelStartDate' => '25.02.2023', 'paymentDate' => '15.09.2022'],
            950
            ],
            // Оплата в октябре текущего года. Ожидаемая скидка: 3%
            [['baseCost' => 1000, 'birthDate' => '01.06.1990', 'travelStartDate' => '30.03.2023', 'paymentDate' => '10.10.2022'],
            970
            ],

            //СКИДКА ЗА ВОЗРАСТ И ЗА РАННЕЕ БРОНИРОВАНИЕ 

            // 1 апреля - 30 сентября 
            // Возраст 4 года скидка 80% , оплата до конца ноября текущего года. Ожидаемая скидка: 7% 
            [['baseCost' => 1000, 'birthDate' => '01.06.2019', 'travelStartDate' => '01.04.2023', 'paymentDate' => '30.11.2022'],
            186
            ],
        ];
    }

    public function provideInvalidCalculateCostScenarios()
    {
        return [
            // Нет обязательного поля birthDate
            [
                ['baseCost' => 1000, 'birthDate' => '', 'travelStartDate' => '01.01.2023', 'paymentDate' => '01.12.2022'],
                'Invalid birthDate format: ',
                422
            ],
            // Неправильный формат даты рождения
            [
                ['baseCost' => 1000, 'birthDate' => '1990.06.01', 'travelStartDate' => '01.01.2023', 'paymentDate' => '01.12.2022'],
                'Invalid birthDate format: 1990.06.01',
                422
            ],
            // Дата оплаты позже даты начала поездки
            [
                ['baseCost' => 1000, 'birthDate' => '01.06.1990', 'travelStartDate' => '01.01.2023', 'paymentDate' => '01.12.2024'],
                'Payment date cannot be later than the travel start date.',
                422
            ],
            // Отрицательная стоимость
            [
                ['baseCost' => -1000, 'birthDate' => '01.06.1990', 'travelStartDate' => '01.01.2023', 'paymentDate' => '01.12.2022'],
                'baseCost: The base cost must be a positive number.',
                422
            ],
            // Строка вместо числа в стоимости
            [
                ['baseCost' => "1000", 'birthDate' => '01.06.1990', 'travelStartDate' => '01.01.2023', 'paymentDate' => '01.12.2022'],
                'baseCost: This value should be of type int.',
                422
            ],
            // Неправильный формат даты начала поездки
            [
                ['baseCost' => 1000, 'birthDate' => '01.06.1990', 'travelStartDate' => '2023.01.01', 'paymentDate' => '01.12.2022'],
                'Invalid travelStartDate format: 2023.01.01',
                422
            ],
            // Дата рождения после даты старта путешествия 
            [
                ['baseCost' => 1000, 'birthDate' => '01.01.2050', 'travelStartDate' => '01.01.2023', 'paymentDate' => '01.12.2022'],
                'Birth date cannot be later than the travel start date.',
                422
            ],
        ];
    }
}
