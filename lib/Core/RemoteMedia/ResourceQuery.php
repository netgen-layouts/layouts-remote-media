<?php

declare(strict_types=1);

namespace Netgen\Layouts\RemoteMedia\Core\RemoteMedia;

use function array_shift;
use function explode;
use function implode;

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

        $query = new self();
        $query->resourceType = array_shift($parts);
        $query->resourceId = implode('/', $parts);

        return $query;
    }
}
