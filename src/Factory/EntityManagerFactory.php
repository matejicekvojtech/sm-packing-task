<?php

declare(strict_types=1);

namespace App\Factory;

use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\UnderscoreNamingStrategy;
use Doctrine\ORM\ORMSetup;

final readonly class EntityManagerFactory
{
    /**
     * @param array<string, mixed> $connection
     */
    public static function create(array $connection): EntityManager
    {
        $config = ORMSetup::createAttributeMetadataConfiguration(
            [__DIR__ . '/../Entity'],
            true,
        );
        $config->setNamingStrategy(new UnderscoreNamingStrategy());

        return new EntityManager(
            DriverManager::getConnection($connection),
            $config,
        );
    }
}
