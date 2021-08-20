<?php

declare(strict_types=1);

namespace Netgen\Layouts\RemoteMedia\Tests\Block\BlockDefinition\Handler;

use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\VariationResolver;
use Netgen\Layouts\API\Values\Block\Block;
use Netgen\Layouts\Block\DynamicParameters;
use Netgen\Layouts\Item\ValueLoaderInterface;
use Netgen\Layouts\Parameters\Parameter;
use Netgen\Layouts\Parameters\ParameterBuilderInterface;
use Netgen\Layouts\Parameters\ParameterType\ChoiceType;
use Netgen\Layouts\Parameters\ParameterType\TextLineType;
use Netgen\Layouts\RemoteMedia\Block\BlockDefinition\Handler\RemoteMediaHandler;
use Netgen\Layouts\RemoteMedia\Parameters\ParameterType\RemoteMediaType;
use Netgen\Layouts\RemoteMedia\Tests\Stubs\RemoteMedia as RemoteMediaStub;
use PHPUnit\Framework\TestCase;

final class RemoteMediaHandlerTest extends TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject&\Netgen\Layouts\Item\ValueLoaderInterface
     */
    private $valueLoaderMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject&\Netgen\Bundle\RemoteMediaBundle\RemoteMedia\VariationResolver
     */
    private $variationResolverMock;

    /**
     * @var array<string>
     */
    private $allowedResourceTypes;

    /**
     * @var \Netgen\Layouts\RemoteMedia\Block\BlockDefinition\Handler\RemoteMediaHandler
     */
    private $handler;

    protected function setUp(): void
    {
        $this->valueLoaderMock = $this->createMock(ValueLoaderInterface::class);
        $this->variationResolverMock = $this->createMock(VariationResolver::class);
        $this->allowedResourceTypes = ['image', 'video'];

        $this->handler = new RemoteMediaHandler(
            $this->valueLoaderMock,
            $this->variationResolverMock,
            $this->allowedResourceTypes,
        );
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\Block\BlockDefinition\Handler\RemoteMediaHandler::isContextual
     */
    public function testIsContextual(): void
    {
        self::assertFalse($this->handler->isContextual(new Block()));
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\Block\BlockDefinition\Handler\RemoteMediaHandler::__construct
     * @covers \Netgen\Layouts\RemoteMedia\Block\BlockDefinition\Handler\RemoteMediaHandler::buildParameters
     * @covers \Netgen\Layouts\RemoteMedia\Block\BlockDefinition\Handler\RemoteMediaHandler::getVariationOptions
     */
    public function testBuildParameters(): void
    {
        $variations = [
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
        ];

        $this->variationResolverMock
            ->expects(self::once())
            ->method('getVariationsForContentType')
            ->with('netgen_layouts_block')
            ->willReturn($variations);

        $builderMock = $this->createMock(ParameterBuilderInterface::class);

        $variationOptions = [
            '(no variation)' => null,
            'Small' => 'Small',
            'Big' => 'Big',
        ];

        $builderMock
            ->expects(self::exactly(3))
            ->method('add')
            ->withConsecutive(
                ['remote_media', RemoteMediaType::class, ['required' => false, 'allowed_types' => $this->allowedResourceTypes]],
                ['variation', ChoiceType::class, ['required' => false, 'options' => $variationOptions]],
                ['title', TextLineType::class],
            );

        $this->handler->buildParameters($builderMock);
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\Block\BlockDefinition\Handler\RemoteMediaHandler::__construct
     * @covers \Netgen\Layouts\RemoteMedia\Block\BlockDefinition\Handler\RemoteMediaHandler::getDynamicParameters
     */
    public function testGetDynamicSettings(): void
    {
        $params = new DynamicParameters();

        $block = Block::fromArray([
            'parameters' => [
                'remote_media' => Parameter::fromArray([
                    'name' => 'remote_media',
                    'value' => 'image|folder|subfolder|image_name.jpg',
                    'isEmpty' => false,
                ]),
                'variation' => Parameter::fromArray([
                    'name' => 'variation',
                    'value' => null,
                    'isEmpty' => true,
                ]),
                'title' => Parameter::fromArray([
                    'name' => 'title',
                    'value' => 'Test title',
                    'isEmpty' => false,
                ]),
            ],
        ]);

        $value = new RemoteMediaStub('folder/subfolder/image_name.jpg', 'image');

        $this->valueLoaderMock
            ->expects(self::once())
            ->method('load')
            ->with('image|folder|subfolder|image_name.jpg')
            ->willReturn($value);

        $this->handler->getDynamicParameters($params, $block);

        self::assertSame($value->resourceType, $params['resource']->resourceType);
        self::assertSame($value->type, $params['resource']->type);
        self::assertSame($value->url, $params['resource']->url);
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\Block\BlockDefinition\Handler\RemoteMediaHandler::__construct
     * @covers \Netgen\Layouts\RemoteMedia\Block\BlockDefinition\Handler\RemoteMediaHandler::getDynamicParameters
     */
    public function testGetDynamicSettingsEmpty(): void
    {
        $params = new DynamicParameters();
        $block = Block::fromArray([
            'parameters' => [
                'remote_media' => Parameter::fromArray([
                    'name' => 'remote_media',
                    'value' => null,
                    'isEmpty' => true,
                ]),
                'variation' => Parameter::fromArray([
                    'name' => 'variation',
                    'value' => null,
                    'isEmpty' => true,
                ]),
                'title' => Parameter::fromArray([
                    'name' => 'title',
                    'value' => 'Test title',
                    'isEmpty' => false,
                ]),
            ],
        ]);

        $this->valueLoaderMock
            ->expects(self::never())
            ->method('load');

        $this->handler->getDynamicParameters($params, $block);

        self::assertNull($params['resource']);
    }
}
