<?php

declare(strict_types=1);

namespace Netgen\Layouts\RemoteMedia\Tests\ContentBrowser\Item\ColumnProvider\RemoteMedia;

use Netgen\Layouts\RemoteMedia\ContentBrowser\Item\ColumnProvider\RemoteMedia\Resolution;
use Netgen\Layouts\RemoteMedia\ContentBrowser\Item\RemoteMedia\Item as RemoteMediaItem;
use Netgen\Layouts\RemoteMedia\Tests\Stubs\RemoteMedia as RemoteMediaStub;
use PHPUnit\Framework\TestCase;

final class ResolutionTest extends TestCase
{
    /**
     * @var \Netgen\Layouts\RemoteMedia\ContentBrowser\Item\ColumnProvider\RemoteMedia\Resolution
     */
    private $resolutionColumn;

    protected function setUp(): void
    {
        $this->resolutionColumn = new Resolution();
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Item\ColumnProvider\RemoteMedia\Resolution::getValue
     */
    public function testGetValue(): void
    {
        $resource = new RemoteMediaStub('folder/test_resource');
        $resource->metaData['width'] = 1920;
        $resource->metaData['height'] = 1080;

        $item = new RemoteMediaItem($resource);

        self::assertSame('1920x1080', $this->resolutionColumn->getValue($item));
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Item\ColumnProvider\RemoteMedia\Resolution::getValue
     */
    public function testGetValueWithEmptyWidth(): void
    {
        $resource = new RemoteMediaStub('folder/test_resource');
        $resource->metaData['width'] = 1920;

        $item = new RemoteMediaItem($resource);

        self::assertSame('', $this->resolutionColumn->getValue($item));
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Item\ColumnProvider\RemoteMedia\Resolution::getValue
     */
    public function testGetValueWithEmptyHeight(): void
    {
        $resource = new RemoteMediaStub('folder/test_resource');
        $resource->metaData['height'] = 1080;

        $item = new RemoteMediaItem($resource);

        self::assertSame('', $this->resolutionColumn->getValue($item));
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Item\ColumnProvider\RemoteMedia\Resolution::getValue
     */
    public function testGetValueWithMissingKeys(): void
    {
        $resource = new RemoteMediaStub('folder/test_resource');
        unset($resource->metaData['width'], $resource->metaData['height']);

        $item = new RemoteMediaItem($resource);

        self::assertSame('', $this->resolutionColumn->getValue($item));
    }
}
