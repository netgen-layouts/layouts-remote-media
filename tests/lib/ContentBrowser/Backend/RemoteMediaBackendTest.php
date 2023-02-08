<?php

declare(strict_types=1);

namespace Netgen\Layouts\RemoteMedia\Tests\ContentBrowser\Backend;

use Cloudinary\Api\Response;
use Netgen\ContentBrowser\Backend\SearchQuery;
use Netgen\ContentBrowser\Backend\SearchResult;
use Netgen\ContentBrowser\Config\Configuration;
use Netgen\ContentBrowser\Exceptions\NotFoundException;
use Netgen\Layouts\RemoteMedia\ContentBrowser\Backend\RemoteMediaBackend;
use Netgen\Layouts\RemoteMedia\ContentBrowser\Item\RemoteMedia\Item;
use Netgen\Layouts\RemoteMedia\ContentBrowser\Item\RemoteMedia\Location;
use Netgen\RemoteMedia\API\NextCursorResolverInterface;
use Netgen\RemoteMedia\API\Search\Query;
use Netgen\RemoteMedia\API\Search\Result;
use Netgen\RemoteMedia\API\Values\RemoteResource;
use Netgen\RemoteMedia\Core\RemoteMediaProvider;
use Netgen\RemoteMedia\Exception\RemoteResourceNotFoundException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\Translation\Translator;

use function json_encode;

