<?php

declare(strict_types=1);

namespace Netgen\Layouts\RemoteMedia\Tests\ContentBrowser\Item\RemoteMedia;

use InvalidArgumentException;
use Netgen\Layouts\RemoteMedia\ContentBrowser\Item\RemoteMedia\Location;
use PHPUnit\Framework\TestCase;

final class LocationTest extends TestCase
{
    /**
     * @var \Netgen\Layouts\RemoteMedia\ContentBrowser\Item\RemoteMedia\Location
     */
    private $sectionLocation;

    /**
     * @var \Netgen\Layouts\RemoteMedia\ContentBrowser\Item\RemoteMedia\Location
     */
    private $folderLocation;

    /**
     * @var \Netgen\Layouts\RemoteMedia\ContentBrowser\Item\RemoteMedia\Location
     */
    private $location;

    protected function setUp(): void
    {
        $this->sectionLocation = Location::createAsSection('all', 'All items');
        $this->folderLocation = Location::createFromFolder('some/folder/path', 'path', 'image');
        $this->location = Location::createFromId('video|some|folder|path');
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Item\RemoteMedia\Location::__construct
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Item\RemoteMedia\Location::createAsSection
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Item\RemoteMedia\Location::createFromFolder
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Item\RemoteMedia\Location::createFromId
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Item\RemoteMedia\Location::getLocationId
     */
    public function testGetLocationId(): void
    {
        self::assertSame('all', $this->sectionLocation->getLocationId());
        self::assertSame('image|some|folder|path', $this->folderLocation->getLocationId());
        self::assertSame('video|some|folder|path', $this->location->getLocationId());
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Item\RemoteMedia\Location::getName
     */
    public function testGetName(): void
    {
        self::assertSame('All items', $this->sectionLocation->getName());
        self::assertSame('path', $this->folderLocation->getName());
        self::assertSame('path', $this->location->getName());
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Item\RemoteMedia\Location::getParentId
     */
    public function testGetParentId(): void
    {
        self::assertNull($this->sectionLocation->getParentId());
        self::assertSame('image|some|folder', $this->folderLocation->getParentId());
        self::assertSame('video|some|folder', $this->location->getParentId());
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Item\RemoteMedia\Location::getFolder
     */
    public function testGetFolder(): void
    {
        self::assertNull($this->sectionLocation->getFolder());
        self::assertSame('some/folder/path', $this->folderLocation->getFolder());
        self::assertSame('some/folder/path', $this->location->getFolder());
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Item\RemoteMedia\Location::getResourceType
     */
    public function testGetResourceType(): void
    {
        self::assertSame('all', $this->sectionLocation->getResourceType());
        self::assertSame('image', $this->folderLocation->getResourceType());
        self::assertSame('video', $this->location->getResourceType());
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Item\RemoteMedia\Location::createFromId
     */
    public function testFromIdWithInvalidResourceType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Provided ID unsupported_resource_type|some|folder|path is invalid');

        Location::createFromId('unsupported_resource_type|some|folder|path');
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Item\RemoteMedia\Location::createAsSection
     */
    public function testAsSectionWithInvalidResourceType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Provided resource type unsupported_resource_type is invalid');

        Location::createAsSection('unsupported_resource_type', 'Unsupported resource type');
    }
}
