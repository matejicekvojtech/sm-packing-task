<?php

declare(strict_types=1);

namespace App\Registry;

final class ServiceRegistry
{
    /**
     * @var iterable<string, object>
     */
    private iterable $services = [];

    /**
     * @param object[] $services
     */
    public function __construct(
        iterable $services,
    ) {
        foreach ($services as $service) {
            /* @phpstan-ignore-next-line */
            $this->services[$service::class] = $service;
        }
    }

    /**
     * @return iterable<string, object>
     */
    public function all(): iterable
    {
        return $this->services;
    }

    /**
     * @param class-string $className
     */
    public function get(string $className): object
    {
        /* @phpstan-ignore-next-line */
        if (! isset($this->services[$className])) {
            throw new \InvalidArgumentException(
                sprintf('Service "%s" not found.', $className),
            );
        }

        return $this->services[$className];
    }
}
