<?php

declare(strict_types=1);

namespace Netgen\Layouts\RemoteMedia\Tests\Browser\Backend;

use Netgen\ContentBrowser\Backend\SearchQuery;
use Netgen\ContentBrowser\Config\Configuration;
use Netgen\ContentBrowser\Exceptions\NotFoundException;
use Netgen\ContentBrowser\Tests\Stubs\Location as LocationStub;
use Netgen\Layouts\RemoteMedia\Browser\Backend\RemoteMediaBackend;
use Netgen\Layouts\RemoteMedia\Browser\Item\RemoteMedia\Item;
use Netgen\Layouts\RemoteMedia\Browser\Item\RemoteMedia\Location;
use Netgen\Layouts\RemoteMedia\Core\RemoteMedia\NextCursorResolverInterface;
use Netgen\RemoteMedia\API\ProviderInterface;
use Netgen\RemoteMedia\API\Search\Query;
use Netgen\RemoteMedia\API\Search\Result;
use Netgen\RemoteMedia\API\Values\Folder;
use Netgen\RemoteMedia\API\Values\RemoteResource;
use Netgen\RemoteMedia\Exception\RemoteResourceNotFoundException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

#[CoversClass(RemoteMediaBackend::class)]
final class RemoteMediaBackendTest extends TestCase
{
    private Stub&ProviderInterface $providerStub;

    private Stub&NextCursorResolverInterface $nextCursorResolverStub;

    private Stub&TranslatorInterface $translatorStub;

    private Configuration $config;

    private RemoteMediaBackend $backend;

    protected function setUp(): void
    {
        $this->providerStub = self::createStub(ProviderInterface::class);
        $this->nextCursorResolverStub = self::createStub(NextCursorResolverInterface::class);
        $this->translatorStub = self::createStub(TranslatorInterface::class);
        $this->config = new Configuration('remote_media', 'Remote media', []);

        $this->backend = new RemoteMediaBackend(
            $this->providerStub,
            $this->nextCursorResolverStub,
            $this->translatorStub,
            $this->config,
        );
    }

    public function testGetSections(): void
    {
        $this->translatorStub
            ->method('trans')
            ->willReturnMap(
                [
                    ['backend.remote_media.resource_type.all', [], 'ngcb', null, 'All resources'],
                    ['backend.remote_media.resource_type.image', [], 'ngcb', null, 'Image'],
                    ['backend.remote_media.resource_type.audio', [], 'ngcb', null, 'Audio'],
                    ['backend.remote_media.resource_type.video', [], 'ngcb', null, 'Video'],
                    ['backend.remote_media.resource_type.document', [], 'ngcb', null, 'Document'],
                    ['backend.remote_media.resource_type.other', [], 'ngcb', null, 'Other'],
                ],
            );

        $sections = $this->backend->getSections();

        self::assertCount(6, $sections);
        self::assertContainsOnlyInstancesOf(Location::class, $sections);
    }

    public function testGetSectionsWithFilter(): void
    {
        $this->config->setParameter('allowed_types', 'image,video');

        $this->translatorStub
            ->method('trans')
            ->willReturnMap(
                [
                    ['backend.remote_media.resource_type.all', [], 'ngcb', null, 'All resources'],
                    ['backend.remote_media.resource_type.image', [], 'ngcb', null, 'Image'],
                    ['backend.remote_media.resource_type.video', [], 'ngcb', null, 'Video'],
                ],
            );

        $sections = $this->backend->getSections();

        self::assertCount(3, $sections);
        self::assertContainsOnlyInstancesOf(Location::class, $sections);
    }

    public function testGetSectionsWithEmptyFilter(): void
    {
        $this->config->setParameter('allowed_types', '');

        $this->translatorStub
            ->method('trans')

            ->willReturnMap(
                [
                    ['backend.remote_media.resource_type.all', [], 'ngcb', null, 'All resources'],
                    ['backend.remote_media.resource_type.image', [], 'ngcb', null, 'Image'],
                    ['backend.remote_media.resource_type.audio', [], 'ngcb', null, 'Audio'],
                    ['backend.remote_media.resource_type.video', [], 'ngcb', null, 'Video'],
                    ['backend.remote_media.resource_type.document', [], 'ngcb', null, 'Document'],
                    ['backend.remote_media.resource_type.other', [], 'ngcb', null, 'Other'],
                ],
            );

        $sections = $this->backend->getSections();

        self::assertCount(6, $sections);
        self::assertContainsOnlyInstancesOf(Location::class, $sections);
    }

