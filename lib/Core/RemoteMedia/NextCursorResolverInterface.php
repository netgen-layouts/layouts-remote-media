<?php

declare(strict_types=1);

namespace Netgen\Layouts\RemoteMedia\Core\RemoteMedia;

use Netgen\RemoteMedia\API\Search\Query;

interface NextCursorResolverInterface
{
    final public const string PROJECT_KEY = 'layoutsremotemedia';

    final public const string PROVIDER_KEY = 'cloudinary';

    final public const string NEXT_CURSOR = 'nextcursor';

    public function resolve(Query $query, int $offset): string;

    public function save(Query $query, int $offset, string $cursor): void;
}
