<?php

declare(strict_types=1);

namespace Netgen\Layouts\RemoteMedia\ContentBrowser\Backend;

use Netgen\ContentBrowser\Backend\BackendInterface;
use Netgen\ContentBrowser\Backend\SearchQuery;
use Netgen\ContentBrowser\Backend\SearchResult;
use Netgen\ContentBrowser\Backend\SearchResultInterface;
use Netgen\ContentBrowser\Config\Configuration;
use Netgen\ContentBrowser\Exceptions\NotFoundException;
use Netgen\ContentBrowser\Item\ItemInterface;
use Netgen\ContentBrowser\Item\LocationInterface;
use Netgen\Layouts\RemoteMedia\ContentBrowser\Item\RemoteMedia\Item;
use Netgen\Layouts\RemoteMedia\ContentBrowser\Item\RemoteMedia\Location;
use Netgen\Layouts\RemoteMedia\Core\RemoteMedia\NextCursorResolverInterface;
use Netgen\Layouts\RemoteMedia\Core\RemoteMedia\ResourceQuery;
use Netgen\RemoteMedia\API\ProviderInterface;
use Netgen\RemoteMedia\API\Search\Query;
use Netgen\RemoteMedia\API\Values\Folder;
use Netgen\RemoteMedia\API\Values\RemoteResourceLocation;
use Netgen\RemoteMedia\Exception\RemoteResourceNotFoundException;
use Symfony\Contracts\Translation\TranslatorInterface;

use function count;
use function explode;
use function in_array;
use function is_string;
use function sprintf;

final class RemoteMediaBackend implements BackendInterface
{
    public function __construct(
        private ProviderInterface $provider,
        private NextCursorResolverInterface $nextCursorResolver,
        private TranslatorInterface $translator,
        private Configuration $config,
        private ?string $rootFolder = null,
    ) {}

    public function getSections(): iterable
    {
        return $this->buildSections();
    }

    public function loadLocation(int|string $id): LocationInterface
    {
        return Location::createFromId((string) $id);
    }

    public function loadItem(int|string $value): ItemInterface
    {
        $query = ResourceQuery::createFromValue((string) $value);

        try {
            $resource = $this->provider->loadFromRemote($query->getRemoteId());
        } catch (RemoteResourceNotFoundException) {
            throw new NotFoundException(
                sprintf(
                    'Remote media with ID "%s" not found.',
                    $value,
                ),
            );
        }

        return new Item(new RemoteResourceLocation($resource));
    }

    public function getSubLocations(LocationInterface $location): iterable
    {
        if (!$location instanceof Location) {
            return [];
        }

        $folders = $this->provider->listFolders($location->folder);

        $locations = [];
        foreach ($folders as $folder) {
            $locations[] = Location::createFromFolder($folder, $location->type);
        }

        return $locations;
    }

    public function getSubLocationsCount(LocationInterface $location): int
    {
        if (!$location instanceof Location) {
            return 0;
        }

        return count($this->provider->listFolders($location->folder));
    }

    public function getSubItems(LocationInterface $location, int $offset = 0, int $limit = 25): iterable
    {
        if (!$location instanceof Location) {
            return [];
        }

        $types = $location->type !== Location::RESOURCE_TYPE_ALL ?
             [$location->type]
            : $this->getAllowedTypes();

        $query = new Query(
            types: $types,
            folders: $location->folder instanceof Folder ? [$location->folder] : [],
            limit: $limit,
        );

        if ($offset > 0) {
            $nextCursor = $this->nextCursorResolver->resolve($query, $offset);

            $query->setNextCursor($nextCursor);
        }

        $result = $this->provider->search($query);

        if (is_string($result->getNextCursor())) {
            $this->nextCursorResolver->save($query, $offset + $limit, $result->getNextCursor());
        }

        $items = [];
        foreach ($result->getResources() as $resource) {
            $items[] = new Item(new RemoteResourceLocation($resource));
        }

        return $items;
    }

    public function getSubItemsCount(LocationInterface $location): int
    {
        if (!$location instanceof Location) {
            return 0;
        }

        $types = $location->type !== Location::RESOURCE_TYPE_ALL ?
            [$location->type]
            : $this->getAllowedTypes();

        $query = new Query(
            types: $types,
            folders: $location->folder instanceof Folder ? [$location->folder] : [],
            limit: 0,
        );

        return $this->provider->searchCount($query);
    }

    public function searchItems(SearchQuery $searchQuery): SearchResultInterface
    {
        $types = $this->getAllowedTypes();

        $location = $searchQuery->location;
        $folders = [];

        if ($location instanceof Location) {
            $types = $location->type !== Location::RESOURCE_TYPE_ALL
                ? [$location->type]
                : $this->getAllowedTypes();

            $folders = $location->folder instanceof Folder
                ? [$location->folder]
                : [];
        }

        $query = new Query(
            query: $searchQuery->searchText,
            types: $types,
            folders: $folders,
            limit: $searchQuery->limit,
        );

        if ($searchQuery->offset > 0) {
            $nextCursor = $this->nextCursorResolver->resolve($query, $searchQuery->offset);

            $query->setNextCursor($nextCursor);
        }

        $result = $this->provider->search($query);

        if (is_string($result->getNextCursor())) {
            $this->nextCursorResolver->save($query, $searchQuery->offset + $searchQuery->limit, $result->getNextCursor());
        }

        $items = [];
        foreach ($result->getResources() as $resource) {
            $items[] = new Item(new RemoteResourceLocation($resource));
        }

        return new SearchResult($items);
    }

    public function searchItemsCount(SearchQuery $searchQuery): int
    {
        $types = $this->getAllowedTypes();

        $location = $searchQuery->location;
        $folders = [];

        if ($location instanceof Location) {
            $types = $location->type !== Location::RESOURCE_TYPE_ALL
                ? [$location->type]
                : $this->getAllowedTypes();

            $folders = $location->folder instanceof Folder
                ? [$location->folder]
                : [];
        }

        $query = new Query(
            query: $searchQuery->searchText,
            types: $types,
            folders: $folders,
            limit: $searchQuery->limit,
        );

        return $this->provider->searchCount($query);
    }

    /**
     * @return \Netgen\Layouts\RemoteMedia\ContentBrowser\Item\RemoteMedia\Location[]
     */
    private function buildSections(): array
    {
        $allowedTypes = $this->getAllowedTypes();

        $sections = [
            Location::createAsSection(
                Location::RESOURCE_TYPE_ALL,
                $this->translator->trans('backend.remote_media.resource_type.' . Location::RESOURCE_TYPE_ALL, [], 'ngcb'),
                $this->rootFolder,
            ),
        ];

        foreach ($allowedTypes as $type) {
            $sections[] = Location::createAsSection(
                $type,
                $this->translator->trans('backend.remote_media.resource_type.' . $type, [], 'ngcb'),
                $this->rootFolder,
            );
        }

        return $sections;
    }

    /**
     * @return string[]
     */
    private function getAllowedTypes(): array
    {
        $allowedTypes = [];

        if ($this->config->hasParameter('allowed_types')) {
            $allowedTypes = explode(',', $this->config->getParameter('allowed_types'));
        }

        foreach ($allowedTypes as $key => $type) {
            if (!in_array($type, Location::SUPPORTED_TYPES, true)) {
                unset($allowedTypes[$key]);
            }
        }

        return count($allowedTypes) > 0 ? $allowedTypes : Location::SUPPORTED_TYPES;
    }
}