    public function testGetSectionsWithRootFolder(): void
    {
        $this->backend = new RemoteMediaBackend(
            $this->providerStub,
            $this->nextCursorResolverStub,
            $this->translatorStub,
            $this->config,
            'images/layouts',
        );

        $this->translatorStub
            ->method('trans')
            ->willReturnMap(
                [
                    ['backend.remote_media.resource_type.all', [], 'ngcb', null, 'All resources'],
                    ['backend.remote_media.resource_type.image', [], 'ngcb', null, 'Image'],
                    ['backend.remote_media.resource_type.audio', [], 'ngcb', null, 'Audio'],
                    ['backend.remote_media.resource_type.video', [], 'ngcb', null, 'Video'],
                    ['backend.remote_media.resource_type.document', [], 'ngcb', null, 'Document'],
                    ['backend.remote_media.resource_type.other', [], 'ngcb', null, 'Other'],
                ],
            );

        $sections = [...$this->backend->getSections()];

        self::assertCount(6, $sections);
        self::assertContainsOnlyInstancesOf(Location::class, $sections);

        self::assertSame('all||images|layouts', $sections[0]->locationId);
        self::assertSame('image||images|layouts', $sections[1]->locationId);
    }

    public function testLoadLocation(): void
    {
        $location = $this->backend->loadLocation('video||media|videos');

        self::assertSame('video||media|videos', $location->locationId);
        self::assertSame('videos', $location->name);
        self::assertSame('video||media', $location->parentId);

        $location = $this->backend->loadLocation('video||media');

        self::assertSame('video||media', $location->locationId);
        self::assertSame('media', $location->name);
        self::assertSame('video', $location->parentId);

        $location = $this->backend->loadLocation('video');

        self::assertSame('video', $location->locationId);
        self::assertSame('video', $location->name);
        self::assertNull($location->parentId);
    }

    public function testLoadItem(): void
    {
        $value = 'upload||video||media|videos|my_video.mp4';
        $resource = new RemoteResource(
            remoteId: 'upload|video|media/videos/my_video.mp4',
            type: RemoteResource::TYPE_VIDEO,
            url: 'https://cloudinary.com/test/upload/video/media/videos/my_video.mp4',
            md5: 'd4e74f7778d6c5a65f8066593e06a93d',
            name: 'my_video.mp4',
            folder: Folder::fromPath('media/videos'),
        );

        $this->providerStub
            ->method('loadFromRemote')
            ->with('upload|video|media/videos/my_video.mp4')
            ->willReturn($resource);

        $item = $this->backend->loadItem($value);

        self::assertInstanceOf(Item::class, $item);
        self::assertSame($value, $item->value);
        self::assertSame('my_video.mp4', $item->name);
    }

    public function testLoadItemNotFound(): void
    {
        $value = 'upload||video||media|videos|my_video.mp4';

        $this->providerStub
            ->method('loadFromRemote')
            ->with('upload|video|media/videos/my_video.mp4')
            ->willThrowException(
                new RemoteResourceNotFoundException('upload|video|media/videos/my_video.mp4'),
            );

        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('Remote media with ID "' . $value . '" not found.');

        $this->backend->loadItem($value);
    }

    public function testGetSubLocationsRoot(): void
    {
        $location = Location::createAsSection('other', 'other');

        $folders = [
            Folder::fromPath('downloads'),
            Folder::fromPath('files'),
            Folder::fromPath('documents'),
        ];

        $this->providerStub
            ->method('listFolders')
            ->willReturn($folders);

        $locations = $this->backend->getSubLocations($location);

        self::assertCount(3, $locations);
        self::assertContainsOnlyInstancesOf(Location::class, $locations);
    }

