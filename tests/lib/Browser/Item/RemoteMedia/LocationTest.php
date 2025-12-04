<?php

declare(strict_types=1);

namespace Netgen\Layouts\RemoteMedia\Tests\Browser\Item\RemoteMedia;

use InvalidArgumentException;
use Netgen\Layouts\RemoteMedia\Browser\Item\RemoteMedia\Location;
use Netgen\RemoteMedia\API\Values\Folder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Location::class)]
final class LocationTest extends TestCase
{
    private Location $sectionLocation;

    private Location $folderLocation;

    private Location $location;

    protected function setUp(): void
    {
        $this->sectionLocation = Location::createAsSection('all', 'All items');
        $this->folderLocation = Location::createFromFolder(Folder::fromPath('some/folder/path'), 'image');
        $this->location = Location::createFromId('video||some|folder|path');
    }

    public function testGetLocationId(): void
    {
        self::assertSame('all', $this->sectionLocation->locationId);
        self::assertSame('image||some|folder|path', $this->folderLocation->locationId);
        self::assertSame('video||some|folder|path', $this->location->locationId);
    }

    public function testGetName(): void
    {
        self::assertSame('All items', $this->sectionLocation->name);
        self::assertSame('path', $this->folderLocation->name);
        self::assertSame('path', $this->location->name);
    }

    public function testGetNameWithSectionWithoutName(): void
    {
        self::assertSame('image', Location::createAsSection('image')->name);
    }

    public function testGetParentId(): void
    {
        self::assertNull($this->sectionLocation->parentId);
        self::assertSame('image||some|folder', $this->folderLocation->parentId);
        self::assertSame('video||some|folder', $this->location->parentId);
    }

    public function testGetParentIdWithSingleFolder(): void
    {
        self::assertSame('image', Location::createFromFolder(Folder::fromPath('some'), 'image')->parentId);
    }

    public function testGetFolder(): void
    {
        self::assertNull($this->sectionLocation->folder);

        self::assertInstanceOf(Folder::class, $this->folderLocation->folder);
        self::assertSame('some/folder/path', $this->folderLocation->folder->getPath());

        self::assertInstanceOf(Folder::class, $this->location->folder);
        self::assertSame('some/folder/path', $this->location->folder->getPath());
    }

    public function testGetType(): void
    {
        self::assertSame('all', $this->sectionLocation->type);
        self::assertSame('image', $this->folderLocation->type);
        self::assertSame('video', $this->location->type);
    }

    public function testCreateAsSectionWithFolder(): void
    {
        $location = Location::createAsSection('image', 'Images', 'images/layouts');

        self::assertSame('image||images|layouts', $location->getLocationId());
        self::assertSame('Images', $location->getName());
        self::assertSame('image', $location->getType());
        self::assertInstanceOf(Folder::class, $location->getFolder());
        self::assertSame('images/layouts', $location->getFolder()->getPath());
    }

    public function testFromIdWithInvalidResourceType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Provided ID unsupported_resource_type||some|folder|path is invalid');

        Location::createFromId('unsupported_resource_type||some|folder|path');
    }

    public function testFromFolderWithInvalidResourceType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Provided ID unsupported_resource_type||test|subtest is invalid');

        Location::createFromFolder(Folder::fromPath('test/subtest'), 'unsupported_resource_type');
    }

    public function testAsSectionWithInvalidResourceType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Provided ID unsupported_resource_type is invalid');

        Location::createAsSection('unsupported_resource_type', 'Unsupported resource type');
    }

    public function testFromFolderWithDefaultType(): void
    {
        $location = Location::createFromFolder(Folder::fromPath('test/subtest'));

        self::assertSame(Location::RESOURCE_TYPE_ALL, $location->type);
        self::assertInstanceOf(Folder::class, $location->folder);
        self::assertSame('test/subtest', $location->folder->getPath());
        self::assertSame('all||test', $location->parentId);
    }
}
