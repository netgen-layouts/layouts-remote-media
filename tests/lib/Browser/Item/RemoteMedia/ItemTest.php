<?php

declare(strict_types=1);

namespace Netgen\Layouts\RemoteMedia\Tests\Browser\Item\RemoteMedia;

use Netgen\Layouts\RemoteMedia\Browser\Item\RemoteMedia\Item;
use Netgen\RemoteMedia\API\Values\Folder;
use Netgen\RemoteMedia\API\Values\RemoteResource;
use Netgen\RemoteMedia\API\Values\RemoteResourceLocation;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

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
        self::assertSame('upload||image||folder|test_resource', $this->item->value);
    }

    public function testGetName(): void
    {
        self::assertSame('test_resource', $this->item->name);
    }

    public function testGetType(): void
    {
        self::assertSame('image', $this->item->type);
    }

    public function testGetRemoteResource(): void
    {
        self::assertSame(
            $this->location->getRemoteResource()->getRemoteId(),
            $this->item->remoteResourceLocation->getRemoteResource()->getRemoteId(),
        );

        self::assertSame(
            $this->location->getRemoteResource()->getType(),
            $this->item->remoteResourceLocation->getRemoteResource()->getType(),
        );

        self::assertSame(
            $this->location->getRemoteResource()->getUrl(),
            $this->item->remoteResourceLocation->getRemoteResource()->getUrl(),
        );

        $folder = $this->location->getRemoteResource()->getFolder();

        self::assertInstanceOf(Folder::class, $folder);

        self::assertInstanceOf(
            Folder::class,
            $this->item->remoteResourceLocation->getRemoteResource()->getFolder(),
        );

        self::assertSame(
            $folder->getPath(),
            $this->item->remoteResourceLocation->getRemoteResource()->getFolder()->getPath(),
        );
    }
}
