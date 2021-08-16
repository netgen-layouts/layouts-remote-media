<?php

declare(strict_types=1);

namespace Netgen\Layouts\RemoteMedia\Tests\Stubs;

use Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Value as RemoteMediaValue;

final class RemoteMedia extends RemoteMediaValue
{
    public function __construct(
        string $resourceId,
        string $resourceType = 'image',
        string $type = 'upload'
    ) {
        parent::__construct([
            'resourceId' => $resourceId,
            'resourceType' => $resourceType,
            'type' => $type,
        ]);
    }
}
