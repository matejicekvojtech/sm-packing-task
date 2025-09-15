<?php

declare(strict_types=1);

namespace App\Provider;

use App\Client\BinPackingClient;
use App\Client\BinPackingClientException;
use App\Entity\MinimalPackaging;
use App\Entity\Packaging;
use App\Service\MinimalPackagingService;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Contracts\Cache\CacheInterface;

readonly class BinPackingApiMinimalPackagingProvider implements MinimalPackagingProviderInterface
{
    use GetPackagingsTrait;

    public function __construct(
        EntityManagerInterface $entityManager,
        CacheInterface $cache,
        private BinPackingClient $binPackingClient,
        private MinimalPackagingService $minimalPackagingService,
    ) {
        $this->packagingRepository = $entityManager->getRepository(Packaging::class);
        $this->cache = $cache;
    }

    public function findMinimalPackaging(array $products): ?MinimalPackaging
    {
        $packagings = $this->getAllPackagings();
        if (empty($packagings)) {
            return null;
        }

        try {
            $binPackingDto = $this->binPackingClient->getMinimalPackaging($products, $packagings);

            $resultPackaging = $this->getPackaging($binPackingDto->packingId);
            if (null === $resultPackaging) {
                throw new MinimalPackagingProviderException(
                    sprintf('Api returned packaging with id [%d] but no such was found in database.', $binPackingDto->packingId),
                );
            }

            return $this->minimalPackagingService->persistForProducts($resultPackaging, $products, $binPackingDto->volumeUtilization);
        } catch (GuzzleException) {
            // HTTP error - application will continue with fallback provider
            return null;
        } catch (BinPackingClientException $e) {
            throw new MinimalPackagingProviderException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
