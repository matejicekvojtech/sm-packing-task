<?php

declare(strict_types=1);

namespace App\Provider;

use App\Entity\Packaging;
use Doctrine\ORM\EntityRepository;
use Psr\Cache\InvalidArgumentException;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

trait GetPackagingsTrait
{
    /**
     * @var EntityRepository<Packaging>
     */
    private readonly EntityRepository $packagingRepository;

    private readonly CacheInterface $cache;

    /**
     * @return list<Packaging>
     * @throws InvalidArgumentException
     */
    protected function getAllPackagings(): array
    {
        return $this->cache->get(
            'packagings',
            function (ItemInterface $item) {
                $item->expiresAfter(3600);

                return $this->packagingRepository->findAll();
            },
        );
    }

    protected function getPackaging(int $id): ?Packaging
    {
        return $this->packagingRepository->find($id);
    }
}
