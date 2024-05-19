<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\WeatherService;
use App\Service\ProductService;

class ProductRecommendationController extends AbstractController
{
    private $weatherService;
    private $productService;

    public function __construct(WeatherService $weatherService, ProductService $productService)
    {
        $this->weatherService = $weatherService;
        $this->productService = $productService;
    }

    #[Route('/recommendations', name: 'product_recommendations', methods: ['POST'])]
    public function index(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);
        $city = $data['weather']['city'] ?? null;
        $date = $data['weather']['date'] ?? 'today';

        if (!$city) {
            return $this->json(['error' => 'City is required'], Response::HTTP_BAD_REQUEST);
        }

        $weather = $this->weatherService->getWeather($city, $date);
        $products = $this->productService->recommendProducts($weather['temp']);

        return $this->json([
            'products' => array_map(function ($product) {
                return [
                    'id' => $product->getId(),
                    'name' => $product->getName() . ' ' . $product->getType()->getType(),
                    'price' => $product->getPrice(),
                ];
            }, $products),
            'weather' => [
                'city' => $city,
                'is' => $weather['is'],
                'date' => $date
            ]
        ]);
    }
}
