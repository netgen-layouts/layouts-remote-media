<?php

declare(strict_types=1);

namespace Netgen\Layouts\RemoteMedia\Browser\Item\RemoteMedia;

use Netgen\ContentBrowser\Item\ItemInterface;
use Netgen\RemoteMedia\API\Values\RemoteResourceLocation;

use function str_replace;

final class Item implements ItemInterface
{
    public string $value {
        get => str_replace(['|', '/'], ['||', '|'], $this->remoteResourceLocation->getRemoteResource()->getRemoteId());
    }

    public string $name {
        get => $this->remoteResourceLocation->getRemoteResource()->getName() ?? 'Unknown';
    }

    public true $isVisible {
        get => true;
    }

    public true $isSelectable {
        get => true;
    }

    public string $type {
        get => $this->remoteResourceLocation->getRemoteResource()->getType();
    }

    public function __construct(
        public private(set) RemoteResourceLocation $remoteResourceLocation,
    ) {}
}
