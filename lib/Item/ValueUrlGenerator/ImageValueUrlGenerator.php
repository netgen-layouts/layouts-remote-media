<?php

declare(strict_types=1);

namespace Netgen\Layouts\RemoteMedia\Item\ValueUrlGenerator;

use Netgen\Layouts\Item\ValueUrlGeneratorInterface;

class ImageValueUrlGenerator implements ValueUrlGeneratorInterface
{
    public function generate(object $object): ?string
    {
        /* @var $object \Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Value */
        return $object->secure_url;
    }
}
