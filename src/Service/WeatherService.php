<?php
namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class WeatherService
{
    private $httpClient;
    private $weatherApiKey;

    public function __construct(HttpClientInterface $httpClient, string $weatherApiKey)
    {
        $this->httpClient = $httpClient;
        $this->weatherApiKey = $weatherApiKey;
    }

    public function getWeather(string $city, string $date)
    {
        $forecastDayIndex = $date === 'tomorrow' ? 1 : 0;
        $response = $this->httpClient->request('GET', 'http://api.weatherapi.com/v1/forecast.json', [
            'query' => [
                'key' => $this->weatherApiKey,
                'q' => $city,
                'days' => 2, // Always fetch 2 days to cover 'today' and 'tomorrow'
            ]
        ]);

        $data = $response->toArray();
        $temp = $data['forecast']['forecastday'][$forecastDayIndex]['day']['avgtemp_c'];

        return [
            'temp' => $temp,
            'is' => $this->getWeatherCondition($temp)
        ];
    }

    private function getWeatherCondition($temperature)
    {
        return $temperature < 10 ? 'cold' : ($temperature <= 20 ? 'mild' : 'hot');
    }
}
