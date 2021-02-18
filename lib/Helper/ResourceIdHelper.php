<?php

declare(strict_types=1);

namespace Netgen\Layouts\RemoteMedia\Helper;

use function str_replace;

final class ResourceIdHelper
{
    /**
     * Converts remote ID (which can be a path to file)
     * to ID that can be safely used.
     *
     * @param string $id
     *
     * @return string
     */
    public function fromRemoteId(string $id): string
    {
        return str_replace('/', '|', $id);
    }

    /**
     * Converts from internal ID to remote ID.
     *
     * @param string $id
     *
     * @return string
     */
    public function toRemoteId(string $id): string
    {
        return str_replace('|', '/', $id);
    }
}
