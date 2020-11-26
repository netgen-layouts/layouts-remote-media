<?php

declare(strict_types=1);

namespace Netgen\Layouts\RemoteMedia\ContentBrowser\Item\ColumnProvider\Image;

use Netgen\ContentBrowser\Item\ColumnProvider\ColumnValueProviderInterface;
use Netgen\ContentBrowser\Item\ItemInterface;

class Height implements ColumnValueProviderInterface
{
    public function getValue(ItemInterface $item): ?string
    {
        /** @var $item \Netgen\Layouts\RemoteMedia\ContentBrowser\Item\Image\Item */
        if (!array_key_exists('height', $item->getRemoteMediaValue()->metaData)) {
            return '';
        }

        return (string) $item->getRemoteMediaValue()->metaData['height'];
    }
}
