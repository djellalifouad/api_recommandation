<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Service\WeatherService;

class ProductRecommendationControllerTest extends WebTestCase
{
    private function createWeatherServiceMock($temperature)
    {
        $weatherService = $this->createMock(WeatherService::class);
        $weatherService->method('getWeather')->willReturn([
            'temp' => $temperature,
            'is' => $this->getWeatherCondition($temperature)
        ]);
        return $weatherService;
    }

    private function getWeatherCondition($temperature)
    {
        return $temperature < 10 ? 'cold' : ($temperature <= 20 ? 'mild' : 'hot');
    }

    private function sendRequest(array $body, WeatherService $weatherService)
    {
        $client = static::createClient();
        $client->getContainer()->set('App\Service\WeatherService', $weatherService);
        $client->request(
            'POST',
            '/recommendations',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($body)
        );
        return $client->getResponse();
    }

    private function assertResponseContains(array $expected, $response)
    {
        $this->assertJson($response->getContent());
        $responseData = json_decode($response->getContent(), true);
        foreach ($expected as $key => $value) {
            $this->assertArrayHasKey($key, $responseData);
            $this->assertEquals($value, $responseData[$key]);
        }
    }

    private function assertProductsContainKeyword($keyword, $responseProducts)
    {
        $this->assertIsArray($responseProducts);
        foreach ($responseProducts as $responseProduct) {
            $this->assertArrayHasKey('id', $responseProduct);
            $this->assertArrayHasKey('name', $responseProduct);
            $this->assertArrayHasKey('price', $responseProduct);
            $this->assertStringContainsString($keyword, strtolower($responseProduct['name']));
        }
    }

    public function testRecommendationForColdWeather()
    {
        $weatherService = $this->createWeatherServiceMock(5); // Cold weather
        $response = $this->sendRequest(['weather' => ['city' => 'Paris']], $weatherService);
        $this->assertEquals(200, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
        $this->assertResponseContains([
            'weather' => ['city' => 'Paris', 'is' => 'cold', 'date' => 'today']
        ], $response);
        $this->assertProductsContainKeyword('pull', $responseData['products']);
    }

    public function testRecommendationForMildWeather()
    {
        $weatherService = $this->createWeatherServiceMock(15); // Mild weather
        $response = $this->sendRequest(['weather' => ['city' => 'Paris']], $weatherService);
        $this->assertEquals(200, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
        $this->assertResponseContains([
            'weather' => ['city' => 'Paris', 'is' => 'mild', 'date' => 'today']
        ], $response);
        $this->assertProductsContainKeyword('sweat', $responseData['products']);
    }

    public function testRecommendationForHotWeather()
    {
        $weatherService = $this->createWeatherServiceMock(25);
        $response = $this->sendRequest(['weather' => ['city' => 'Paris']], $weatherService);
        $this->assertEquals(200, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
        $this->assertResponseContains([
            'weather' => ['city' => 'Paris', 'is' => 'hot', 'date' => 'today']
        ], $response);
        $this->assertProductsContainKeyword('t shirt', $responseData['products']);
    }

    public function testValidRequestBodyWithCityOnly()
    {
        $weatherService = $this->createWeatherServiceMock(15);
        $response = $this->sendRequest(['weather' => ['city' => 'Paris']], $weatherService);
        $this->assertEquals(200, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
        $this->assertResponseContains([
            'weather' => ['city' => 'Paris', 'is' => 'mild', 'date' => 'today']
        ], $response);
        $this->assertProductsContainKeyword('sweat', $responseData['products']);
    }

    public function testValidRequestBodyWithCityAndDate()
    {
        $weatherService = $this->createWeatherServiceMock(15);
        $response = $this->sendRequest(['weather' => ['city' => 'Marseille', 'date' => 'tomorrow']], $weatherService);
        $this->assertEquals(200, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
        $this->assertResponseContains([
            'weather' => ['city' => 'Marseille', 'date' => 'tomorrow', 'is' => 'mild']
        ], $response);
        $this->assertProductsContainKeyword('sweat', $responseData['products']);
    }

    public function testInvalidRequestBodyWithoutCity()
    {
        $weatherService = $this->createWeatherServiceMock(15);
        $response = $this->sendRequest(['weather' => ['date' => 'tomorrow']], $weatherService);
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertResponseContains([
            'error' => 'Invalid request body'
        ], $response);
    }

    public function testInvalidRequestBodyWithInvalidDate()
    {
        $weatherService = $this->createWeatherServiceMock(15);
        $response = $this->sendRequest(['weather' => ['city' => 'Paris', 'date' => 'nextweek']], $weatherService);
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertResponseContains([
            'error' => 'Invalid request body'
        ], $response);
    }
}
