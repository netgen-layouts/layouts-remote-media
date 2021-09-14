<?php

declare(strict_types=1);

namespace Netgen\Layouts\RemoteMedia\ContentBrowser\Item\RemoteMedia;

use Netgen\ContentBrowser\Item\ItemInterface;
use Netgen\RemoteMedia\API\Values\RemoteResource;
use function array_pop;
use function explode;
use function str_replace;

final class Item implements ItemInterface
{
    private RemoteResource $resource;

    public function __construct(RemoteResource $resource)
    {
        $this->resource = $resource;
    }

    public function getValue(): string
    {
        return $this->getResourceType() . '|' . str_replace('/', '|', $this->resource->resourceId);
    }

    public function getName(): string
    {
        $parts = explode('/', $this->resource->resourceId);

        return array_pop($parts);
    }

    public function isVisible(): bool
    {
        return true;
    }

    public function isSelectable(): bool
    {
        return true;
    }

    public function getResourceType(): string
    {
        return $this->resource->resourceType;
    }

    public function getRemoteMediaValue(): RemoteResource
    {
        return $this->resource;
    }
}
