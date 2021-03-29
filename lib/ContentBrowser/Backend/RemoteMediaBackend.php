<?php

declare(strict_types=1);

namespace Netgen\Layouts\RemoteMedia\ContentBrowser\Backend;

use Cloudinary\Api\NotFound as CloudinaryNotFoundException;
use Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Value;
use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\NextCursorResolver;
use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Provider\Cloudinary\Search\Query;
use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\RemoteMediaProvider;
use Netgen\ContentBrowser\Backend\BackendInterface;
use Netgen\ContentBrowser\Backend\SearchQuery;
use Netgen\ContentBrowser\Backend\SearchResult;
use Netgen\ContentBrowser\Backend\SearchResultInterface;
use Netgen\ContentBrowser\Exceptions\NotFoundException;
use Netgen\ContentBrowser\Item\ItemInterface;
use Netgen\ContentBrowser\Item\LocationInterface;
use Netgen\Layouts\RemoteMedia\ContentBrowser\Item\RemoteMedia\Item;
use Netgen\Layouts\RemoteMedia\ContentBrowser\Item\RemoteMedia\Location;
use Netgen\Layouts\RemoteMedia\Core\RemoteMedia\ResourceQuery;
use Symfony\Component\Translation\TranslatorInterface;
use function count;
use function is_string;
use function sprintf;

final class RemoteMediaBackend implements BackendInterface
{
    /**
     * @var \Netgen\Bundle\RemoteMediaBundle\RemoteMedia\RemoteMediaProvider
     */
    private $provider;

    /**
     * @var \Netgen\Bundle\RemoteMediaBundle\RemoteMedia\NextCursorResolver
     */
    private $nextCursorResolver;

    /**
     * @var \Symfony\Component\Translation\TranslatorInterface
     */
    private $translator;

    public function __construct(
        RemoteMediaProvider $provider,
        NextCursorResolver $nextCursorResolver,
        TranslatorInterface $translator
    ) {
        $this->provider = $provider;
        $this->nextCursorResolver = $nextCursorResolver;
        $this->translator = $translator;
    }

    public function getSections(): iterable
    {
        return $this->buildSections();
    }

    public function loadLocation($id): LocationInterface
    {
        return Location::createFromId((string) $id);
    }

    public function loadItem($value): ItemInterface
    {
        $query = ResourceQuery::createFromString((string) $value);

        try {
            $resource = $this->provider->getRemoteResource(
                $query->getResourceId(),
                $query->getResourceType()
            );
        } catch (CloudinaryNotFoundException $e) {
            throw new NotFoundException(
                sprintf(
                    'Remote media with ID "%s" not found.',
                    $value
                )
            );
        }

        return new Item($resource);
    }

    public function getSubLocations(LocationInterface $location): iterable
    {
        /** @var \Netgen\Layouts\RemoteMedia\ContentBrowser\Item\RemoteMedia\Location $location */
        if ($location->isFolder()) {
            return [];
        }

        $folders = $this->provider->listFolders();

        $locations = [];
        foreach ($folders as $folder) {
            $locations[] = Location::createFromId(
                Location::TYPE_FOLDER . '|' . $location->getResourceType() . '|' . $folder['name']
            );
        }

        return $locations;
    }

    public function getSubLocationsCount(LocationInterface $location): int
    {
        /** @var \Netgen\Layouts\RemoteMedia\ContentBrowser\Item\RemoteMedia\Location $location */
        if ($location->isFolder()) {
            return 0;
        }

        return count($this->provider->listFolders());
    }

    public function getSubItems(LocationInterface $location, int $offset = 0, int $limit = 25): iterable
    {
        /** @var \Netgen\Layouts\RemoteMedia\ContentBrowser\Item\RemoteMedia\Location $location */
        $resourceType = $location->getResourceType() !== Location::RESOURCE_TYPE_ALL ?
            $location->getResourceType()
            : null;

        $query = new Query(
            '',
            $resourceType,
            $limit,
            $location->getFolder()
        );

        if ($offset > 0) {
            $nextCursor = $this->nextCursorResolver->resolve($query, $offset);

            $query = new Query(
                '',
                $resourceType,
                $limit,
                $location->getFolder(),
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
            $items[] = new Item(Value::createFromCloudinaryResponse($resource));
        }

        return $items;
    }

    public function getSubItemsCount(LocationInterface $location): int
    {
        /** @var \Netgen\Layouts\RemoteMedia\ContentBrowser\Item\RemoteMedia\Location $location */
        if ($location->isSection() || $location->getFolder() === null) {
            return $this->provider->countResources();
        }

        return $this->provider->countResourcesInFolder($location->getFolder());
    }

    public function searchItems(SearchQuery $searchQuery): SearchResultInterface
    {
        $resourceType = null;
        $folder = null;
        if ($searchQuery->getLocation() instanceof Location) {
            $resourceType = $searchQuery->getLocation()->getResourceType() !== Location::RESOURCE_TYPE_ALL
                ? $searchQuery->getLocation()->getResourceType()
                : null;

            $folder = $searchQuery->getLocation()->getFolder();
        }

        $query = new Query(
            $searchQuery->getSearchText(),
            $resourceType,
            $searchQuery->getLimit(),
            $folder
        );

        if ($searchQuery->getOffset() > 0) {
            $nextCursor = $this->nextCursorResolver->resolve($query, $searchQuery->getOffset());

            $query = new Query(
                $searchQuery->getSearchText(),
                $resourceType,
                $searchQuery->getLimit(),
                $folder,
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
            $items[] = new Item(Value::createFromCloudinaryResponse($resource));
        }

        return new SearchResult($items);
    }

    public function searchItemsCount(SearchQuery $searchQuery): int
    {
        $resourceType = null;
        $folder = null;
        if ($searchQuery->getLocation() instanceof Location) {
            $resourceType = $searchQuery->getLocation()->getResourceType() !== Location::RESOURCE_TYPE_ALL
                ? $searchQuery->getLocation()->getResourceType()
                : null;

            $folder = $searchQuery->getLocation()->getFolder();
        }

        $query = new Query(
            $searchQuery->getSearchText(),
            $resourceType,
            $searchQuery->getLimit(),
            $folder
        );

        return $this->provider->searchResourcesCount($query);
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

    /**
     * @return \Netgen\Layouts\RemoteMedia\ContentBrowser\Item\RemoteMedia\Location[]
     */
    private function buildSections(): array
    {
        return [
            Location::createFromId(
                'section|' . Location::RESOURCE_TYPE_ALL,
                $this->translator->trans('backend.remote_media.resource_type.' . Location::RESOURCE_TYPE_ALL, [], 'ngcb')
            ),
            Location::createFromId(
                'section|' . Location::RESOURCE_TYPE_IMAGE,
                $this->translator->trans('backend.remote_media.resource_type.' . Location::RESOURCE_TYPE_IMAGE, [], 'ngcb')
            ),
            Location::createFromId(
                'section|' . Location::RESOURCE_TYPE_VIDEO,
                $this->translator->trans('backend.remote_media.resource_type.' . Location::RESOURCE_TYPE_VIDEO, [], 'ngcb')
            ),
            Location::createFromId(
                'section|' . Location::RESOURCE_TYPE_RAW,
                $this->translator->trans('backend.remote_media.resource_type.' . Location::RESOURCE_TYPE_RAW, [], 'ngcb')
            ),
        ];
    }
}
