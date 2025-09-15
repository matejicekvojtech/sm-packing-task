<?php

namespace Test\App;

use App\Application;
use App\Dto\RequestProductDto;
use App\Entity\MinimalPackaging;
use App\Provider\MinimalPackagingProviderException;
use App\Provider\MinimalPackagingProviderInterface;
use App\Service\ProductService;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Exception\MissingConstructorArgumentsException;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ApplicationTest extends TestCase
{
    use TestDataTrait;

    private EntityManagerInterface&MockObject $entityManager;
    private ProductService&MockObject $productService;
    private MinimalPackagingProviderInterface&MockObject $minimalPackagingProvider;
    private SerializerInterface&MockObject $serializer;
    private ValidatorInterface&MockObject $validator;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->productService = $this->createMock(ProductService::class);
        $this->minimalPackagingProvider = $this->createMock(MinimalPackagingProviderInterface::class);
        $this->serializer = $this->createMock(Serializer::class);
        $this->validator = $this->createMock(ValidatorInterface::class);
    }

    public function testItWillSendErrorResponseOnNoProductData(): void
    {
        $request = new Request(
            'POST',
            'http://localhost',
            [
                'Content-Type' => 'application/json',
            ],
            (string) json_encode([], JSON_THROW_ON_ERROR),
        );

        $application = new Application(
            $this->entityManager,
            $this->productService,
            $this->minimalPackagingProvider,
            $this->serializer,
            $this->validator,
        );

        $response = $application->run($request);
        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame(400, $response->getStatusCode());
    }

    public function testItWillSendErrorResponseOnEmptyProductData(): void
    {
        $request = new Request(
            'POST',
            'http://localhost',
            [
                'Content-Type' => 'application/json',
            ],
            (string) json_encode([
                'products' => [],
            ], JSON_THROW_ON_ERROR),
        );

        $this->serializer->expects($this->never())
            ->method('denormalize');

        $application = new Application(
            $this->entityManager,
            $this->productService,
            $this->minimalPackagingProvider,
            $this->serializer,
            $this->validator,
        );

        $response = $application->run($request);
        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame(400, $response->getStatusCode());
    }

    public function testItWillSendErrorResponseOnMissingProductData(): void
    {
        $body = [
            'products' => [
                [
                    'id' => 1,
                    'width' => 5,
                    'height' => 5,
                    'weight' => 5,
                ],
            ],
        ];

        $request = new Request(
            'POST',
            'http://localhost',
            [
                'Content-Type' => 'application/json',
            ],
            (string) json_encode($body, JSON_THROW_ON_ERROR),
        );

        $this->serializer->expects($this->once())
            ->method('denormalize')
            ->with(
                $body['products'][0],
                RequestProductDto::class,
            )
            ->willThrowException(new MissingConstructorArgumentsException('Missing length'));

        $application = new Application(
            $this->entityManager,
            $this->productService,
            $this->minimalPackagingProvider,
            $this->serializer,
            $this->validator,
        );

        $response = $application->run($request);
        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame(400, $response->getStatusCode());
    }

    public function testItWillSendErrorResponseOnInvalidProductData(): void
    {
        $body = [
            'products' => [
                [
                    'id' => 1,
                    'width' => 5,
                    'height' => 5,
                    'length' => 0,
                    'weight' => 5,
                ],
            ],
        ];

        $request = new Request(
            'POST',
            'http://localhost',
            [
                'Content-Type' => 'application/json',
            ],
            (string) json_encode($body, JSON_THROW_ON_ERROR),
        );

        $dto = new RequestProductDto(
            1,
            5,
            5,
            0,
            5,
        );

        $this->serializer->expects($this->once())
            ->method('denormalize')
            ->with(
                $body['products'][0],
                RequestProductDto::class,
            )
            ->willReturn($dto);

        $validations = $this->createMock(ConstraintViolationListInterface::class);
        $this->validator->expects($this->once())
            ->method('validate')
            ->with($dto)
            ->willReturn($validations);

        $validations->expects($this->once())
            ->method('count')
            ->willReturn(1);

        $error = $this->createMock(ConstraintViolationInterface::class);
        $validations->expects($this->exactly(2))
            ->method('get')
            ->willReturn($error);

        $error->expects($this->once())
            ->method('getPropertyPath')
            ->willReturn('length');

        $error->expects($this->once())
            ->method('getMessage')
            ->willReturn('Property must be greater than 0');

        $application = new Application(
            $this->entityManager,
            $this->productService,
            $this->minimalPackagingProvider,
            $this->serializer,
            $this->validator,
        );

        $response = $application->run($request);
        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame(400, $response->getStatusCode());
    }

    public function testItWillSendErrorResponseOnProviderException(): void
    {
        $body = [
            'products' => [
                [
                    'id' => 1,
                    'width' => 5,
                    'height' => 5,
                    'length' => 5,
                    'weight' => 5,
                ],
            ],
        ];

        $request = new Request(
            'POST',
            'http://localhost',
            [
                'Content-Type' => 'application/json',
            ],
            (string) json_encode($body, JSON_THROW_ON_ERROR),
        );

        $dto = new RequestProductDto(
            1,
            5,
            5,
            5,
            5,
        );

        $this->serializer->expects($this->once())
            ->method('denormalize')
            ->with(
                $body['products'][0],
                RequestProductDto::class,
            )
            ->willReturn($dto);

        $validations = $this->createMock(ConstraintViolationListInterface::class);
        $this->validator->expects($this->once())
            ->method('validate')
            ->with($dto)
            ->willReturn($validations);

        $validations->expects($this->once())
            ->method('count')
            ->willReturn(0);

        $this->productService->expects($this->once())
            ->method('findByDimensionsOrCreate')
            ->with($dto, false)
            ->willReturn($this->getProducts()[0]);

        $this->minimalPackagingProvider->expects($this->once())
            ->method('findMinimalPackaging')
            ->with($this->getProducts())
            ->willThrowException(
                new MinimalPackagingProviderException('Some error', 400),
            );

        $application = new Application(
            $this->entityManager,
            $this->productService,
            $this->minimalPackagingProvider,
            $this->serializer,
            $this->validator,
        );

        $response = $application->run($request);
        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame(400, $response->getStatusCode());
    }

    public function testItWillSendErrorResponseOnProviderCouldNotFindPackage(): void
    {
        $body = [
            'products' => [
                [
                    'id' => 1,
                    'width' => 5,
                    'height' => 5,
                    'length' => 5,
                    'weight' => 5,
                ],
            ],
        ];

        $request = new Request(
            'POST',
            'http://localhost',
            [
                'Content-Type' => 'application/json',
            ],
            (string) json_encode($body, JSON_THROW_ON_ERROR),
        );

        $dto = new RequestProductDto(
            1,
            5,
            5,
            5,
            5,
        );

        $this->serializer->expects($this->once())
            ->method('denormalize')
            ->with(
                $body['products'][0],
                RequestProductDto::class,
            )
            ->willReturn($dto);

        $validations = $this->createMock(ConstraintViolationListInterface::class);
        $this->validator->expects($this->once())
            ->method('validate')
            ->with($dto)
            ->willReturn($validations);

        $validations->expects($this->once())
            ->method('count')
            ->willReturn(0);

        $this->productService->expects($this->once())
            ->method('findByDimensionsOrCreate')
            ->with($dto, false)
            ->willReturn($this->getProducts()[0]);

        $this->minimalPackagingProvider->expects($this->once())
            ->method('findMinimalPackaging')
            ->with($this->getProducts())
            ->willReturn(null);

        $application = new Application(
            $this->entityManager,
            $this->productService,
            $this->minimalPackagingProvider,
            $this->serializer,
            $this->validator,
        );

        $response = $application->run($request);
        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame(404, $response->getStatusCode());
    }

    public function testItWillSendSuccessResponse(): void
    {
        $body = [
            'products' => [
                [
                    'id' => 1,
                    'width' => 5,
                    'height' => 5,
                    'length' => 5,
                    'weight' => 5,
                ],
            ],
        ];

        $request = new Request(
            'POST',
            'http://localhost',
            [
                'Content-Type' => 'application/json',
            ],
            (string) json_encode($body, JSON_THROW_ON_ERROR),
        );

        $dto = new RequestProductDto(
            1,
            5,
            5,
            5,
            5,
        );

        $this->serializer->expects($this->once())
            ->method('denormalize')
            ->with(
                $body['products'][0],
                RequestProductDto::class,
            )
            ->willReturn($dto);

        $validations = $this->createMock(ConstraintViolationListInterface::class);
        $this->validator->expects($this->once())
            ->method('validate')
            ->with($dto)
            ->willReturn($validations);

        $validations->expects($this->once())
            ->method('count')
            ->willReturn(0);

        $this->productService->expects($this->once())
            ->method('findByDimensionsOrCreate')
            ->with($dto, false)
            ->willReturn($this->getProducts()[0]);

        $this->minimalPackagingProvider->expects($this->once())
            ->method('findMinimalPackaging')
            ->with($this->getProducts())
            ->willReturn(new MinimalPackaging(
                $this->getPackagings()[0],
                [1],
                50,
            ));

        $application = new Application(
            $this->entityManager,
            $this->productService,
            $this->minimalPackagingProvider,
            $this->serializer,
            $this->validator,
        );

        $response = $application->run($request);
        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame(200, $response->getStatusCode());
    }
}
