<?php

declare(strict_types=1);

namespace Netgen\Layouts\RemoteMedia\ContentBrowser\Item\ColumnProvider\RemoteMedia;

use Netgen\ContentBrowser\Item\ColumnProvider\ColumnValueProviderInterface;
use Netgen\ContentBrowser\Item\ItemInterface;
use Netgen\Layouts\RemoteMedia\ContentBrowser\Item\RemoteMedia\Item;
use function array_key_exists;

final class Type implements ColumnValueProviderInterface
{
    public function getValue(ItemInterface $item): ?string
    {
        if (!$item instanceof Item) {
            return null;
        }

        $value = '';
        if (!array_key_exists('resource_type', $item->getRemoteMediaValue()->metaData)) {
            return $value;
        }

        $value = $item->getRemoteMediaValue()->metaData['resource_type'];

        if (array_key_exists('format', $item->getRemoteMediaValue()->metaData)) {
            $value .= ' / ' . $item->getRemoteMediaValue()->metaData['format'];
        }

        return $value;
    }
}
