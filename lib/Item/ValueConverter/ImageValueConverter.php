<?php

declare(strict_types=1);

namespace Netgen\Layouts\RemoteMedia\Item\ValueConverter;

use Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Value;
use Netgen\Layouts\Item\ValueConverterInterface;

/**
 * @implements \Netgen\Layouts\Item\ValueConverterInterface<\Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Value>
 */
final class ImageValueConverter implements ValueConverterInterface
{
    public function supports(object $object): bool
    {
        return $object instanceof Value;
    }

    public function getValueType(object $object): string
    {
        return 'remote_media_image';
    }

    public function getId(object $object)
    {
        return $object->resourceId;
    }

    public function getRemoteId(object $object)
    {
        return $object->resourceId;
    }

    public function getName(object $object): string
    {
        return $object->resourceId;
    }

    public function getIsVisible(object $object): bool
    {
        return true;
    }

    public function getObject(object $object): object
    {
        return $object;
    }
}
