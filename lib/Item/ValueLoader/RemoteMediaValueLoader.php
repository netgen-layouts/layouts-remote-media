<?php

declare(strict_types=1);

namespace Netgen\Layouts\RemoteMedia\Item\ValueLoader;

use Cloudinary\Api\NotFound as CloudinaryNotFoundException;
use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\RemoteMediaProvider;
use Netgen\ContentBrowser\Exceptions\NotFoundException;
use Netgen\Layouts\Item\ValueLoaderInterface;
use Netgen\Layouts\RemoteMedia\Core\RemoteMedia\ResourceQuery;
use Netgen\Layouts\RemoteMedia\Helper\ResourceIdHelper;

final class RemoteMediaValueLoader implements ValueLoaderInterface
{
    /**
     * @var \Netgen\Bundle\RemoteMediaBundle\RemoteMedia\RemoteMediaProvider
     */
    private $provider;

    /**
     * @var \Netgen\Layouts\RemoteMedia\Helper\ResourceIdHelper
     */
    private $resourceIdHelper;

    public function __construct(RemoteMediaProvider $provider, ResourceIdHelper $resourceIdHelper)
    {
        $this->provider = $provider;
        $this->resourceIdHelper = $resourceIdHelper;
    }

    public function load($id): ?object
    {
        $query = ResourceQuery::createFromString($id);

        try {
            return $this->provider->getRemoteResource(
                $query->resourceId,
                $query->resourceType
            );
        } catch (CloudinaryNotFoundException $e) {
            return null;
        }
    }

    public function loadByRemoteId($remoteId): ?object
    {
        return $this->load($remoteId);
    }
}