    public function testGetSubLocationsFolder(): void
    {
        $location = Location::createFromFolder(Folder::fromPath('test_folder/test_subfolder'), 'other');

        $folders = [
            Folder::fromPath('downloads'),
            Folder::fromPath('files'),
            Folder::fromPath('documents'),
        ];

        $this->providerStub
            ->method('listFolders')
            ->with(Folder::fromPath('test_folder/test_subfolder'))
            ->willReturn($folders);

        $locations = $this->backend->getSubLocations($location);

        self::assertCount(3, $locations);
        self::assertContainsOnlyInstancesOf(Location::class, $locations);
    }

    public function testGetSubLocationsInvalidLocation(): void
    {
        self::assertSame([], $this->backend->getSubLocations(new LocationStub(42)));
    }

    public function testGetSubLocationsCountRoot(): void
    {
        $location = Location::createAsSection('other', 'other');

        $folders = [
            Folder::fromPath('downloads'),
            Folder::fromPath('files'),
            Folder::fromPath('documents'),
        ];

        $this->providerStub
            ->method('listFolders')
            ->willReturn($folders);

        self::assertSame(3, $this->backend->getSubLocationsCount($location));
    }

    public function testGetSubLocationsCountFolder(): void
    {
        $location = Location::createFromFolder(Folder::fromPath('test_folder/test_subfolder'), 'other');

        $folders = [
            Folder::fromPath('downloads'),
            Folder::fromPath('files'),
            Folder::fromPath('documents'),
        ];

        $this->providerStub
            ->method('listFolders')
            ->with(Folder::fromPath('test_folder/test_subfolder'))
            ->willReturn($folders);

        self::assertSame(3, $this->backend->getSubLocationsCount($location));
    }

    public function testGetSubLocationsCountInvalidLocation(): void
    {
        self::assertSame(0, $this->backend->getSubLocationsCount(new LocationStub(42)));
    }

    public function testGetSubItems(): void
    {
        $location = Location::createAsSection('image', 'Image');

        $query = new Query(
            types: ['image'],
            limit: 25,
        );

        $searchResult = $this->getSearchResult();

        $this->providerStub
            ->method('search')
            ->with($query)
            ->willReturn($searchResult);

        $this->nextCursorResolverStub
            ->method('save')
            ->with($query, 25, 'test-cursor-123');

        $items = $this->backend->getSubItems($location);

        self::assertCount(5, $items);
        self::assertContainsOnlyInstancesOf(Item::class, $items);
    }

    public function testGetSubItemsWithOffset(): void
    {
        $location = Location::createFromId('all||media|new');
        $nextCursor = 'k83hn24hs92ao98';

        $query = new Query(
            types: ['image', 'audio', 'video', 'document', 'other'],
            folders: [Folder::fromPath('media/new')],
            limit: 5,
        );

        $this->nextCursorResolverStub
            ->method('resolve')
            ->with($query, 5)
            ->willReturn($nextCursor);

        $query = new Query(
            types: ['image', 'audio', 'video', 'document', 'other'],
            folders: [Folder::fromPath('media/new')],
            limit: 5,
            nextCursor: $nextCursor,
        );

        $searchResult = $this->getSearchResult();

        $this->providerStub
            ->method('search')
            ->with($query)
            ->willReturn($searchResult);

        $this->nextCursorResolverStub
            ->method('save')
            ->with($query, 10, 'test-cursor-123');

        $items = $this->backend->getSubItems($location, 5, 5);

        self::assertCount(5, $items);
        self::assertContainsOnlyInstancesOf(Item::class, $items);
    }

    public function testGetSubItemsWithFilter(): void
    {
        $location = Location::createFromId('all||media|latest');

        $this->config->setParameter('allowed_types', 'image,other');

        $query = new Query(
            types: ['image', 'other'],
            folders: [Folder::fromPath('media/latest')],
            limit: 5,
        );

        $searchResult = $this->getSearchResult();

        $this->providerStub
            ->method('search')
            ->with($query)
            ->willReturn($searchResult);

        $this->nextCursorResolverStub
            ->method('save')
            ->with($query, 5, 'test-cursor-123');

        $items = $this->backend->getSubItems($location, 0, 5);

        self::assertCount(5, $items);
        self::assertContainsOnlyInstancesOf(Item::class, $items);
    }

