<?php

declare(strict_types=1);

namespace Netgen\Layouts\RemoteMedia\ContentBrowser\Item\Image;

use Netgen\ContentBrowser\Item\ItemInterface;
use Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Value;

final class Item implements ItemInterface
{
    /**
     * @var \Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Value
     */
    private $value;

    public function __construct(Value $value)
    {
        $this->value = $value;
    }

    public function getValue()
    {
        return $this->value->resourceId;
    }

    public function getName(): string
    {
        return (string) $this->value->resourceId;
    }

    public function isVisible(): bool
    {
        return true;
    }

    public function isSelectable(): bool
    {
        return true;
    }

    /**
     * @return \Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Value
     */
    public function getRemoteMediaValue(): Value
    {
        return $this->value;
    }
}
