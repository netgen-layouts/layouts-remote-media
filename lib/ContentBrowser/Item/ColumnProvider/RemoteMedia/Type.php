<?php

declare(strict_types=1);

namespace Netgen\Layouts\RemoteMedia\ContentBrowser\Item\ColumnProvider\RemoteMedia;

use Netgen\ContentBrowser\Item\ColumnProvider\ColumnValueProviderInterface;
use Netgen\ContentBrowser\Item\ItemInterface;
use Netgen\Layouts\RemoteMedia\ContentBrowser\Item\RemoteMedia\Item;

final class Type implements ColumnValueProviderInterface
{
    public function getValue(ItemInterface $item): ?string
    {
        if (!$item instanceof Item) {
            return null;
        }

        $value = $item->getRemoteMediaValue()->resourceType;
        $format = $item->getRemoteMediaValue()->metaData['format'] ?? '';

        if ($format !== '') {
            $value .= ' / ' . $item->getRemoteMediaValue()->metaData['format'];
        }

        return $value;
    }
}