    public function testGetSubItemsWithNoResults(): void
    {
        $location = Location::createAsSection('video', 'Video');

        $query = new Query(
            types: ['video'],
            limit: 25,
        );

        $searchResult = $this->getEmptySearchResult();

        $this->providerStub
            ->method('search')
            ->with($query)
            ->willReturn($searchResult);

        self::assertSame([], $this->backend->getSubItems($location));
    }

    public function testGetSubItemsInvalidLocation(): void
    {
        self::assertSame([], $this->backend->getSubItems(new LocationStub(42)));
    }

    public function testGetSubItemsCountInSection(): void
    {
        $location = Location::createAsSection('video', 'Video');

        $query = new Query(
            types: ['video'],
            limit: 0,
        );

        $this->providerStub
            ->method('searchCount')
            ->with($query)
            ->willReturn(150);

        self::assertSame(150, $this->backend->getSubItemsCount($location));
    }

    public function testGetSubItemsCount(): void
    {
        $location = Location::createAsSection('all', 'All');

        $query = new Query(
            types: ['image', 'audio', 'video', 'document', 'other'],
            limit: 0,
        );

        $this->providerStub
            ->method('searchCount')
            ->with($query)
            ->willReturn(1000);

        self::assertSame(1000, $this->backend->getSubItemsCount($location));
    }

    public function testGetSubItemsCountInFolderWithFilter(): void
    {
        $location = Location::createFromId('all||media|latest|blog');

        $this->config->setParameter('allowed_types', 'image');

        $query = new Query(
            types: ['image'],
            folders: [Folder::fromPath('media/latest/blog')],
            limit: 0,
        );

        $this->providerStub
            ->method('searchCount')
            ->with($query)
            ->willReturn(6000);

        self::assertSame(6000, $this->backend->getSubItemsCount($location));
    }

    public function testGetSubItemsCountWithEmptyFilter(): void
    {
        $location = Location::createAsSection('all', 'All');

        $this->config->setParameter('allowed_types', '');

        $query = new Query(
            types: ['image', 'audio', 'video', 'document', 'other'],
            limit: 0,
        );

        $this->providerStub
            ->method('searchCount')
            ->with($query)
            ->willReturn(1000);

        self::assertSame(1000, $this->backend->getSubItemsCount($location));
    }

    public function testGetSubItemsCountInvalidLocation(): void
    {
        self::assertSame(0, $this->backend->getSubItemsCount(new LocationStub(42)));
    }

    public function testSearchItems(): void
    {
        $location = Location::createFromId('all');

        $searchQuery = new SearchQuery('test', $location);

        $query = new Query(
            query: 'test',
            types: ['image', 'audio', 'video', 'document', 'other'],
            limit: 25,
        );

        $searchResult = $this->getSearchResult();

        $this->providerStub
            ->method('search')
            ->with($query)
            ->willReturn($searchResult);

        $this->nextCursorResolverStub
            ->method('save')
            ->with($query, 25, 'test-cursor-123');

        $searchResult = $this->backend->searchItems($searchQuery);

        self::assertCount(5, $searchResult->results);
        self::assertContainsOnlyInstancesOf(Item::class, $searchResult->results);
    }

    public function testSearchItemsWithFilter(): void
    {
        $location = Location::createFromFolder(Folder::fromPath('media'), 'all');

        $searchQuery = new SearchQuery('test', $location);

        $this->config->setParameter('allowed_types', 'other');

        $query = new Query(
            query: 'test',
            types: ['other'],
            folders: [Folder::fromPath('media')],
            limit: 25,
        );

        $searchResult = $this->getSearchResult();

        $this->providerStub
            ->method('search')
            ->with($query)
            ->willReturn($searchResult);

        $this->nextCursorResolverStub
            ->method('save')
            ->with($query, 25, 'test-cursor-123');

        $searchResult = $this->backend->searchItems($searchQuery);

        self::assertCount(5, $searchResult->results);
        self::assertContainsOnlyInstancesOf(Item::class, $searchResult->results);
    }

