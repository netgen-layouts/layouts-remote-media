<?php

declare(strict_types=1);

namespace Netgen\Layouts\RemoteMedia\Tests\ContentBrowser\Item\RemoteMedia;

use Netgen\Layouts\RemoteMedia\ContentBrowser\Item\RemoteMedia\Item;
use Netgen\Layouts\Tests\Core\Service\TransactionRollback\TestCase;
use Netgen\RemoteMedia\API\Values\Folder;
use Netgen\RemoteMedia\API\Values\RemoteResource;
use Netgen\RemoteMedia\API\Values\RemoteResourceLocation;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Item::class)]
final class ItemTest extends TestCase
{
    private RemoteResourceLocation $location;

    private Item $item;

    protected function setUp(): void
    {
        $this->location = new RemoteResourceLocation(
            new RemoteResource(
                remoteId: 'upload|image|folder/test_resource',
                type: RemoteResource::TYPE_IMAGE,
                url: 'https://cloudinary.com/test/upload/image/folder/test_resource',
                md5: 'db4133438a186b6895d8e0fc0f253302',
                name: 'test_resource',
                folder: Folder::fromPath('folder'),
            ),
        );

        $this->item = new Item($this->location);

        parent::setUp();
    }

    public function testGetValue(): void
    {
        self::assertSame('upload||image||folder|test_resource', $this->item->getValue());
    }

    public function testGetName(): void
    {
        self::assertSame('test_resource', $this->item->getName());
    }

    public function testIsVisible(): void
    {
        self::assertTrue($this->item->isVisible());
    }

    public function testIsSelectable(): void
    {
        self::assertTrue($this->item->isSelectable());
    }

    public function testGetType(): void
    {
        self::assertSame('image', $this->item->getType());
    }

    public function testGetRemoteResource(): void
    {
        self::assertSame(
            $this->location->getRemoteResource()->getRemoteId(),
            $this->item->getRemoteResourceLocation()->getRemoteResource()->getRemoteId(),
        );

        self::assertSame(
            $this->location->getRemoteResource()->getType(),
            $this->item->getRemoteResourceLocation()->getRemoteResource()->getType(),
        );

        self::assertSame(
            $this->location->getRemoteResource()->getUrl(),
            $this->item->getRemoteResourceLocation()->getRemoteResource()->getUrl(),
        );

        $folder = $this->location->getRemoteResource()->getFolder();

        self::assertInstanceOf(Folder::class, $folder);

        self::assertInstanceOf(
            Folder::class,
            $this->item->getRemoteResourceLocation()->getRemoteResource()->getFolder(),
        );

        self::assertSame(
            $folder->getPath(),
            $this->item->getRemoteResourceLocation()->getRemoteResource()->getFolder()->getPath(),
        );
    }
}
