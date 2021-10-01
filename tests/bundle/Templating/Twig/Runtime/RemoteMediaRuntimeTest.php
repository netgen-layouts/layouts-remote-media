<?php

declare(strict_types=1);

namespace Netgen\Bundle\LayoutsRemoteMediaBundle\Tests\Templating\Twig\Runtime;

use Netgen\Bundle\LayoutsRemoteMediaBundle\Templating\Twig\Runtime\RemoteMediaRuntime;
use Netgen\RemoteMedia\API\Values\RemoteResource;
use Netgen\RemoteMedia\API\Values\Variation;
use Netgen\RemoteMedia\Core\RemoteMediaProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class RemoteMediaRuntimeTest extends TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject&\Netgen\RemoteMedia\Core\RemoteMediaProvider
     */
    private MockObject $providerMock;

    private RemoteMediaRuntime $runtime;

    protected function setUp(): void
    {
        $this->providerMock = $this->createMock(RemoteMediaProvider::class);

        $this->runtime = new RemoteMediaRuntime(
            $this->providerMock,
        );
    }

    /**
     * @covers \Netgen\Bundle\LayoutsRemoteMediaBundle\Templating\Twig\Runtime\RemoteMediaRuntime::__construct
     * @covers \Netgen\Bundle\LayoutsRemoteMediaBundle\Templating\Twig\Runtime\RemoteMediaRuntime::getBlockVariation
     */
    public function testGetBlockVariation(): void
    {
        $resource = RemoteResource::createFromParameters(['resourceId' => 'test_image']);
        $variationUrl = 'https://cloudinary.com/upload/some_variation_config/test_image';
        $variation = new Variation([
            'url' => $variationUrl,
        ]);

        $this->providerMock
            ->expects(self::once())
            ->method('buildVariation')
            ->with($resource, 'netgen_layouts_block', 'test_variation', true)
            ->willReturn($variation);

        self::assertSame(
            $variationUrl,
            $this->runtime->getBlockVariation($resource, 'test_variation')->url,
        );
    }

    /**
     * @covers \Netgen\Bundle\LayoutsRemoteMediaBundle\Templating\Twig\Runtime\RemoteMediaRuntime::__construct
     * @covers \Netgen\Bundle\LayoutsRemoteMediaBundle\Templating\Twig\Runtime\RemoteMediaRuntime::getItemVariation
     */
    public function testGetItemVariation(): void
    {
        $resource = RemoteResource::createFromParameters(['resourceId' => 'test_image']);
        $variationUrl = 'https://cloudinary.com/upload/some_variation_config/test_image';
        $variation = new Variation([
            'url' => $variationUrl,
        ]);

        $this->providerMock
            ->expects(self::once())
            ->method('buildVariation')
            ->with($resource, 'netgen_layouts_item', 'test_variation', true)
            ->willReturn($variation);

        self::assertSame(
            $variationUrl,
            $this->runtime->getBlockVariation($resource, 'test_variation')->url,
        );
    }

    /**
     * @covers \Netgen\Bundle\LayoutsRemoteMediaBundle\Templating\Twig\Runtime\RemoteMediaRuntime::__construct
     * @covers \Netgen\Bundle\LayoutsRemoteMediaBundle\Templating\Twig\Runtime\RemoteMediaRuntime::getBlockVideoTag
     */
    public function testGetBlockVideoTag(): void
    {
        $resource = RemoteResource::createFromParameters([
            'resourceId' => 'test_video',
            'resourceType' => 'video',
        ]);

        $videoTagString = '<video src="https://cloudinary.com/upload/some_variation_config/test_video">';

        $this->providerMock
            ->expects(self::once())
            ->method('generateVideoTag')
            ->with($resource, 'netgen_layouts_block', 'test_variation')
            ->willReturn($videoTagString);

        self::assertSame(
            $videoTagString,
            $this->runtime->getBlockVideoTag($resource, 'test_variation'),
        );
    }
}
