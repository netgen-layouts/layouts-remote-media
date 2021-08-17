<?php

declare(strict_types=1);

namespace Netgen\Bundle\LayoutsRemoteMediaBundle\Tests\Templating\Twig\Runtime;

use Netgen\Bundle\LayoutsRemoteMediaBundle\Templating\Twig\Runtime\RemoteMediaRuntime;
use Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Variation;
use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\RemoteMediaProvider;
use Netgen\Layouts\RemoteMedia\Tests\Stubs\RemoteMedia as RemoteMediaStub;
use PHPUnit\Framework\TestCase;

final class RemoteMediaRuntimeTest extends TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject&\Netgen\Bundle\RemoteMediaBundle\RemoteMedia\RemoteMediaProvider
     */
    private $providerMock;

    /**
     * @var \Netgen\Bundle\LayoutsRemoteMediaBundle\Templating\Twig\Runtime\RemoteMediaRuntime
     */
    private $runtime;

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
        $value = new RemoteMediaStub('test_image');
        $variationUrl = 'https://cloudinary.com/upload/some_variation_config/test_image';
        $variation = new Variation([
            'url' => $variationUrl,
        ]);

        $this->providerMock
            ->expects(self::once())
            ->method('buildVariation')
            ->with($value, 'layouts_block', 'test_variation', true)
            ->willReturn($variation);

        $this->assertEquals(
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
        $value = new RemoteMediaStub('test_video');
        $videoTagString = '<video src="https://cloudinary.com/upload/some_variation_config/test_video">';

        $this->providerMock
            ->expects(self::once())
            ->method('generateVideoTag')
            ->with($value, 'layouts_block', 'test_variation')
            ->willReturn($videoTagString);

        $this->assertEquals(
            $videoTagString,
            $this->runtime->getRemoteVideoTagEmbed($value, 'test_variation'),
        );
    }
}
