<?php

declare(strict_types=1);

namespace Netgen\Layouts\RemoteMedia\ContentBrowser\Backend;

use Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Value;
use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\NextCursorResolver;
use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Provider\Cloudinary\Search\Query;
use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\RemoteMediaProvider;
use Netgen\ContentBrowser\Backend\BackendInterface;
use Netgen\ContentBrowser\Backend\SearchQuery;
use Netgen\ContentBrowser\Backend\SearchResult;
use Netgen\ContentBrowser\Backend\SearchResultInterface;
use Netgen\ContentBrowser\Item\ItemInterface;
use Netgen\ContentBrowser\Item\LocationInterface;
use Netgen\Layouts\RemoteMedia\ContentBrowser\Item\Image\Item;
use Netgen\Layouts\RemoteMedia\ContentBrowser\Item\Image\Location;
use Netgen\Layouts\RemoteMedia\Helper\ResourceIdHelper;
use function count;

final class ImageBackend implements BackendInterface
{
    private const ROOT_LOCATION_NAME = 'root';

    /**
     * @var \Netgen\Bundle\RemoteMediaBundle\RemoteMedia\RemoteMediaProvider
     */
    private $provider;

    /**
     * @var \Netgen\Layouts\RemoteMedia\Helper\ResourceIdHelper
     */
    private $resourceIdHelper;

    /**
     * @var \Netgen\Bundle\RemoteMediaBundle\RemoteMedia\NextCursorResolver
     */
    private $nextCursorResolver;

    public function __construct(RemoteMediaProvider $provider, ResourceIdHelper $resourceIdHelper, NextCursorResolver $nextCursorResolver)
    {
        $this->provider = $provider;
        $this->resourceIdHelper = $resourceIdHelper;
        $this->nextCursorResolver = $nextCursorResolver;
    }

    public function getSections(): iterable
    {
        return [$this->buildRootLocation()];
    }

    public function loadLocation($id): LocationInterface
    {
        if ($id === self::ROOT_LOCATION_NAME) {
            return $this->buildRootLocation();
        }

        return new Location(
            $this->resourceIdHelper->fromRemoteId((string) $id),
            self::ROOT_LOCATION_NAME
        );
    }

    public function loadItem($value): ItemInterface
    {
        $id = $this->resourceIdHelper->toRemoteId((string) $value);
        $resource = $this->provider->getRemoteResource($id);

        return $this->buildItem($resource);
    }

    public function getSubLocations(LocationInterface $location): iterable
    {
        if ($location->getLocationId() !== self::ROOT_LOCATION_NAME) {
            return [];
        }

        $folders = $this->provider->listFolders();

        $locations = [];
        foreach ($folders as $folder) {
            $locations[] = new Location(
                $this->resourceIdHelper->fromRemoteId((string) $folder['name']),
                self::ROOT_LOCATION_NAME
            );
        }

        return $locations;
    }

    public function getSubLocationsCount(LocationInterface $location): int
    {
        if ($location->getLocationId() === self::ROOT_LOCATION_NAME) {
            return count($this->provider->listFolders());
        }

        return 0;
    }

    public function getSubItems(LocationInterface $location, int $offset = 0, int $limit = 25): iterable
    {
        $folder = $location->getLocationId() !== self::ROOT_LOCATION_NAME
            ? $location->getName()
            : '';

        $query = new Query(
            '',
            'image',
            $limit,
            $folder
        );

        if ($offset > 0) {
            $nextCursor = $this->nextCursorResolver->resolve($query, $offset);

            $query = new Query(
                '',
                'image',
                $limit,
                $folder,
                null,
                $nextCursor
            );
        }

        $result = $this->provider->searchResources($query);

        if (is_string($result->getNextCursor())) {
            $this->nextCursorResolver->save($query, $offset + $limit, $result->getNextCursor());
        }

        $items = [];

        foreach ($result->getResults() as $resource) {
            $items[] = $this->buildItem(Value::createFromCloudinaryResponse($resource));
        }

        return $items;
    }

    public function getSubItemsCount(LocationInterface $location): int
    {
        if ($location->getLocationId() === self::ROOT_LOCATION_NAME) {
            return $this->provider->countResources();
        }

        return $this->provider->countResourcesInFolder((string) $location->getLocationId());
    }

    public function searchItems(SearchQuery $searchQuery): SearchResultInterface
    {
        $query = new Query(
            $searchQuery->getSearchText(),
            'image',
            $searchQuery->getLimit()
        );

        if ($searchQuery->getOffset() > 0) {
            $nextCursor = $this->nextCursorResolver->resolve($query, $searchQuery->getOffset());

            $query = new Query(
                $searchQuery->getSearchText(),
                'image',
                $searchQuery->getLimit(),
                null,
                null,
                $nextCursor
            );
        }

        $result = $this->provider->searchResources($query);

        if (is_string($result->getNextCursor())) {
            $this->nextCursorResolver->save($query, $searchQuery->getOffset() + $searchQuery->getLimit(), $result->getNextCursor());
        }

        $items = [];

        foreach ($result->getResults() as $resource) {
            $items[] = $this->buildItem(Value::createFromCloudinaryResponse($resource));
        }

        return new SearchResult($items);
    }

    public function searchItemsCount(SearchQuery $searchQuery): int
    {
        return $this->provider->countResourcesInFolder($searchQuery->getSearchText());
    }

    public function search(string $searchText, int $offset = 0, int $limit = 25): iterable
    {
        $searchQuery = new SearchQuery($searchText);
        $searchQuery->setOffset($offset);
        $searchQuery->setLimit($limit);

        $searchResult = $this->searchItems($searchQuery);

        return $searchResult->getResults();
    }

    public function searchCount(string $searchText): int
    {
        return $this->searchItemsCount(new SearchQuery($searchText));
    }

    private function buildRootLocation(): Location
    {
        return new Location(self::ROOT_LOCATION_NAME);
    }

    private function buildItem(Value $resource): Item
    {
        $resourceId = $resource->resourceId !== null ?
            $this->resourceIdHelper->fromRemoteId($resource->resourceId)
            : null;

        return new Item($resource, $resourceId);
    }
}
