<?php

declare(strict_types=1);

namespace Netgen\Layouts\RemoteMedia\Item\ValueUrlGenerator;

use Netgen\Layouts\Item\ValueUrlGeneratorInterface;

/**
 * @implements \Netgen\Layouts\Item\ValueUrlGeneratorInterface<\Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Value>
 */
class ImageValueUrlGenerator implements ValueUrlGeneratorInterface
{
    public function generate(object $object): ?string
    {
        return $object->secure_url;
    }
}
