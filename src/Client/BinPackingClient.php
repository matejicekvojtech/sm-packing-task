<?php

declare(strict_types=1);

namespace App\Client;

use App\Dto\BinPackingDto;
use App\Entity\Packaging;
use App\Entity\Product;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class BinPackingClient
{
    public function __construct(
        private Client $httpClient,
        private string $username,
        private string $apiKey,
    ) {}

    /**
     * @param Product[]   $products
     * @param Packaging[] $packagings
     *
     * @throws GuzzleException
     */
    public function getMinimalPackaging(array $products, array $packagings): BinPackingDto
    {
        $body = $this->compileRequestBody($products, $packagings);

        try {
            $response = $this->httpClient->post('/packer/packIntoMany', [
                'json' => $body,
            ]);

            $body = json_decode($response->getBody()->getContents(), true, flags: JSON_THROW_ON_ERROR);

            return $this->processResponse($body['response']);
        } catch (\JsonException $e) {
            throw new BinPackingClientException(
                sprintf('Error parsing BinPackaging HTTP response: %s', $e->getMessage()),
            );
        }
    }

    /**
     * @param Product[]   $products
     * @param Packaging[] $packagings
     *
     * @return array<string, mixed>
     */
    private function compileRequestBody(array $products, array $packagings): array
    {
        $body = [
            'username' => $this->username,
            'api_key' => $this->apiKey,
            'bins' => [],
            'items' => [],
            'params' => [
                'optimization_mode' => 'bins_number',
            ],
        ];

        foreach ($packagings as $packagingDto) {
            $body['bins'][] = [
                'id' => $packagingDto->getId(),
                'h' => $packagingDto->getHeight(),
                'w' => $packagingDto->getWidth(),
                'd' => $packagingDto->getLength(),
                'max_wg' => $packagingDto->getMaxWeight(),
            ];
        }

        foreach ($products as $productDto) {
            $body['items'][] = [
                'id' => $productDto->getId(),
                'h' => $productDto->getDimension1(),
                'w' => $productDto->getDimension2(),
                'd' => $productDto->getDimension3(),
                'wg' => $productDto->getWeight(),
                'vr' => 1,
                'q' => 1,
            ];
        }

        return $body;
    }

    /**
     * @param array<string, mixed> $response
     */
    private function processResponse(array $response): BinPackingDto
    {
        if (1 !== $response['status']) {
            if (! empty($response['errors'])) {
                throw new BinPackingClientException(reset($response['errors']));
            }

            throw new BinPackingClientException('Bin packing responded with error status but without error message.');
        }

        if (! empty($response['not_packed_items'])) {
            $itemIds = array_map(
                /** @var array<string, int|string> $item */
                static fn(array $item) => $item['id'],
                $response['not_packed_items'],
            );

            throw new BinPackingClientException(
                sprintf('Could not pack items with ids [%s]', implode(', ', $itemIds)),
            );
        }

        if (count($response['bins_packed']) > 1) {
            throw new BinPackingClientException('Could not pack items with just 1 bin.');
        }

        $binData = $response['bins_packed'][0]['bin_data'] ?? [];
        if (empty($binData)) {
            throw new BinPackingClientException('Could not fetch bin data.');
        }

        return new BinPackingDto(
            $binData['id'],
            round($binData['used_space'], 2),
        );
    }
}
