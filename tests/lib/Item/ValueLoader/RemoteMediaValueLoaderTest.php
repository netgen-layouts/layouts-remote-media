<?php

declare(strict_types=1);

namespace Netgen\Layouts\RemoteMedia\Tests\Item\ValueLoader;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Netgen\Layouts\RemoteMedia\API\Values\RemoteMediaItem;
use Netgen\Layouts\RemoteMedia\Item\ValueLoader\RemoteMediaValueLoader;
use Netgen\RemoteMedia\API\ProviderInterface;
use Netgen\RemoteMedia\API\Values\Folder;
use Netgen\RemoteMedia\API\Values\RemoteResource;
use Netgen\RemoteMedia\API\Values\RemoteResourceLocation;
use Netgen\RemoteMedia\Exception\RemoteResourceNotFoundException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;

#[CoversClass(RemoteMediaValueLoader::class)]
final class RemoteMediaValueLoaderTest extends TestCase
{
    private Stub&ProviderInterface $providerStub;

    private MockObject&EntityManagerInterface $entityManagerMock;

    /**
     * @var \PHPUnit\Framework\MockObject\Stub&\Doctrine\ORM\EntityRepository<\Netgen\Layouts\RemoteMedia\API\Values\RemoteMediaItem>
     */
    private Stub&EntityRepository $remoteMediaItemRepositoryStub;

    private RemoteMediaValueLoader $valueLoader;

    protected function setUp(): void
    {
        $this->providerStub = self::createStub(ProviderInterface::class);
        $this->entityManagerMock = $this->createMock(EntityManagerInterface::class);
        $this->remoteMediaItemRepositoryStub = self::createStub(EntityRepository::class);

        $this->entityManagerMock
            ->expects($this->once())
            ->method('getRepository')
            ->willReturn($this->remoteMediaItemRepositoryStub);

        $this->valueLoader = new RemoteMediaValueLoader($this->providerStub, $this->entityManagerMock);
    }

    public function testLoadExisting(): void
    {
        $resource = new RemoteResource(
            remoteId: 'upload|video|folder/test_resource',
            type: RemoteResource::TYPE_VIDEO,
            url: 'https://cloudinary.com/test/upload/video/folder/test_resource',
            md5: '922adce6ceff0f0ab367cf321bdd1909',
            name: 'test_resource',
            folder: Folder::fromPath('folder'),
        );

        $location = new RemoteResourceLocation($resource, 'netgen_layouts_value');

        $remoteMediaItem = new RemoteMediaItem('upload||video||folder|test_resource', $location);

        $this->remoteMediaItemRepositoryStub
            ->method('findOneBy')
            ->willReturn($remoteMediaItem);

        self::assertSame($location, $this->valueLoader->load('upload||video||folder|test_resource'));
    }

    public function testLoadNewLocation(): void
    {
        $resource = new RemoteResource(
            remoteId: 'upload|video|folder/test_resource',
            type: RemoteResource::TYPE_VIDEO,
            url: 'https://cloudinary.com/test/upload/video/folder/test_resource',
            md5: '5a4c5dd69f0c282cdec63a5a699d1d74',
            name: 'test_resource',
            folder: Folder::fromPath('folder'),
        );

        $location = new RemoteResourceLocation($resource, 'netgen_layouts_value');

        $this->remoteMediaItemRepositoryStub
            ->method('findOneBy')
            ->willReturn(null);

        $this->providerStub
            ->method('loadByRemoteId')
            ->willReturn($resource);

        $this->providerStub
            ->method('store')
            ->willReturn($resource);

        $this->providerStub
            ->method('storeLocation')
            ->willReturn($location);

        $remoteMediaItem = new RemoteMediaItem('upload||video||folder|test_resource', $location);

        $this->entityManagerMock
            ->expects($this->once())
            ->method('persist')
            ->with($remoteMediaItem);

        self::assertSame($location, $this->valueLoader->load('upload||video||folder|test_resource'));
    }

    public function testLoadNewResource(): void
    {
        $resource = new RemoteResource(
            remoteId: 'upload|video|folder/test_resource',
            type: RemoteResource::TYPE_VIDEO,
            url: 'https://cloudinary.com/test/upload/video/folder/test_resource',
            md5: '3c0c49aac4b5dd39ecf1cf6e6a6555ca',
            name: 'test_resource',
            folder: Folder::fromPath('folder'),
        );

        $location = new RemoteResourceLocation($resource, 'netgen_layouts_value');

        $this->remoteMediaItemRepositoryStub
            ->method('findOneBy')
            ->willReturn(null);

        $this->providerStub
            ->method('loadByRemoteId')
            ->willThrowException(new RemoteResourceNotFoundException('upload|video|folder/test_resource'));

        $this->providerStub
            ->method('loadFromRemote')
            ->willReturn($resource);

        $this->providerStub
            ->method('store')
            ->willReturn($resource);

        $this->providerStub
            ->method('storeLocation')
            ->willReturn($location);

        $remoteMediaItem = new RemoteMediaItem('upload||video||folder|test_resource', $location);

        $this->entityManagerMock
            ->expects($this->once())
            ->method('persist')
            ->with($remoteMediaItem);

        self::assertSame($location, $this->valueLoader->load('upload||video||folder|test_resource'));
    }

    public function testLoadNotFound(): void
    {
        $this->remoteMediaItemRepositoryStub
            ->method('findOneBy')
            ->willReturn(null);

        $this->providerStub
            ->method('loadByRemoteId')
            ->willThrowException(new RemoteResourceNotFoundException('upload|video|folder/test_resource'));

        $this->providerStub
            ->method('loadFromRemote')
            ->willThrowException(new RemoteResourceNotFoundException('upload|video|folder/test_resource'));

        self::assertNull($this->valueLoader->load('upload||video||folder|test_resource'));
    }

    public function testLoadByRemoteId(): void
    {
        $resource = new RemoteResource(
            remoteId: 'upload|video|folder/test_resource',
            type: RemoteResource::TYPE_VIDEO,
            url: 'https://cloudinary.com/test/upload/video/folder/test_resource',
            md5: '7646ae197b0fa3a85ccd8f48e35a600b',
            name: 'test_resource',
            folder: Folder::fromPath('folder'),
        );

        $location = new RemoteResourceLocation($resource, 'netgen_layouts_value');

        $remoteMediaItem = new RemoteMediaItem('upload||video||folder|test_resource', $location);

        $this->remoteMediaItemRepositoryStub
            ->method('findOneBy')
            ->willReturn($remoteMediaItem);

        self::assertSame($location, $this->valueLoader->loadByRemoteId('upload||video||folder|test_resource'));
    }
}
