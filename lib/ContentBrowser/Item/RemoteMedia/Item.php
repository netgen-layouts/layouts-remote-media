<?php

declare(strict_types=1);

namespace Netgen\Layouts\RemoteMedia\ContentBrowser\Item\RemoteMedia;

use Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Value;
use Netgen\ContentBrowser\Item\ItemInterface;
use function array_pop;
use function explode;
use function str_replace;

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
        return $this->getResourceType() . '|' . str_replace('/', '|', $this->value->resourceId);
    }

    public function getName(): string
    {
        $parts = explode('/', $this->value->resourceId);

        return array_pop($parts);
    }

    public function isVisible(): bool
    {
        return true;
    }

    public function isSelectable(): bool
    {
        return true;
    }

    public function getResourceType(): string
    {
        if (array_key_exists('resource_type', $this->value->metaData)) {
            return $this->value->metaData['resource_type'];
        }

        return 'image';
    }

    public function getRemoteMediaValue(): Value
    {
        return $this->value;
    }
}
