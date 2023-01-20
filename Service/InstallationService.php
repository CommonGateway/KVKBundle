<?php

// src/Service/InstallationService.php
namespace CommonGateway\KVKBundle\Service;

use App\Entity\DashboardCard;
use App\Entity\Endpoint;
use App\Entity\Entity;
use CommonGateway\CoreBundle\Installer\InstallerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ContainerInterface;

class InstallationService implements InstallerInterface
{
    private EntityManagerInterface $entityManager;
    private ContainerInterface $container;
    private SymfonyStyle $io;

    public const SCHEMAS_THAT_SHOULD_HAVE_ENDPOINTS = [
        ['reference' => 'https://opencatalogi.nl/kvk.vestigings.schema.json.schema.json', 'path' => 'vestigingen','methods' => []],
    ];

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Set symfony style in order to output to the console
     *
     * @param SymfonyStyle $io
     * @return self
     */
    public function setStyle(SymfonyStyle $io): self
    {
        $this->io = $io;

        return $this;
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
        // Let create some endpoints
        $this->createEndpoints($this::SCHEMAS_THAT_SHOULD_HAVE_ENDPOINTS);

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

    private function createEndpoints($objectsThatShouldHaveEndpoints): array
    {
        $endpointRepository = $this->entityManager->getRepository('App:Endpoint');
        $endpoints = [];
        foreach($objectsThatShouldHaveEndpoints as $objectThatShouldHaveEndpoint) {
            $entity = $this->entityManager->getRepository('App:Entity')->findOneBy(['reference' => $objectThatShouldHaveEndpoint['reference']]);
            if ($entity instanceof Entity && !$endpointRepository->findOneBy(['name' => $entity->getName()])) {
                $endpoint = new Endpoint($entity, null, $objectThatShouldHaveEndpoint);

                $this->entityManager->persist($endpoint);
                $this->entityManager->flush();
                $endpoints[] = $endpoint;
            }
        }
        (isset($this->io) ? $this->io->writeln(count($endpoints).' Endpoints Created'): '');

        return $endpoints;
    }
}
