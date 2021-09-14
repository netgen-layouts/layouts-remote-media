<?php

declare(strict_types=1);

namespace Netgen\Layouts\RemoteMedia\Tests\Stubs;

use Netgen\RemoteMedia\API\Values\RemoteResource;

final class RemoteMedia extends RemoteResource
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
