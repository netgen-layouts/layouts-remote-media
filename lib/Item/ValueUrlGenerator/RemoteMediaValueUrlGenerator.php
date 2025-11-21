<?php

declare(strict_types=1);

namespace Netgen\Layouts\RemoteMedia\Item\ValueUrlGenerator;

use Netgen\Layouts\Item\ValueUrlGeneratorInterface;

/**
 * @implements \Netgen\Layouts\Item\ValueUrlGeneratorInterface<\Netgen\RemoteMedia\API\Values\RemoteResourceLocation>
 */
final class RemoteMediaValueUrlGenerator implements ValueUrlGeneratorInterface
{
    public function generateDefaultUrl(object $object): ?string
    {
        return $object->getRemoteResource()->getUrl();
    }

    public function generateAdminUrl(object $object): ?string
    {
        return $object->getRemoteResource()->getUrl();
    }
}
