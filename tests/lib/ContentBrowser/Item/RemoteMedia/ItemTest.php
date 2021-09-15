<?php

declare(strict_types=1);

namespace Netgen\Layouts\RemoteMedia\Tests\ContentBrowser\Item\RemoteMedia;

use Netgen\Layouts\RemoteMedia\ContentBrowser\Item\RemoteMedia\Item;
use Netgen\RemoteMedia\API\Values\RemoteResource;
use PHPUnit\Framework\TestCase;

final class ItemTest extends TestCase
{
    private RemoteResource $resource;

    private Item $item;

    protected function setUp(): void
    {
        $this->resource = RemoteResource::createFromParameters([
            'resourceId' => 'folder/test_resource',
            'resourceType' => 'image',
        ]);

        $this->item = new Item($this->resource);
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Item\RemoteMedia\Item::__construct
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Item\RemoteMedia\Item::getValue
     */
    public function testGetValue(): void
    {
        self::assertSame('image|folder|test_resource', $this->item->getValue());
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Item\RemoteMedia\Item::getName
     */
    public function testGetName(): void
    {
        self::assertSame('test_resource', $this->item->getName());
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Item\RemoteMedia\Item::isVisible
     */
    public function testIsVisible(): void
    {
        self::assertTrue($this->item->isVisible());
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Item\RemoteMedia\Item::isSelectable
     */
    public function testIsSelectable(): void
    {
        self::assertTrue($this->item->isSelectable());
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Item\RemoteMedia\Item::getResourceType
     */
    public function testGetResourceType(): void
    {
        self::assertSame('image', $this->item->getResourceType());
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Item\RemoteMedia\Item::getRemoteMediaValue
     */
    public function testGetRemoteMediaValue(): void
    {
        self::assertSame($this->resource, $this->item->getRemoteMediaValue());
    }
}
