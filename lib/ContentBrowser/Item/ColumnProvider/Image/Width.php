<?php

declare(strict_types=1);

namespace Netgen\Layouts\RemoteMedia\ContentBrowser\Item\ColumnProvider\Image;

use Netgen\ContentBrowser\Item\ColumnProvider\ColumnValueProviderInterface;
use Netgen\ContentBrowser\Item\ItemInterface;
use Netgen\Layouts\RemoteMedia\ContentBrowser\Item\Image\Item;
use function array_key_exists;

final class Width implements ColumnValueProviderInterface
{
    public function getValue(ItemInterface $item): ?string
    {
        if (!$item instanceof Item) {
            return null;
        }

        if (!array_key_exists('width', $item->getRemoteMediaValue()->metaData)) {
            return '';
        }

        return (string) $item->getRemoteMediaValue()->metaData['width'];
    }
}
