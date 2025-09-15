<?php

declare(strict_types=1);

namespace App;

use App\Dto\RequestProductDto;
use App\Entity\Product;
use App\Provider\MinimalPackagingProviderException;
use App\Provider\MinimalPackagingProviderInterface;
use App\Service\ProductService;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Serializer\Exception\MissingConstructorArgumentsException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class Application
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ProductService $productService,
        private MinimalPackagingProviderInterface $minimalPackagingProvider,
        private SerializerInterface $serializer,
        private ValidatorInterface $validator,
    ) {}

    public function run(RequestInterface $request): ResponseInterface
    {
        $data = $request->getBody()->getContents();

        try {
            $products = $this->getProductDataFromRequest($data);
        } catch (ApplicationException $exception) {
            return $this->jsonResponse(
                $exception->getCode(),
                [
                    'error' => $exception->getMessage(),
                ],
            );
        }

        try {
            $minimalPackaging = $this->minimalPackagingProvider->findMinimalPackaging($products);
            if (null === $minimalPackaging) {
                return $this->jsonResponse(404, [
                    'error' => 'No possible minimal packaging found.',
                ]);
            }

            return $this->jsonResponse(200, [
                'minimal_packaging' => $minimalPackaging->serializeToArray(),
            ]);
        } catch (MinimalPackagingProviderException $exception) {
            return $this->jsonResponse(
                $exception->getCode(),
                [
                    'error' => $exception->getMessage(),
                ],
            );
        }
    }

    /**
     * @return Product[]
     *
     * @throws ApplicationException
     */
    public function getProductDataFromRequest(string $request): array
    {
        try {
            /** @var array<string, array<string, float|int>[]> $productsData */
            $productsData = json_decode($request, true, flags: JSON_THROW_ON_ERROR);
            if (! array_key_exists('products', $productsData)) {
                throw new ApplicationException('No products data provided.', 400);
            }
        } catch (\JsonException) {
            throw new ApplicationException('Invalid JSON request.', 400);
        }

        $createdNewProducts = false;
        foreach ($this->denormalizeProductsToDto($productsData['products']) as $productDto) {
            $products[] = $this->productService->findByDimensionsOrCreate(
                $productDto,
                $createdNewProducts,
            );
        }

        if (true === $createdNewProducts) {
            $this->entityManager->flush();
        }

        if (empty($products)) {
            throw new ApplicationException('No products data provided.', 400);
        }

        return $products;
    }

    /**
     * @param array<string, float|int>[] $productsData
     */
    private function denormalizeProductsToDto(array $productsData): \Generator
    {
        foreach ($productsData as $productData) {
            try {
                /* @phpstan-ignore-next-line method.notExists */
                $productDto = $this->serializer->denormalize($productData, RequestProductDto::class);
                $errors = $this->validator->validate($productDto);
                if ($errors->count() > 0) {
                    throw new ApplicationException(
                        sprintf(
                            'Product [id: %d]: %s: %s',
                            $productDto->id,
                            $errors->get(0)->getPropertyPath(),
                            $errors->get(0)->getMessage(),
                        ),
                        400,
                    );
                }

                yield $productDto;
            } catch (MissingConstructorArgumentsException $e) {
                throw new ApplicationException(
                    sprintf(
                        'Product [id: %d]: Missing required properties: [%s]',
                        $productData['id'],
                        implode(', ', $e->getMissingConstructorArguments()),
                    ),
                    400,
                );
            }
        }
    }

    /**
     * @param array<string, mixed> $data
     *
     * @throws ApplicationException
     */
    private function jsonResponse(int $code, array $data): ResponseInterface
    {
        try {
            $responseBody = json_encode($data, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT);
        } catch (\JsonException $e) {
            throw new ApplicationException($e->getMessage(), $e->getCode(), $e);
        }

        return new Response(
            $code,
            [
                'Content-Type' => 'application/json',
            ],
            $responseBody,
        );
    }
}
