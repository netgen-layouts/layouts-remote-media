<?php

declare(strict_types=1);

namespace Netgen\Layouts\RemoteMedia\Item\ValueLoader;

use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\RemoteMediaProvider;
use Netgen\Layouts\Item\ValueLoaderInterface;
use Netgen\Layouts\RemoteMedia\Helper\ResourceIdHelper;

final class ImageValueLoader implements ValueLoaderInterface
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
        return $this->provider->getRemoteResource(
            $this->resourceIdHelper->toRemoteId((string) $id)
        );
    }

    public function loadByRemoteId($remoteId): ?object
    {
        return $this->load($remoteId);
    }
}
