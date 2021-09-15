<?php

declare(strict_types=1);

namespace Netgen\Layouts\RemoteMedia\Tests\ContentBrowser\Item\ColumnProvider\RemoteMedia;

use Netgen\Layouts\RemoteMedia\ContentBrowser\Item\ColumnProvider\RemoteMedia\Resolution;
use Netgen\Layouts\RemoteMedia\ContentBrowser\Item\RemoteMedia\Item as RemoteMediaItem;
use Netgen\RemoteMedia\API\Values\RemoteResource;
use PHPUnit\Framework\TestCase;

final class ResolutionTest extends TestCase
{
    private Resolution $resolutionColumn;

    protected function setUp(): void
    {
        $this->resolutionColumn = new Resolution();
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Item\ColumnProvider\RemoteMedia\Resolution::getValue
     */
    public function testGetValue(): void
    {
        $resource = RemoteResource::createFromParameters(['resourceId' => 'folder/test_resource']);
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
        $resource = RemoteResource::createFromParameters(['resourceId' => 'folder/test_resource']);
        $resource->metaData['width'] = 1920;

        $item = new RemoteMediaItem($resource);

        self::assertSame('', $this->resolutionColumn->getValue($item));
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Item\ColumnProvider\RemoteMedia\Resolution::getValue
     */
    public function testGetValueWithEmptyHeight(): void
    {
        $resource = RemoteResource::createFromParameters(['resourceId' => 'folder/test_resource']);
        $resource->metaData['height'] = 1080;

        $item = new RemoteMediaItem($resource);

        self::assertSame('', $this->resolutionColumn->getValue($item));
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Item\ColumnProvider\RemoteMedia\Resolution::getValue
     */
    public function testGetValueWithMissingKeys(): void
    {
        $resource = RemoteResource::createFromParameters(['resourceId' => 'folder/test_resource']);
        unset($resource->metaData['width'], $resource->metaData['height']);

        $item = new RemoteMediaItem($resource);

        self::assertSame('', $this->resolutionColumn->getValue($item));
    }
}
