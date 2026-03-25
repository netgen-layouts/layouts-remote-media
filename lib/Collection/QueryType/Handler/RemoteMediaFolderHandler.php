<?php

declare(strict_types=1);

namespace Netgen\Layouts\RemoteMedia\Collection\QueryType\Handler;

use Netgen\Layouts\API\Values\Collection\Query;
use Netgen\Layouts\Collection\QueryType\QueryTypeHandlerInterface;
use Netgen\Layouts\Parameters\ParameterBuilderInterface;
use Netgen\Layouts\Parameters\ParameterType;
use Netgen\RemoteMedia\API\ProviderInterface;
use Netgen\RemoteMedia\API\Search\Query as SearchQuery;
use Netgen\RemoteMedia\API\Values\Folder;
use Netgen\RemoteMedia\API\Values\RemoteResource;
use Netgen\RemoteMedia\API\Values\RemoteResourceLocation;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Throwable;

use function array_map;
use function array_slice;
use function count;
use function implode;
use function str_replace;

final class RemoteMediaFolderHandler implements QueryTypeHandlerInterface
{
    private const LOCATION_SOURCE = 'remote_media_folder_query';
    private const CACHE_KEY_PREFIX = 'ngl_remote_media_folder';

    private LoggerInterface $logger;

    public function __construct(
        private readonly ProviderInterface $provider,
        private readonly ?CacheItemPoolInterface $cache = null,
        private readonly int $cacheTtl = 1800,
        ?LoggerInterface $logger = null,
    ) {
        $this->logger = $logger ?? new NullLogger();
    }

    public function buildParameters(ParameterBuilderInterface $builder): void
    {
        $builder->add(
            'folder_path',
            ParameterType\TextLineType::class,
            [
                'required' => true,
            ],
        );

        $builder->add(
            'resource_type',
            ParameterType\ChoiceType::class,
            [
                'required' => true,
                'options' => [
                    'All' => '',
                    'Image' => 'image',
                    'Video' => 'video',
                    'Document' => 'document',
                ],
            ],
        );
    }

    public function getValues(Query $query, int $offset = 0, ?int $limit = null): iterable
    {
        $folderPath = $query->getParameter('folder_path')->getValue();

        $this->logger->debug('RemoteMediaFolderHandler::getValues', [
            'folder_path' => $folderPath,
            'offset' => $offset,
            'limit' => $limit,
        ]);

        if ($folderPath === null || $folderPath === '') {
            return [];
        }

        $cacheKey = $this->buildCacheKey('values', $query, $offset, $limit);
        $cached = $this->getCached($cacheKey);

        if ($cached !== null) {
            return $cached;
        }

        try {
            $searchQuery = $this->buildSearchQuery($query, $offset + ($limit ?? 25));
            $result = $this->provider->search($searchQuery);
            $resources = array_slice($result->getResources(), $offset, $limit);

            $this->logger->debug('RemoteMediaFolderHandler::getValues result', [
                'total' => $result->getTotalCount(),
                'returned' => count($resources),
            ]);

            $locations = array_map(
                static fn (RemoteResource $resource) => new RemoteResourceLocation($resource, self::LOCATION_SOURCE),
                $resources,
            );

            $this->saveToCache($cacheKey, $locations);

            return $locations;
        } catch (Throwable $t) {
            $this->logger->error('RemoteMediaFolderHandler::getValues error', [
                'error' => $t->getMessage(),
                'trace' => $t->getTraceAsString(),
            ]);

            return [];
        }
    }

    public function getCount(Query $query): int
    {
        $folderPath = $query->getParameter('folder_path')->getValue();

        $this->logger->debug('RemoteMediaFolderHandler::getCount', [
            'folder_path' => $folderPath,
        ]);

        if ($folderPath === null || $folderPath === '') {
            return 0;
        }

        $cacheKey = $this->buildCacheKey('count', $query);
        $cached = $this->getCached($cacheKey);

        if ($cached !== null) {
            return $cached;
        }

        try {
            $searchQuery = $this->buildSearchQuery($query, 0);
            $count = $this->provider->searchCount($searchQuery);

            $this->saveToCache($cacheKey, $count);

            return $count;
        } catch (Throwable $t) {
            $this->logger->error('RemoteMediaFolderHandler::getCount error', [
                'error' => $t->getMessage(),
            ]);

            return 0;
        }
    }

    public function isContextual(Query $query): bool
    {
        return false;
    }

    private function getCached(string $cacheKey): mixed
    {
        if ($this->cache === null) {
            return null;
        }

        $cacheItem = $this->cache->getItem($cacheKey);

        if ($cacheItem->isHit()) {
            $this->logger->debug('RemoteMediaFolderHandler: cache hit', ['key' => $cacheKey]);

            return $cacheItem->get();
        }

        return null;
    }

    private function saveToCache(string $cacheKey, mixed $value): void
    {
        if ($this->cache === null) {
            return;
        }

        $cacheItem = $this->cache->getItem($cacheKey);
        $cacheItem->set($value);
        $cacheItem->expiresAfter($this->cacheTtl);
        $this->cache->save($cacheItem);
    }

    private function buildCacheKey(string $type, Query $query, int $offset = 0, ?int $limit = null): string
    {
        $folderPath = $query->getParameter('folder_path')->getValue() ?? '';
        $resourceType = $query->getParameter('resource_type')->getValue() ?? '';

        $parts = [
            self::CACHE_KEY_PREFIX,
            $type,
            $folderPath,
            $resourceType,
        ];

        if ($type === 'values') {
            $parts[] = (string) $offset;
            $parts[] = (string) ($limit ?? 0);
        }

        $key = implode('-', $parts);

        return str_replace(
            ['{', '}', '(', ')', '/', '\\', '@', ' '],
            '_',
            $key,
        );
    }

    private function buildSearchQuery(Query $query, int $limit): SearchQuery
    {
        $folderPath = $query->getParameter('folder_path')->getValue();
        $resourceType = $query->getParameter('resource_type')->getValue();

        $folder = Folder::fromPath($folderPath);
        $types = $resourceType !== '' && $resourceType !== null ? [$resourceType] : [];

        return new SearchQuery(
            folders: [$folder],
            types: $types,
            limit: $limit,
        );
    }
}
