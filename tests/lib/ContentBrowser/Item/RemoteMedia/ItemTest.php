<?php

declare(strict_types=1);

namespace Netgen\Layouts\RemoteMedia\Tests\ContentBrowser\Item\RemoteMedia;

use Netgen\Layouts\RemoteMedia\ContentBrowser\Item\RemoteMedia\Item;
use Netgen\Layouts\RemoteMedia\Tests\Stubs\RemoteMedia as RemoteMediaStub;
use PHPUnit\Framework\TestCase;

final class ItemTest extends TestCase
{
    /**
     * @var \Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Value
     */
    private $resource;

    /**
     * @var \Netgen\Layouts\RemoteMedia\ContentBrowser\Item\RemoteMedia\Item
     */
    private $item;

    protected function setUp(): void
    {
        $this->resource = new RemoteMediaStub('folder/test_resource');

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
