<?php

declare(strict_types=1);

namespace Netgen\Layouts\RemoteMedia\Item\ValueLoader;

use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\RemoteMediaProvider;
use Netgen\Layouts\Item\ValueLoaderInterface;

class ImageValueLoader implements ValueLoaderInterface
{
    /**
     * @var \Netgen\Bundle\RemoteMediaBundle\RemoteMedia\RemoteMediaProvider
     */
    protected $provider;

    public function __construct(RemoteMediaProvider $provider)
    {
        $this->provider = $provider;
    }

    public function load($id): ?object
    {
        return $this->provider->getRemoteResource($id, 'image');
    }

    public function loadByRemoteId($remoteId): ?object
    {
        return $this->load($remoteId);
    }
}
