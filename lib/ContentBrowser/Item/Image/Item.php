<?php

declare(strict_types=1);

namespace Netgen\Layouts\RemoteMedia\ContentBrowser\Item\Image;

use Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Value;
use Netgen\ContentBrowser\Item\ItemInterface;

final class Item implements ItemInterface
{
    /**
     * @var \Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Value
     */
    private $value;

    /**
     * @var string|null
     */
    private $resourceId;

    public function __construct(Value $value, ?string $resourceId)
    {
        $this->value = $value;
        $this->resourceId = $resourceId;
    }

    public function getValue()
    {
        return $this->resourceId;
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

    public function getRemoteMediaValue(): Value
    {
        return $this->value;
    }
}
