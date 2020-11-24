<?php

declare(strict_types=1);

namespace Netgen\Layouts\RemoteMedia\Item\ValueConverter;

use Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Value;
use Netgen\Layouts\Item\ValueConverterInterface;

class ImageValueConverter implements ValueConverterInterface
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
        /** @var $object \Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Value */
        return $object->resourceId;
    }

    public function getRemoteId(object $object)
    {
        /** @var $object \Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Value */
        return $object->resourceId;
    }

    public function getName(object $object): string
    {
        /** @var $object \Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Value */
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
