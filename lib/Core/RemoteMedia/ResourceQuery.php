<?php

declare(strict_types=1);

namespace Netgen\Layouts\RemoteMedia\Core\RemoteMedia;

use Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Value;

final class ResourceQuery
{
    /**
     * @var string
     */
    public $resourceId;

    /**
     * @var string
     */
    public $resourceType;

    public static function createFromString(string $input)
    {
        $parts = explode('|', $input);

        $query = new ResourceQuery();
        $query->resourceType = array_shift($parts);
        $query->resourceId = implode('/', $parts);

        return $query;
    }
}
