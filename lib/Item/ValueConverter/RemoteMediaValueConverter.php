<?php

declare(strict_types=1);

namespace Netgen\Layouts\RemoteMedia\Item\ValueConverter;

use Netgen\Layouts\Item\ValueConverterInterface;
use Netgen\RemoteMedia\API\Values\RemoteResource;
use function array_pop;
use function explode;

/**
 * @implements \Netgen\Layouts\Item\ValueConverterInterface<\Netgen\RemoteMedia\API\Values\RemoteResource>
 */
final class RemoteMediaValueConverter implements ValueConverterInterface
{
    public function supports(object $object): bool
    {
        return $object instanceof RemoteResource;
    }

    public function getValueType(object $object): string
    {
        return 'remote_media';
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
        $parts = explode('/', $object->resourceId);

        return array_pop($parts);
    }

    public function getIsVisible(object $object): bool
    {
        return true;
    }

    public function getObject(object $object): RemoteResource
    {
        return $object;
    }
}
