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
        $value = RemoteResource::createFromParameters(['resourceId' => 'test_image']);
        $variationUrl = 'https://cloudinary.com/upload/some_variation_config/test_image';
        $variation = new Variation([
            'url' => $variationUrl,
        ]);

        $this->providerMock
            ->expects(self::once())
            ->method('buildVariation')
            ->with($value, 'netgen_layouts_block', 'test_variation', true)
            ->willReturn($variation);

        self::assertSame(
            $variationUrl,
            $this->runtime->getBlockVariation($value, 'test_variation')->url,
        );
    }

    /**
     * @covers \Netgen\Bundle\LayoutsRemoteMediaBundle\Templating\Twig\Runtime\RemoteMediaRuntime::__construct
     * @covers \Netgen\Bundle\LayoutsRemoteMediaBundle\Templating\Twig\Runtime\RemoteMediaRuntime::getRemoteVideoTagEmbed
     */
    public function testGetRemoteVideoTagEmbed(): void
    {
        $value = RemoteResource::createFromParameters(['resourceId' => 'test_video']);
        $videoTagString = '<video src="https://cloudinary.com/upload/some_variation_config/test_video">';

        $this->providerMock
            ->expects(self::once())
            ->method('generateVideoTag')
            ->with($value, 'netgen_layouts_block', 'test_variation')
            ->willReturn($videoTagString);

        self::assertSame(
            $videoTagString,
            $this->runtime->getRemoteVideoTagEmbed($value, 'test_variation'),
        );
    }
}
