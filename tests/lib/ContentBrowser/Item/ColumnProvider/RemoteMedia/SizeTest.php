<?php

declare(strict_types=1);

namespace Netgen\Layouts\RemoteMedia\Tests\ContentBrowser\Item\ColumnProvider\RemoteMedia;

use Netgen\Layouts\RemoteMedia\ContentBrowser\Item\ColumnProvider\RemoteMedia\Size;
use Netgen\Layouts\RemoteMedia\ContentBrowser\Item\RemoteMedia\Item as RemoteMediaItem;
use Netgen\RemoteMedia\API\Values\RemoteResource;
use PHPUnit\Framework\TestCase;

final class SizeTest extends TestCase
{
    private Size $sizeColumn;

    protected function setUp(): void
    {
        $this->sizeColumn = new Size();
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Item\ColumnProvider\RemoteMedia\Size::getValue
     */
    public function testGetValueInB(): void
    {
        $resource = RemoteResource::createFromParameters(['resourceId' => 'folder/test_resource']);
        $resource->size = 586;

        $item = new RemoteMediaItem($resource);

        self::assertSame('586B', $this->sizeColumn->getValue($item));
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Item\ColumnProvider\RemoteMedia\Size::getValue
     */
    public function testGetValueInkB(): void
    {
        $resource = RemoteResource::createFromParameters(['resourceId' => 'folder/test_resource']);
        $resource->size = 1086;

        $item = new RemoteMediaItem($resource);

        self::assertSame('1.06kB', $this->sizeColumn->getValue($item));
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Item\ColumnProvider\RemoteMedia\Size::getValue
     */
    public function testGetValueInMB(): void
    {
        $resource = RemoteResource::createFromParameters(['resourceId' => 'folder/test_resource']);
        $resource->size = 269840548;

        $item = new RemoteMediaItem($resource);

        self::assertSame('257.34MB', $this->sizeColumn->getValue($item));
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Item\ColumnProvider\RemoteMedia\Size::getValue
     */
    public function testGetValueInGB(): void
    {
        $resource = RemoteResource::createFromParameters(['resourceId' => 'folder/test_resource']);
        $resource->size = 269840548462;

        $item = new RemoteMediaItem($resource);

        self::assertSame('251.31GB', $this->sizeColumn->getValue($item));
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Item\ColumnProvider\RemoteMedia\Size::getValue
     */
    public function testGetValueInTB(): void
    {
        $resource = RemoteResource::createFromParameters(['resourceId' => 'folder/test_resource']);
        $resource->size = 269840548462634;

        $item = new RemoteMediaItem($resource);

        self::assertSame('245.42TB', $this->sizeColumn->getValue($item));
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Item\ColumnProvider\RemoteMedia\Size::getValue
     */
    public function testGetValueInPB(): void
    {
        $resource = RemoteResource::createFromParameters(['resourceId' => 'folder/test_resource']);
        $resource->size = 269840548462634154;

        $item = new RemoteMediaItem($resource);

        self::assertSame('239.67PB', $this->sizeColumn->getValue($item));
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Item\ColumnProvider\RemoteMedia\Size::getValue
     */
    public function testGetValueWithNoSize(): void
    {
        $resource = RemoteResource::createFromParameters(['resourceId' => 'folder/test_resource']);
        $item = new RemoteMediaItem($resource);

        self::assertSame('0B', $this->sizeColumn->getValue($item));
    }
}
