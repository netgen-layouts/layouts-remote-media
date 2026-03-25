<?php

declare(strict_types=1);

namespace Netgen\Layouts\RemoteMedia\Collection\QueryType\Handler;

use Netgen\Layouts\API\Values\Collection\Query;
use Netgen\Layouts\Collection\QueryType\QueryTypeHandlerInterface;
use Netgen\Layouts\Parameters\ParameterBuilderInterface;
use Netgen\Layouts\Parameters\ParameterType;
use Netgen\Layouts\RemoteMedia\Core\RemoteMedia\NextCursorResolverInterface;
use Netgen\RemoteMedia\API\ProviderInterface;
use Netgen\RemoteMedia\API\Search\Query as SearchQuery;
use Netgen\RemoteMedia\API\Values\Folder;
use Netgen\RemoteMedia\API\Values\RemoteResource;
use Netgen\RemoteMedia\API\Values\RemoteResourceLocation;
use RuntimeException;
use Throwable;

use function array_map;
use function is_string;

final class RemoteMediaFolderHandler implements QueryTypeHandlerInterface
{
    private const LOCATION_SOURCE = 'remote_media_folder_query';

    public function __construct(
        private readonly ProviderInterface $provider,
        private readonly NextCursorResolverInterface $nextCursorResolver,
    ) {}

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

        if ($folderPath === null || $folderPath === '') {
            return [];
        }

        $limit ??= 25;

        try {
            $searchQuery = $this->buildSearchQuery($query, $limit);

            if ($offset > 0) {
                try {
                    $cursor = $this->nextCursorResolver->resolve($searchQuery, $offset);
                    $searchQuery->setNextCursor($cursor);
                } catch (RuntimeException) {
                    throw new RuntimeException('Jumping to pages is not supported. Please, use only next/previous buttons.');
                }
            }

            $result = $this->provider->search($searchQuery);

            if (is_string($result->getNextCursor())) {
                $this->nextCursorResolver->save($searchQuery, $offset + $limit, $result->getNextCursor());
            }

            return array_map(
                static fn (RemoteResource $resource) => new RemoteResourceLocation($resource, self::LOCATION_SOURCE),
                $result->getResources(),
            );
        } catch (RuntimeException $e) {
            throw $e;
        } catch (Throwable $t) {
            return [];
        }
    }

    public function getCount(Query $query): int
    {
        $folderPath = $query->getParameter('folder_path')->getValue();

        if ($folderPath === null || $folderPath === '') {
            return 0;
        }

        try {
            $searchQuery = $this->buildSearchQuery($query, 0);

            return $this->provider->searchCount($searchQuery);
        } catch (Throwable) {
            return 0;
        }
    }

    public function isContextual(Query $query): bool
    {
        return false;
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
