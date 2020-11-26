<?php

declare(strict_types=1);

namespace Netgen\Layouts\RemoteMedia\ContentBrowser\Item\ColumnProvider\Image;

use Netgen\ContentBrowser\Item\ColumnProvider\ColumnValueProviderInterface;
use Netgen\ContentBrowser\Item\ItemInterface;

class Tags implements ColumnValueProviderInterface
{
    public function getValue(ItemInterface $item): ?string
    {
        /** @var $item \Netgen\Layouts\RemoteMedia\ContentBrowser\Item\Image\Item */
        if (!array_key_exists('tags', $item->getRemoteMediaValue()->metaData)) {
            return '';
        }

        return implode(', ', $item->getRemoteMediaValue()->metaData['tags']);
    }
}
