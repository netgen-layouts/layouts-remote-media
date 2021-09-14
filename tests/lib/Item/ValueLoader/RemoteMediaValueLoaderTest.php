<?php

declare(strict_types=1);

namespace Netgen\Layouts\RemoteMedia\Tests\Item\ValueLoader;

use Cloudinary\Api\NotFound as CloudinaryNotFoundException;
use Netgen\Layouts\RemoteMedia\Item\ValueLoader\RemoteMediaValueLoader;
use Netgen\Layouts\RemoteMedia\Tests\Stubs\RemoteMedia as RemoteMediaStub;
use Netgen\RemoteMedia\Core\RemoteMediaProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class RemoteMediaValueLoaderTest extends TestCase
{
    private MockObject $providerMock;

    private RemoteMediaValueLoader $valueLoader;

    protected function setUp(): void
    {
        $this->providerMock = $this->createMock(RemoteMediaProvider::class);

        $this->valueLoader = new RemoteMediaValueLoader($this->providerMock);
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\Item\ValueLoader\RemoteMediaValueLoader::__construct
     * @covers \Netgen\Layouts\RemoteMedia\Item\ValueLoader\RemoteMediaValueLoader::load
     */
    public function testLoad(): void
    {
        $resource = new RemoteMediaStub('folder/test_resource', 'video');

        $this->providerMock
            ->expects(self::once())
            ->method('getRemoteResource')
            ->with('folder/test_resource', 'video')
            ->willReturn($resource);

        self::assertSame($resource, $this->valueLoader->load('video|folder|test_resource'));
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\Item\ValueLoader\RemoteMediaValueLoader::load
     */
    public function testLoadNotFound(): void
    {
        $this->providerMock
            ->expects(self::once())
            ->method('getRemoteResource')
            ->with('folder/test_resource', 'video')
            ->willThrowException(new CloudinaryNotFoundException());

        self::assertNull($this->valueLoader->load('video|folder|test_resource'));
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\Item\ValueLoader\RemoteMediaValueLoader::loadByRemoteId
     */
    public function testLoadByRemoteId(): void
    {
        $resource = new RemoteMediaStub('folder/test_resource', 'video');

        $this->providerMock
            ->expects(self::once())
            ->method('getRemoteResource')
            ->with('folder/test_resource', 'video')
            ->willReturn($resource);

        self::assertSame($resource, $this->valueLoader->loadByRemoteId('video|folder|test_resource'));
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\Item\ValueLoader\RemoteMediaValueLoader::loadByRemoteId
     */
    public function testLoadByRemoteIdNotFound(): void
    {
        $this->providerMock
            ->expects(self::once())
            ->method('getRemoteResource')
            ->with('folder/test_resource', 'video')
            ->willThrowException(new CloudinaryNotFoundException());

        self::assertNull($this->valueLoader->loadByRemoteId('video|folder|test_resource'));
    }
}
