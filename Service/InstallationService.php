<?php

// src/Service/InstallationService.php
namespace CommonGateway\KVKBundle\Service;

use App\Entity\DashboardCard;
use App\Entity\Endpoint;
use App\Entity\Entity;
use CommonGateway\CoreBundle\Installer\InstallerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class InstallationService implements InstallerInterface
{
    private EntityManagerInterface $entityManager;
    private ContainerInterface $container;


    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function install()
    {
        $this->checkDataConsistency();
    }

    public function update()
    {
        $this->checkDataConsistency();
    }

    public function uninstall()
    {
        // Do some cleanup
    }

    public function checkDataConsistency()
    {
        // Lets create the KVK zoeken endpoint
        $endpointRepository = $this->entityManager->getRepository('App:Endpoint');
        $entity = $this->entityManager->getRepository('App:Entity')->findOneBy(['reference' => 'https://opencatalogi.nl/kvk.vestigings.schema.json.schema.json']);
        if ($entity instanceof Entity && !$endpointRepository->findOneBy(['name' => 'kvk zoeken'])) {
            // todo: add a const for this just like all the other endpoints!
            $endpoint = new Endpoint($entity, null, ['path' => '/kvk/zoeken', 'methods' => ['GET']]);

            $this->entityManager->persist($endpoint);
            $this->entityManager->flush();
        }
    }
}
