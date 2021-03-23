<?php

declare(strict_types=1);

namespace Netgen\Layouts\RemoteMedia\ContentBrowser\Item\ColumnProvider\RemoteMedia;

use Netgen\ContentBrowser\Item\ColumnProvider\ColumnValueProviderInterface;
use Netgen\ContentBrowser\Item\ItemInterface;
use Netgen\Layouts\RemoteMedia\ContentBrowser\Item\RemoteMedia\Item;
use function array_key_exists;

final class Resolution implements ColumnValueProviderInterface
{
    public function getValue(ItemInterface $item): ?string
    {
        if (!$item instanceof Item) {
            return null;
        }

        if (!array_key_exists('width', $item->getRemoteMediaValue()->metaData)
            || !array_key_exists('height', $item->getRemoteMediaValue()->metaData)
            || ($item->getRemoteMediaValue()->metaData['width'] !== '' && empty($item->getRemoteMediaValue()->metaData['height']) !== '')) {
            return '';
        }

        return $item->getRemoteMediaValue()->metaData['width'] . 'x' . $item->getRemoteMediaValue()->metaData['height'];
    }
}