    public function testSearchItemsWithOffset(): void
    {
        $location = Location::createFromId('image');

        $searchQuery = new SearchQuery('test', $location);
        $searchQuery->limit = 5;
        $searchQuery->offset = 5;

        $nextCursor = 'k83hn24hs92ao98';

        $query = new Query(
            query: 'test',
            types: ['image'],
            limit: 5,
        );

        $this->nextCursorResolverStub
            ->method('resolve')
            ->with($query, 5)
            ->willReturn($nextCursor);

        $query = new Query(
            query: 'test',
            types: ['image'],
            limit: 5,
            nextCursor: $nextCursor,
        );

        $searchResult = $this->getSearchResult();

        $this->providerStub
            ->method('search')
            ->with($query)
            ->willReturn($searchResult);

        $this->nextCursorResolverStub
            ->method('save')
            ->with($query, 10, 'test-cursor-123');

        $searchResult = $this->backend->searchItems($searchQuery);

        self::assertCount(5, $searchResult->results);
        self::assertContainsOnlyInstancesOf(Item::class, $searchResult->results);
    }

    public function testSearchItemsWithNoResults(): void
    {
        $location = Location::createAsSection('video', 'Video');

        $searchQuery = new SearchQuery('non-existing text', $location);

        $query = new Query(
            query: 'non-existing text',
            types: ['video'],
            limit: 25,
        );

        $searchResult = $this->getEmptySearchResult();

        $this->providerStub
            ->method('search')
            ->with($query)
            ->willReturn($searchResult);

        $searchResult = $this->backend->searchItems($searchQuery);

        self::assertCount(0, $searchResult->results);
    }

    public function testSearchItemsCount(): void
    {
        $location = Location::createFromId('other||media|files');

        $searchQuery = new SearchQuery('test', $location);

        $query = new Query(
            query: 'test',
            types: ['other'],
            folders: [Folder::fromPath('media/files')],
            limit: 25,
        );

        $this->providerStub
            ->method('searchCount')
            ->with($query)
            ->willReturn(12);

        self::assertSame(12, $this->backend->searchItemsCount($searchQuery));
    }

    public function testSearchItemsCountWithFilter(): void
    {
        $location = Location::createFromId('all||media|files');

        $searchQuery = new SearchQuery('test', $location);

        $this->config->setParameter('allowed_types', 'video');

        $query = new Query(
            query: 'test',
            types: ['video'],
            folders: [Folder::fromPath('media/files')],
            limit: 25,
        );

        $this->providerStub
            ->method('searchCount')
            ->with($query)
            ->willReturn(12);

        self::assertSame(12, $this->backend->searchItemsCount($searchQuery));
    }

    public function testSearchItemsCountWithoutLocation(): void
    {
        $searchQuery = new SearchQuery('test');

        $query = new Query(
            query: 'test',
            types: ['image', 'audio', 'video', 'document', 'other'],
            limit: 25,
        );

        $this->providerStub
            ->method('searchCount')
            ->with($query)
            ->willReturn(12);

        self::assertSame(12, $this->backend->searchItemsCount($searchQuery));
    }

    private function getSearchResult(): Result
    {
        return new Result(
            15,
            'test-cursor-123',
            [
                $this->getResource('test_resource_1', RemoteResource::TYPE_IMAGE, 'https://cloudinary.com/test/upload/image/test_resource_1', '857bcccd18b32a4463760bffd77d87f6'),
                $this->getResource('test_resource_2', RemoteResource::TYPE_VIDEO, 'https://cloudinary.com/test/upload/video/test_resource_2', '83c98c7ec6a1d2ef4b609892ffb17f3e'),
                $this->getResource('test_resource_3', RemoteResource::TYPE_AUDIO, 'https://cloudinary.com/test/upload/audio/test_resource_3', '495219081e3353c31ef3e149f99b04fe'),
                $this->getResource('test_resource_4', RemoteResource::TYPE_DOCUMENT, 'https://cloudinary.com/test/upload/document/test_resource_4', 'd44f50df3af3a8e497269859c77acedf'),
                $this->getResource('folder/test_resource_5', RemoteResource::TYPE_OTHER, 'https://cloudinary.com/test/upload/raw/test_resource_5', '955d612b460288731a497557b6f4ffb0'),
            ],
        );
    }

    private function getEmptySearchResult(): Result
    {
        return new Result(0, null, []);
    }

    private function getResource(string $remoteId, string $type, string $url, string $md5): RemoteResource
    {
        return new RemoteResource(
            remoteId: $remoteId,
            type: $type,
            url: $url,
            md5: $md5,
        );
    }
}
