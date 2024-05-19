<?php
namespace App\Service;

use App\Repository\ProduitRepository;

class ProductService
{
private $produitRepository;

public function __construct(ProduitRepository $produitRepository)
{
$this->produitRepository = $produitRepository;
}

public function recommendProducts($temperature)
{
$typeLabel = $this->getProductTypeFromTemperature($temperature);
return $this->produitRepository->findByTypeLabel($typeLabel);
}

private function getProductTypeFromTemperature($temperature)
{

if ($temperature < 10) { return 'pull' ; } elseif ($temperature>= 10 && $temperature <= 20) { return 'sweat' ; } else { return 'T Shirt' ; } } }