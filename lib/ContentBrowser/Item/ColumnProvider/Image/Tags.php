<?php

declare(strict_types=1);

namespace Netgen\Layouts\RemoteMedia\ContentBrowser\Item\ColumnProvider\Image;

use Netgen\ContentBrowser\Item\ColumnProvider\ColumnValueProviderInterface;
use Netgen\ContentBrowser\Item\ItemInterface;
use function array_key_exists;
use function implode;

final class Tags implements ColumnValueProviderInterface
{
    public function getValue(ItemInterface $item): ?string
    {
        /** @var \Netgen\Layouts\RemoteMedia\ContentBrowser\Item\Image\Item $item */
        if (!array_key_exists('tags', $item->getRemoteMediaValue()->metaData)) {
            return '';
        }

        return implode(', ', $item->getRemoteMediaValue()->metaData['tags']);
    }
}
