<?php

namespace App\Command;

use App\Entity\Type;
use App\Entity\Produit;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Faker\Factory;
use Symfony\Component\Console\Attribute\AsCommand;
#[AsCommand(name: 'app:generate-data',     description: 'Remplit la base de données avec des données fictives.',)]

class GenerateDataCommand extends Command
{
    private $entityManager;
    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
    }
    protected function configure()
    {
        $this->setDescription('Remplit la base de données avec des données fictives.');
    }
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $faker = Factory::create();
        $types = ['pull', 'sweat', 'T Shirt'];

        foreach ($types as $typeLabel) {
            $type = new Type();
            $type->setType($typeLabel);
            $this->entityManager->persist($type);
        }
        $this->entityManager->flush();
        $typeEntities = $this->entityManager->getRepository(Type::class)->findAll();
        for ($i = 0; $i < 20; $i++) {
            $produit = new Produit();
            $produit->setName($faker->word);
            $produit->setPrice($faker->randomFloat(2, 10, 100));
            $produit->setType($faker->randomElement($typeEntities));
            $this->entityManager->persist($produit);
        }
        $this->entityManager->flush();
        $output->writeln('Données créées avec succès.');
        return Command::SUCCESS;
    }
}
