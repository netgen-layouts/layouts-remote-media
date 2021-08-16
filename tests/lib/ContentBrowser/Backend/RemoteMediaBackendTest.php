<?php

declare(strict_types=1);

namespace Netgen\Layouts\RemoteMedia\Tests\ContentBrowser\Backend;

use Cloudinary\Api\NotFound as CloudinaryNotFoundException;
use Cloudinary\Api\Response;
use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\NextCursorResolver;
use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Provider\Cloudinary\Search\Query;
use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Provider\Cloudinary\Search\Result;
use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\RemoteMediaProvider;
use Netgen\ContentBrowser\Backend\SearchQuery;
use Netgen\ContentBrowser\Backend\SearchResult;
use Netgen\ContentBrowser\Exceptions\NotFoundException;
use Netgen\Layouts\RemoteMedia\ContentBrowser\Backend\RemoteMediaBackend;
use Netgen\Layouts\RemoteMedia\ContentBrowser\Item\RemoteMedia\Item;
use Netgen\Layouts\RemoteMedia\ContentBrowser\Item\RemoteMedia\Location;
use Netgen\Layouts\RemoteMedia\Tests\Stubs\RemoteMedia as RemoteMediaStub;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\Translation\TranslatorInterface;
use function json_encode;

final class RemoteMediaBackendTest extends TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject&\Netgen\Bundle\RemoteMediaBundle\RemoteMedia\RemoteMediaProvider
     */
    private $providerMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject&\Netgen\Bundle\RemoteMediaBundle\RemoteMedia\NextCursorResolver
     */
    private $nextCursorResolverMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject&\Symfony\Component\Translation\TranslatorInterface
     */
    private $translatorMock;

    /**
     * @var \Netgen\Layouts\RemoteMedia\ContentBrowser\Backend\RemoteMediaBackend
     */
    private $backend;

    protected function setUp(): void
    {
        $this->providerMock = $this->createMock(RemoteMediaProvider::class);
        $this->nextCursorResolverMock = $this->createMock(NextCursorResolver::class);
        $this->translatorMock = $this->createMock(TranslatorInterface::class);

        $this->backend = new RemoteMediaBackend(
            $this->providerMock,
            $this->nextCursorResolverMock,
            $this->translatorMock,
        );
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Backend\RemoteMediaBackend::__construct
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Backend\RemoteMediaBackend::buildSections
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Backend\RemoteMediaBackend::getSections
     */
    public function testGetSections(): void
    {
        $this->translatorMock
            ->expects(self::exactly(4))
            ->method('trans')
            ->withConsecutive(
                ['backend.remote_media.resource_type.all', [], 'ngcb'],
                ['backend.remote_media.resource_type.image', [], 'ngcb'],
                ['backend.remote_media.resource_type.video', [], 'ngcb'],
                ['backend.remote_media.resource_type.raw', [], 'ngcb'],
            )
            ->willReturnOnConsecutiveCalls('All', 'Image', 'Video', 'RAW');

        $sections = $this->backend->getSections();

        self::assertCount(4, $sections);
        self::assertContainsOnlyInstancesOf(Location::class, $sections);
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Backend\RemoteMediaBackend::loadLocation
     */
    public function testLoadLocation(): void
    {
        $location = $this->backend->loadLocation('video|some|folder|path');

        self::assertSame('video|some|folder|path', $location->getLocationId());
        self::assertSame('path', $location->getName());
        self::assertSame('video|some|folder', $location->getParentId());
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Backend\RemoteMediaBackend::loadItem
     */
    public function testLoadItem(): void
    {
        $value = 'video|some|folder|path|my_video.mp4';
        $resource = new RemoteMediaStub('some/folder/path/my_video.mp4', 'video');

        $this->providerMock
            ->expects(self::once())
            ->method('getRemoteResource')
            ->with('some/folder/path/my_video.mp4', 'video')
            ->willReturn($resource);

        $item = $this->backend->loadItem($value);

        self::assertInstanceOf(Item::class, $item);
        self::assertSame($value, $item->getValue());
        self::assertSame('my_video.mp4', $item->getName());
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Backend\RemoteMediaBackend::loadItem
     */
    public function testLoadItemNotFound(): void
    {
        $value = 'video|some|folder|path|my_video.mp4';

        $this->providerMock
            ->expects(self::once())
            ->method('getRemoteResource')
            ->with('some/folder/path/my_video.mp4', 'video')
            ->willThrowException(new CloudinaryNotFoundException());

        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('Remote media with ID "' . $value . '" not found.');

        $this->backend->loadItem($value);
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Backend\RemoteMediaBackend::getSubLocations
     */
    public function testGetSubLocationsRoot(): void
    {
        $location = Location::createAsSection('raw', 'RAW');

        $folders = [
            [
                'path' => 'downloads',
                'name' => 'downloads',
            ],
            [
                'path' => 'files',
                'name' => 'files',
            ],
            [
                'path' => 'documents',
                'name' => 'documents',
            ],
        ];

        $this->providerMock
            ->expects(self::once())
            ->method('listFolders')
            ->willReturn($folders);

        $locations = $this->backend->getSubLocations($location);

        self::assertCount(3, $locations);
        self::assertContainsOnlyInstancesOf(Location::class, $locations);
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Backend\RemoteMediaBackend::getSubLocations
     */
    public function testGetSubLocationsFolder(): void
    {
        $location = Location::createFromFolder('test_folder/test_subfolder', 'Test sub folder', 'raw');

        $subFolders = [
            [
                'path' => 'downloads',
                'name' => 'downloads',
            ],
            [
                'path' => 'files',
                'name' => 'files',
            ],
            [
                'path' => 'documents',
                'name' => 'documents',
            ],
        ];

        $this->providerMock
            ->expects(self::once())
            ->method('listSubFolders')
            ->with('test_folder/test_subfolder')
            ->willReturn($subFolders);

        $locations = $this->backend->getSubLocations($location);

        self::assertCount(3, $locations);
        self::assertContainsOnlyInstancesOf(Location::class, $locations);
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Backend\RemoteMediaBackend::getSubLocationsCount
     */
    public function testGetSubLocationsCountRoot(): void
    {
        $location = Location::createAsSection('raw', 'RAW');

        $folders = [
            [
                'path' => 'downloads',
                'name' => 'downloads',
            ],
            [
                'path' => 'files',
                'name' => 'files',
            ],
            [
                'path' => 'documents',
                'name' => 'documents',
            ],
        ];

        $this->providerMock
            ->expects(self::once())
            ->method('listFolders')
            ->willReturn($folders);

        self::assertSame(3, $this->backend->getSubLocationsCount($location));
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Backend\RemoteMediaBackend::getSubLocationsCount
     */
    public function testGetSubLocationsCountFolder(): void
    {
        $location = Location::createFromFolder('test_folder/test_subfolder', 'Test sub folder', 'raw');

        $subFolders = [
            [
                'path' => 'downloads',
                'name' => 'downloads',
            ],
            [
                'path' => 'files',
                'name' => 'files',
            ],
            [
                'path' => 'documents',
                'name' => 'documents',
            ],
        ];

        $this->providerMock
            ->expects(self::once())
            ->method('listSubFolders')
            ->with('test_folder/test_subfolder')
            ->willReturn($subFolders);

        self::assertSame(3, $this->backend->getSubLocationsCount($location));
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Backend\RemoteMediaBackend::getSubItems
     */
    public function testGetSubItems(): void
    {
        $location = Location::createAsSection('image', 'Image');

        $this->nextCursorResolverMock
            ->expects(self::never())
            ->method('resolve');

        $query = new Query(
            '',
            'image',
            25,
        );

        $searchResult = Result::fromResponse(new Response($this->getSearchResponse()));

        $this->providerMock
            ->expects(self::once())
            ->method('searchResources')
            ->with($query)
            ->willReturn($searchResult);

        $this->nextCursorResolverMock
            ->expects(self::once())
            ->method('save')
            ->with($query, 25, 'testcursor123');

        $items = $this->backend->getSubItems($location);

        self::assertCount(5, $items);
        self::assertContainsOnlyInstancesOf(Item::class, $items);
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Backend\RemoteMediaBackend::getSubItems
     */
    public function testGetSubItemsWithOffset(): void
    {
        $location = Location::createFromId('all|some|folder');
        $nextCursor = 'k83hn24hs92ao98';

        $query = new Query(
            '',
            null,
            5,
            'some/folder',
        );

        $this->nextCursorResolverMock
            ->expects(self::once())
            ->method('resolve')
            ->with($query, 5)
            ->willReturn($nextCursor);

        $query = new Query(
            '',
            null,
            5,
            'some/folder',
            null,
            $nextCursor,
        );

        $searchResult = Result::fromResponse(new Response($this->getSearchResponse()));

        $this->providerMock
            ->expects(self::once())
            ->method('searchResources')
            ->with($query)
            ->willReturn($searchResult);

        $this->nextCursorResolverMock
            ->expects(self::once())
            ->method('save')
            ->with($query, 10, 'testcursor123');

        $items = $this->backend->getSubItems($location, 5, 5);

        self::assertCount(5, $items);
        self::assertContainsOnlyInstancesOf(Item::class, $items);
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Backend\RemoteMediaBackend::getSubItems
     */
    public function testGetSubItemsWithNoResults(): void
    {
        $location = Location::createAsSection('video', 'Video');

        $this->nextCursorResolverMock
            ->expects(self::never())
            ->method('resolve');

        $query = new Query(
            '',
            'video',
            25,
        );

        $searchResult = Result::fromResponse(new Response($this->getEmptySearchResponse()));

        $this->providerMock
            ->expects(self::once())
            ->method('searchResources')
            ->with($query)
            ->willReturn($searchResult);

        $this->nextCursorResolverMock
            ->expects(self::never())
            ->method('save');

        self::assertSame([], $this->backend->getSubItems($location));
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Backend\RemoteMediaBackend::getSubItemsCount
     */
    public function testGetSubItemsCount(): void
    {
        $location = Location::createAsSection('video', 'Video');

        $this->providerMock
            ->expects(self::once())
            ->method('countResources')
            ->willReturn(5);

        self::assertSame(5, $this->backend->getSubItemsCount($location));
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Backend\RemoteMediaBackend::getSubItemsCount
     */
    public function testGetSubItemsCountInFolder(): void
    {
        $location = Location::createFromId('all|test|folder|subfolder');

        $this->providerMock
            ->expects(self::once())
            ->method('countResourcesInFolder')
            ->with('test/folder/subfolder')
            ->willReturn(2);

        self::assertSame(2, $this->backend->getSubItemsCount($location));
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Backend\RemoteMediaBackend::searchItems
     */
    public function testSearchItems(): void
    {
        $location = Location::createFromId('all');

        $searchQuery = new SearchQuery('test', $location);

        $this->nextCursorResolverMock
            ->expects(self::never())
            ->method('resolve');

        $query = new Query(
            'test',
            null,
            25,
        );

        $searchResult = Result::fromResponse(new Response($this->getSearchResponse()));

        $this->providerMock
            ->expects(self::once())
            ->method('searchResources')
            ->with($query)
            ->willReturn($searchResult);

        $this->nextCursorResolverMock
            ->expects(self::once())
            ->method('save')
            ->with($query, 25, 'testcursor123');

        $searchResult = $this->backend->searchItems($searchQuery);

        self::assertCount(5, $searchResult->getResults());
        self::assertContainsOnlyInstancesOf(Item::class, $searchResult->getResults());
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Backend\RemoteMediaBackend::searchItems
     */
    public function testSearchItemsWithOffset(): void
    {
        $location = Location::createFromId('image');

        $searchQuery = new SearchQuery('test', $location);
        $searchQuery->setLimit(5);
        $searchQuery->setOffset(5);

        $nextCursor = 'k83hn24hs92ao98';

        $query = new Query(
            'test',
            'image',
            5,
        );

        $this->nextCursorResolverMock
            ->expects(self::once())
            ->method('resolve')
            ->with($query, 5)
            ->willReturn($nextCursor);

        $query = new Query(
            'test',
            'image',
            5,
            null,
            null,
            $nextCursor,
        );

        $searchResult = Result::fromResponse(new Response($this->getSearchResponse()));

        $this->providerMock
            ->expects(self::once())
            ->method('searchResources')
            ->with($query)
            ->willReturn($searchResult);

        $this->nextCursorResolverMock
            ->expects(self::once())
            ->method('save')
            ->with($query, 10, 'testcursor123');

        $searchResult = $this->backend->searchItems($searchQuery);

        self::assertCount(5, $searchResult->getResults());
        self::assertContainsOnlyInstancesOf(Item::class, $searchResult->getResults());
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Backend\RemoteMediaBackend::searchItems
     */
    public function testSearchItemsWithNoResults(): void
    {
        $location = Location::createAsSection('video', 'Video');

        $searchQuery = new SearchQuery('non-existing text', $location);

        $this->nextCursorResolverMock
            ->expects(self::never())
            ->method('resolve');

        $query = new Query(
            'non-existing text',
            'video',
            25,
        );

        $searchResult = Result::fromResponse(new Response($this->getEmptySearchResponse()));

        $this->providerMock
            ->expects(self::once())
            ->method('searchResources')
            ->with($query)
            ->willReturn($searchResult);

        $this->nextCursorResolverMock
            ->expects(self::never())
            ->method('save');

        $searchResult = $this->backend->searchItems($searchQuery);

        self::assertInstanceOf(SearchResult::class, $searchResult);
        self::assertCount(0, $searchResult->getResults());
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Backend\RemoteMediaBackend::searchItemsCount
     */
    public function testSearchItemsCount(): void
    {
        $location = Location::createFromId('raw|some|folder');

        $searchQuery = new SearchQuery('test', $location);

        $query = new Query(
            'test',
            'raw',
            25,
            'some/folder',
        );

        $this->providerMock
            ->expects(self::once())
            ->method('searchResourcesCount')
            ->with($query)
            ->willReturn(12);

        self::assertSame(12, $this->backend->searchItemsCount($searchQuery));
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Backend\RemoteMediaBackend::searchItemsCount
     */
    public function testSearchItemsCountWithoutLocation(): void
    {
        $searchQuery = new SearchQuery('test');

        $query = new Query(
            'test',
            null,
            25,
            null,
        );

        $this->providerMock
            ->expects(self::once())
            ->method('searchResourcesCount')
            ->with($query)
            ->willReturn(12);

        self::assertSame(12, $this->backend->searchItemsCount($searchQuery));
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Backend\RemoteMediaBackend::search
     */
    public function testSearch(): void
    {
        $this->nextCursorResolverMock
            ->expects(self::never())
            ->method('resolve');

        $query = new Query(
            'test',
            null,
            25,
        );

        $searchResult = Result::fromResponse(new Response($this->getSearchResponse()));

        $this->providerMock
            ->expects(self::once())
            ->method('searchResources')
            ->with($query)
            ->willReturn($searchResult);

        $this->nextCursorResolverMock
            ->expects(self::once())
            ->method('save')
            ->with($query, 25, 'testcursor123');

        $items = $this->backend->search('test');

        self::assertCount(5, $items);
        self::assertContainsOnlyInstancesOf(Item::class, $items);
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Backend\RemoteMediaBackend::searchCount
     */
    public function testSearchCount(): void
    {
        $location = Location::createFromId('raw|some|folder');

        $searchQuery = new SearchQuery('test', $location);

        $query = new Query(
            'test',
            'raw',
            25,
            'some/folder',
        );

        $this->providerMock
            ->expects(self::once())
            ->method('searchResourcesCount')
            ->with($query)
            ->willReturn(12);

        self::assertSame(12, $this->backend->searchItemsCount($searchQuery));
    }

    private function getSearchResponse(): stdClass
    {
        $response = new \stdClass();
        $response->body = json_encode([
            'total_count' => 15,
            'next_cursor' => 'testcursor123',
            'resources' => [
                $this->getCloudinaryResourceResponse('test_resource_1', 'image'),
                $this->getCloudinaryResourceResponse('test_resource_2', 'image'),
                $this->getCloudinaryResourceResponse('test_resource_3', 'image'),
                $this->getCloudinaryResourceResponse('test_resource_4', 'image'),
                $this->getCloudinaryResourceResponse('test_resource_5', 'image'),
            ],
        ]);
        $response->responseCode = 200;
        $response->headers = [
            'X-FeatureRateLimit-Reset' => 'test',
            'X-FeatureRateLimit-Limit' => 'test',
            'X-FeatureRateLimit-Remaining' => 'test',
        ];

        return $response;
    }

    private function getEmptySearchResponse(): stdClass
    {
        $response = new stdClass();
        $response->body = json_encode([
            'total_count' => 0,
            'next_cursor' => null,
            'resources' => [],
        ]);
        $response->responseCode = 200;
        $response->headers = [
            'X-FeatureRateLimit-Reset' => 'test',
            'X-FeatureRateLimit-Limit' => 'test',
            'X-FeatureRateLimit-Remaining' => 'test',
        ];

        return $response;
    }

    /**
     * @return array<string, mixed>
     */
    private function getCloudinaryResourceResponse(string $resourceId, string $resourceType): array
    {
        return [
            'public_id' => $resourceId,
            'resource_type' => $resourceType,
            'type' => 'upload',
            'url' => 'http://cloudinary.com/c_fit,w_200,h_200/' . $resourceId,
            'secure_url' => 'http://cloudinary.com/c_fit,w_200,h_200/' . $resourceId,
            'bytes' => 435657,
        ];
    }
}
