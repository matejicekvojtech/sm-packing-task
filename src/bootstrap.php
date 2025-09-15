<?php

declare(strict_types=1);

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Dotenv\Dotenv;

require __DIR__ . '/../vendor/autoload.php';

// loads .env configuration
$dotenv = new Dotenv();
$dotenv->loadEnv(__DIR__ . '/../.env');

// create DI container
$container = new ContainerBuilder();

// load services.yaml for DI
$loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../config'));
$loader->load('services.yaml');

// set db configuration from .env
$container->setParameter('db.driver', (string) $_ENV['DB_DRIVER']);
$container->setParameter('db.user', (string) $_ENV['DB_USER']);
$container->setParameter('db.password', (string) $_ENV['DB_PASSWORD']);
$container->setParameter('db.name', (string) $_ENV['DB_NAME']);
$container->setParameter('db.host', (string) $_ENV['DB_HOST']);

// set bin packing API configuration from .env
$container->setParameter('binpacking.url', (string) $_ENV['BINPACKING_URL']);
$container->setParameter('binpacking.username', (string) $_ENV['BINPACKING_USERNAME']);
$container->setParameter('binpacking.api_key', (string) $_ENV['BINPACKING_API_KEY']);

$container->compile();

return $container;
