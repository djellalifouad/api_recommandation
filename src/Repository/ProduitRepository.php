<?php
namespace App\Repository;

use App\Entity\Produit;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ProduitRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Produit::class);
    }

    public function findByTypeLabel($typeLabel)
    {
        return $this->createQueryBuilder('p')
            ->join('p.type', 't')
            ->where('t.type = :type')
            ->setParameter('type', $typeLabel)
            ->getQuery()
            ->getResult();
    }
}
