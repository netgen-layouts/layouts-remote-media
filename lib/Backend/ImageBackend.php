<?php

declare(strict_types=1);

namespace Netgen\ContentBrowser\Cloudinary\Backend;

use Netgen\ContentBrowser\Cloudinary\Item\Image\Item;
use Netgen\ContentBrowser\Cloudinary\Item\Image\Location;
use Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Value;
use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Provider\Cloudinary\CloudinaryProvider;
use Netgen\ContentBrowser\Backend\BackendInterface;
use Netgen\ContentBrowser\Backend\SearchQuery;
use Netgen\ContentBrowser\Item\ItemInterface;
use Netgen\ContentBrowser\Item\LocationInterface;

/**
 * @method \Netgen\ContentBrowser\Backend\SearchResultInterface searchItems(SearchQuery $searchQuery)
 * @method int searchItemsCount(SearchQuery $searchQuery)
 */
final class ImageBackend implements BackendInterface
{
    private const ROOT_LOCATION_NAME = 'root';

    /**
     * @var \Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Provider\Cloudinary\CloudinaryProvider
     */
    private $provider;

    public function __construct(CloudinaryProvider $provider)
    {
        $this->provider = $provider;
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

        return new Location((string) $id, self::ROOT_LOCATION_NAME);
    }

    public function loadItem($value): ItemInterface
    {
        $resource = $this->provider->getRemoteResource($value, 'image');

        return new Item($resource);
    }

    public function getSubLocations(LocationInterface $location): iterable
    {
        if ($location->getLocationId() !== self::ROOT_LOCATION_NAME) {
            return [];
        }

        $folders = $this->provider->listFolders();

        $locations = [];
        foreach ($folders as $folder) {
            $locations[] = new Location((string) $folder['name'], self::ROOT_LOCATION_NAME);
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
        $query = $location->getLocationId() !== self::ROOT_LOCATION_NAME
            ? $location->getName()
            : '';

        $resources = $this->provider->searchResources($query, $limit, $offset);

        $items = [];
        foreach ($resources as $resource) {
            $items[] = new Item(Value::createFromCloudinaryResponse($resource));
        }

        return $items;
    }

    public function getSubItemsCount(LocationInterface $location): int
    {
        if ($location->getLocationId() === self::ROOT_LOCATION_NAME) {
            return $this->provider->countResources();
        }

        return $this->provider->countResourcesInFolder($location->getLocationId());
    }

    public function search(string $searchText, int $offset = 0, int $limit = 25): iterable
    {
        $resources = $this->provider->searchResources($searchText, $limit, $offset);

        $items = [];
        foreach ($resources as $resource) {
            $items[] = new Item(Value::createFromCloudinaryResponse($resource));
        }

        return $items;
    }

    public function searchCount(string $searchText): int
    {
        return $this->provider->countResourcesInFolder($searchText);
    }

    public function __call($name, $arguments)
    {
        // TODO: Implement @method \Netgen\ContentBrowser\Backend\SearchResultInterface searchItems(SearchQuery $searchQuery)
        // TODO: Implement @method int searchItemsCount(SearchQuery $searchQuery)
    }

    private function buildRootLocation(): Location
    {
        return new Location(self::ROOT_LOCATION_NAME, null);
    }
}
