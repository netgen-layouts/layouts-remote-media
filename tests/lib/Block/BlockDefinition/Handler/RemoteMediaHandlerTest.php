<?php

declare(strict_types=1);

namespace Netgen\Layouts\RemoteMedia\Tests\Block\BlockDefinition\Handler;

use Netgen\Layouts\API\Values\Block\Block;
use Netgen\Layouts\Block\DynamicParameters;
use Netgen\Layouts\Item\ValueLoaderInterface;
use Netgen\Layouts\Parameters\Parameter;
use Netgen\Layouts\Parameters\ParameterList;
use Netgen\Layouts\RemoteMedia\Block\BlockDefinition\Handler\RemoteMediaHandler;
use Netgen\RemoteMedia\API\Values\RemoteResource;
use Netgen\RemoteMedia\Core\Resolver\Variation as VariationResolver;
use Netgen\RemoteMedia\Core\Transformation\Registry;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

#[CoversClass(RemoteMediaHandler::class)]
final class RemoteMediaHandlerTest extends TestCase
{
    private Stub&ValueLoaderInterface $valueLoaderStub;

    private RemoteMediaHandler $handler;

    protected function setUp(): void
    {
        $this->valueLoaderStub = self::createStub(ValueLoaderInterface::class);

        $variationResolver = new VariationResolver(
            new Registry(),
            new NullLogger(),
            [
                'netgen_layouts_block' => [
                    'Small' => [
                        'transformations' => [
                            'limit' => [300],
                        ],
                    ],
                    'Big' => [
                        'transformations' => [
                            'limit' => [1200],
                        ],
                    ],
                ],
            ],
        );

        $this->handler = new RemoteMediaHandler(
            $this->valueLoaderStub,
            $variationResolver,
            ['image', 'video'],
        );
    }

    public function testIsContextual(): void
    {
        self::assertFalse($this->handler->isContextual(new Block()));
    }

    public function testGetDynamicSettings(): void
    {
        $params = new DynamicParameters();

        $block = Block::fromArray(
            [
                'parameters' => new ParameterList(
                    [
                        'remote_media' => Parameter::fromArray(
                            [
                                'name' => 'remote_media',
                                'value' => 'image|folder|subfolder|image_name.jpg',
                                'isEmpty' => false,
                            ],
                        ),
                        'variation' => Parameter::fromArray(
                            [
                                'name' => 'variation',
                                'value' => null,
                                'isEmpty' => true,
                            ],
                        ),
                        'title' => Parameter::fromArray(
                            [
                                'name' => 'title',
                                'value' => 'Test title',
                                'isEmpty' => false,
                            ],
                        ),
                    ],
                ),
            ],
        );

        $value = new RemoteResource(
            remoteId: 'folder/subfolder/image_name.jpg',
            type: RemoteResource::TYPE_IMAGE,
            url: 'https://cloudinary.com/test/upload/image/folder/subfolder/image_name.jpg',
            md5: '185901e0a6f0c338cc4115a8b1923f44',
        );

        $this->valueLoaderStub
            ->method('load')
            ->with('image|folder|subfolder|image_name.jpg')
            ->willReturn($value);

        $this->handler->getDynamicParameters($params, $block);

        self::assertSame($value->getRemoteId(), $params['remote_resource_location']->getRemoteId());
        self::assertSame($value->getType(), $params['remote_resource_location']->getType());
        self::assertSame($value->getUrl(), $params['remote_resource_location']->getUrl());
    }

    public function testGetDynamicSettingsEmpty(): void
    {
        $params = new DynamicParameters();
        $block = Block::fromArray(
            [
                'parameters' => new ParameterList(
                    [
                        'remote_media' => Parameter::fromArray(
                            [
                                'name' => 'remote_media',
                                'value' => null,
                                'isEmpty' => true,
                            ],
                        ),
                        'variation' => Parameter::fromArray(
                            [
                                'name' => 'variation',
                                'value' => null,
                                'isEmpty' => true,
                            ],
                        ),
                        'title' => Parameter::fromArray(
                            [
                                'name' => 'title',
                                'value' => 'Test title',
                                'isEmpty' => false,
                            ],
                        ),
                    ],
                ),
            ],
        );

        $this->handler->getDynamicParameters($params, $block);

        self::assertNull($params['remote_resource_location']);
    }
}
