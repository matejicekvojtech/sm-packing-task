<?php

namespace Test\App\Client;

use App\Client\BinPackingClient;
use App\Client\BinPackingClientException;
use App\Dto\BinPackingDto;
use GuzzleHttp\Client;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Test\App\TestDataTrait;

class BinPackingClientTest extends TestCase
{
    use TestDataTrait;

    private Client&MockObject $httpClient;
    private string $username;
    private string $apiKey;

    public function setUp(): void
    {
        $this->httpClient = $this->createMock(Client::class);
        $this->username = 'tester';
        $this->apiKey = 'testApiKey';
    }

    public function testItWillReturnValidDto(): void
    {
        $requestBody = [
            'username' => $this->username,
            'api_key' => $this->apiKey,
            'bins' => $this->getPackagingsArray(),
            'items' => $this->getProductsArray(),
            'params' => [
                'optimization_mode' => 'bins_number',
            ],
        ];

        $responseBody = [
            'response' => [
                'status' => 1,
                'errors' => [],
                'not_packed_items' => [],
                'bins_packed' => [
                    [
                        'bin_data' => [
                            'id' => 1,
                            'used_space' => 50,
                        ],
                    ],
                ],
            ],
        ];

        $response = $this->createMock(ResponseInterface::class);
        $rBody = $this->createMock(StreamInterface::class);

        $this->httpClient->expects($this->once())
            ->method('post')
            ->with(
                '/packer/packIntoMany',
                [
                    'json' => $requestBody,
                ],
            )
            ->willReturn(
                $response,
            );

        $response->expects($this->once())
            ->method('getBody')
            ->willReturn($rBody);

        $rBody->expects($this->once())
            ->method('getContents')
            ->willReturn(json_encode($responseBody, JSON_THROW_ON_ERROR));

        $client = new BinPackingClient(
            $this->httpClient,
            $this->username,
            $this->apiKey,
        );

        $result = $client->getMinimalPackaging($this->getProducts(), $this->getPackagings());
        $this->assertInstanceOf(BinPackingDto::class, $result);
    }

    public function testExceptionOnStatus(): void
    {
        $requestBody = [
            'username' => $this->username,
            'api_key' => $this->apiKey,
            'bins' => $this->getPackagingsArray(),
            'items' => $this->getProductsArray(),
            'params' => [
                'optimization_mode' => 'bins_number',
            ],
        ];

        $responseBody = [
            'response' => [
                'status' => 0,
                'errors' => ['error message'],
            ],
        ];

        $response = $this->createMock(ResponseInterface::class);
        $rBody = $this->createMock(StreamInterface::class);

        $this->httpClient->expects($this->once())
            ->method('post')
            ->with(
                '/packer/packIntoMany',
                [
                    'json' => $requestBody,
                ],
            )
            ->willReturn(
                $response,
            );

        $response->expects($this->once())
            ->method('getBody')
            ->willReturn($rBody);

        $rBody->expects($this->once())
            ->method('getContents')
            ->willReturn(json_encode($responseBody, JSON_THROW_ON_ERROR));

        $client = new BinPackingClient(
            $this->httpClient,
            $this->username,
            $this->apiKey,
        );

        $this->expectException(BinPackingClientException::class);
        $client->getMinimalPackaging($this->getProducts(), $this->getPackagings());
    }

    public function testExceptionOnNotPackedItems(): void
    {
        $requestBody = [
            'username' => $this->username,
            'api_key' => $this->apiKey,
            'bins' => $this->getPackagingsArray(),
            'items' => $this->getProductsArray(),
            'params' => [
                'optimization_mode' => 'bins_number',
            ],
        ];

        $responseBody = [
            'response' => [
                'status' => 1,
                'errors' => [],
                'not_packed_items' => [
                    [
                        'id' => 1,
                    ],
                ],
            ],
        ];

        $response = $this->createMock(ResponseInterface::class);
        $rBody = $this->createMock(StreamInterface::class);

        $this->httpClient->expects($this->once())
            ->method('post')
            ->with(
                '/packer/packIntoMany',
                [
                    'json' => $requestBody,
                ],
            )
            ->willReturn(
                $response,
            );

        $response->expects($this->once())
            ->method('getBody')
            ->willReturn($rBody);

        $rBody->expects($this->once())
            ->method('getContents')
            ->willReturn(json_encode($responseBody, JSON_THROW_ON_ERROR));

        $client = new BinPackingClient(
            $this->httpClient,
            $this->username,
            $this->apiKey,
        );

        $this->expectException(BinPackingClientException::class);
        $client->getMinimalPackaging($this->getProducts(), $this->getPackagings());
    }

    public function testExceptionOnMultipleBins(): void
    {
        $requestBody = [
            'username' => $this->username,
            'api_key' => $this->apiKey,
            'bins' => $this->getPackagingsArray(),
            'items' => $this->getProductsArray(),
            'params' => [
                'optimization_mode' => 'bins_number',
            ],
        ];

        $responseBody = [
            'response' => [
                'status' => 1,
                'errors' => [],
                'bins_packed' => [
                    [
                        'id' => 1,
                    ],
                    [
                        'id' => 2,
                    ],
                ],
            ],
        ];

        $response = $this->createMock(ResponseInterface::class);
        $rBody = $this->createMock(StreamInterface::class);

        $this->httpClient->expects($this->once())
            ->method('post')
            ->with(
                '/packer/packIntoMany',
                [
                    'json' => $requestBody,
                ],
            )
            ->willReturn(
                $response,
            );

        $response->expects($this->once())
            ->method('getBody')
            ->willReturn($rBody);

        $rBody->expects($this->once())
            ->method('getContents')
            ->willReturn(json_encode($responseBody, JSON_THROW_ON_ERROR));

        $client = new BinPackingClient(
            $this->httpClient,
            $this->username,
            $this->apiKey,
        );

        $this->expectException(BinPackingClientException::class);
        $client->getMinimalPackaging($this->getProducts(), $this->getPackagings());
    }

    /**
     * @return array[][]
     */
    private function getProductsArray(): array
    {
        return [
            [
                'id' => 1,
                'h' => 1.1,
                'w' => 2.2,
                'd' => 3.3,
                'wg' => 4,
                'vr' => 1,
                'q' => 1,
            ],
        ];
    }

    /**
     * @return array[][]
     */
    private function getPackagingsArray(): array
    {
        return [
            [
                'id' => 1,
                'h' => 10,
                'w' => 10,
                'd' => 10,
                'max_wg' => 10,
            ],
        ];
    }
}