final class RemoteMediaBackendTest extends TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject&\Netgen\RemoteMedia\Core\RemoteMediaProvider
     */
    private MockObject $providerMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject&\Netgen\RemoteMedia\API\NextCursorResolverInterface
     */
    private MockObject $nextCursorResolverMock;

    /**
     * Mocked Translator class directly due to supporting Symfony versions from 3 to 5
     * (TranslationInterface has been deprecated in v4 and replaced in v5 with TranslatorInterface
     * from symfony/translation-contracts bundle).
     *
     * @var \PHPUnit\Framework\MockObject\MockObject&\Symfony\Component\Translation\Translator
     */
    private MockObject $translatorMock;

    private Configuration $config;

    private RemoteMediaBackend $backend;

    protected function setUp(): void
    {
        $this->providerMock = $this->createMock(RemoteMediaProvider::class);
        $this->nextCursorResolverMock = $this->createMock(NextCursorResolverInterface::class);
        $this->translatorMock = $this->createMock(Translator::class);
        $this->config = new Configuration('remote_media', 'Remote media', []);

        $this->backend = new RemoteMediaBackend(
            $this->providerMock,
            $this->nextCursorResolverMock,
            $this->translatorMock,
            $this->config,
        );
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Backend\RemoteMediaBackend::__construct
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Backend\RemoteMediaBackend::buildSections
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Backend\RemoteMediaBackend::getAllowedTypes
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Backend\RemoteMediaBackend::getSections
     */
    public function testGetSections(): void
    {
        $this->translatorMock
            ->expects(self::exactly(4))
            ->method('trans')
            ->willReturnMap(
                [
                    ['backend.remote_media.resource_type.all', [], 'ngcb', null, 'All'],
                    ['backend.remote_media.resource_type.image', [], 'ngcb', null, 'Image'],
                    ['backend.remote_media.resource_type.video', [], 'ngcb', null, 'Video'],
                    ['backend.remote_media.resource_type.raw', [], 'ngcb', null, 'RAW'],
                ],
            );

        $sections = $this->backend->getSections();

        self::assertCount(4, $sections);
        self::assertContainsOnlyInstancesOf(Location::class, $sections);
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Backend\RemoteMediaBackend::__construct
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Backend\RemoteMediaBackend::buildSections
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Backend\RemoteMediaBackend::getAllowedTypes
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Backend\RemoteMediaBackend::getSections
     */
    public function testGetSectionsWithFilter(): void
    {
        $this->config->setParameter('allowed_types', 'image,video');

        $this->translatorMock
            ->expects(self::exactly(3))
            ->method('trans')
            ->willReturnMap(
                [
                    ['backend.remote_media.resource_type.all', [], 'ngcb', null, 'All'],
                    ['backend.remote_media.resource_type.image', [], 'ngcb', null, 'Image'],
                    ['backend.remote_media.resource_type.video', [], 'ngcb', null, 'Video'],
                ],
            );

        $sections = $this->backend->getSections();

        self::assertCount(3, $sections);
        self::assertContainsOnlyInstancesOf(Location::class, $sections);
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Backend\RemoteMediaBackend::__construct
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Backend\RemoteMediaBackend::buildSections
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Backend\RemoteMediaBackend::getAllowedTypes
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Backend\RemoteMediaBackend::getSections
     */
    public function testGetSectionsWithEmptyFilter(): void
    {
        $this->config->setParameter('allowed_types', '');

        $this->translatorMock
            ->expects(self::exactly(4))
            ->method('trans')
            ->willReturnMap(
                [
                    ['backend.remote_media.resource_type.all', [], 'ngcb', null, 'All'],
                    ['backend.remote_media.resource_type.image', [], 'ngcb', null, 'Image'],
                    ['backend.remote_media.resource_type.video', [], 'ngcb', null, 'Video'],
                    ['backend.remote_media.resource_type.raw', [], 'ngcb', null, 'RAW'],
                ],
            );

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
        $resource = RemoteResource::createFromParameters([
            'resourceId' => 'some/folder/path/my_video.mp4',
            'resourceType' => 'video',
        ]);

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
            ->willThrowException(
                new RemoteResourceNotFoundException('some/folder/path/my_video.mp4', 'video'),
            );

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
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Backend\RemoteMediaBackend::getAllowedTypes
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Backend\RemoteMediaBackend::getSubItems
     */
    public function testGetSubItemsWithOffset(): void
    {
        $location = Location::createFromId('all|some|folder');
        $nextCursor = 'k83hn24hs92ao98';

        $query = new Query(
            '',
            ['image', 'video', 'raw'],
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
            ['image', 'video', 'raw'],
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
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Backend\RemoteMediaBackend::getAllowedTypes
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Backend\RemoteMediaBackend::getSubItems
     */
    public function testGetSubItemsWithFilter(): void
    {
        $location = Location::createFromId('all|some|folder');

        $this->config->setParameter('allowed_types', 'image,raw');

        $query = new Query(
            '',
            ['image', 'raw'],
            5,
            'some/folder',
        );

        $this->nextCursorResolverMock
            ->expects(self::never())
            ->method('resolve');

        $searchResult = Result::fromResponse(new Response($this->getSearchResponse()));

        $this->providerMock
            ->expects(self::once())
            ->method('searchResources')
            ->with($query)
            ->willReturn($searchResult);

        $this->nextCursorResolverMock
            ->expects(self::once())
            ->method('save')
            ->with($query, 5, 'testcursor123');

        $items = $this->backend->getSubItems($location, 0, 5);

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
    public function testGetSubItemsCountInSection(): void
    {
        $location = Location::createAsSection('video', 'Video');

        $query = new Query(
            '',
            'video',
            0,
        );

        $this->providerMock
            ->expects(self::once())
            ->method('searchResourcesCount')
            ->with($query)
            ->willReturn(150);

        self::assertSame(150, $this->backend->getSubItemsCount($location));
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Backend\RemoteMediaBackend::getAllowedTypes
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Backend\RemoteMediaBackend::getSubItemsCount
     */
    public function testGetSubItemsCount(): void
    {
        $location = Location::createAsSection('all', 'All');

        $query = new Query(
            '',
            ['image', 'video', 'raw'],
            0,
        );

        $this->providerMock
            ->expects(self::once())
            ->method('searchResourcesCount')
            ->with($query)
            ->willReturn(1000);

        self::assertSame(1000, $this->backend->getSubItemsCount($location));
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Backend\RemoteMediaBackend::getAllowedTypes
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Backend\RemoteMediaBackend::getSubItemsCount
     */
    public function testGetSubItemsCountInFolderWithFilter(): void
    {
        $location = Location::createFromId('all|test|folder|subfolder');

        $this->config->setParameter('allowed_types', 'image');

        $query = new Query(
            '',
            ['image'],
            0,
            'test/folder/subfolder',
        );

        $this->providerMock
            ->expects(self::once())
            ->method('searchResourcesCount')
            ->with($query)
            ->willReturn(6000);

        self::assertSame(6000, $this->backend->getSubItemsCount($location));
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Backend\RemoteMediaBackend::getAllowedTypes
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Backend\RemoteMediaBackend::getSubItemsCount
     */
    public function testGetSubItemsCountWithEmptyFilter(): void
    {
        $location = Location::createAsSection('all', 'All');

        $this->config->setParameter('allowed_types', '');

        $query = new Query(
            '',
            ['image', 'video', 'raw'],
            0,
        );

        $this->providerMock
            ->expects(self::once())
            ->method('searchResourcesCount')
            ->with($query)
            ->willReturn(1000);

        self::assertSame(1000, $this->backend->getSubItemsCount($location));
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Backend\RemoteMediaBackend::getAllowedTypes
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
            ['image', 'video', 'raw'],
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
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Backend\RemoteMediaBackend::getAllowedTypes
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Backend\RemoteMediaBackend::searchItems
     */
    public function testSearchItemsWithFilter(): void
    {
        $location = Location::createFromId('all');

        $searchQuery = new SearchQuery('test', $location);

        $this->config->setParameter('allowed_types', 'raw');

        $this->nextCursorResolverMock
            ->expects(self::never())
            ->method('resolve');

        $query = new Query(
            'test',
            ['raw'],
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
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Backend\RemoteMediaBackend::getAllowedTypes
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Backend\RemoteMediaBackend::searchItemsCount
     */
    public function testSearchItemsCountWithFilter(): void
    {
        $location = Location::createFromId('all|some|folder');

        $searchQuery = new SearchQuery('test', $location);

        $this->config->setParameter('allowed_types', 'video');

        $query = new Query(
            'test',
            ['video'],
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
        $response = new stdClass();
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
