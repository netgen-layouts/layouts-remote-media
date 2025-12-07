<?php

declare(strict_types=1);

namespace Netgen\Layouts\RemoteMedia\Core\RemoteMedia;

use function array_map;
use function explode;
use function implode;
use function str_replace;

final class ResourceQuery
{
    public string $remoteId {
        get {
            $parts = array_map(
                static fn (string $part): string => str_replace('|', '/', $part),
                explode('||', $this->value),
            );

            return implode('|', $parts);
        }
    }

    private function __construct(
        public private(set) string $value,
    ) {}

    public static function createFromValue(string $value): self
    {
        return new self($value);
    }

    public static function createFromRemoteId(string $remoteId): self
    {
        $value = str_replace(['|', '/'], ['||', '|'], $remoteId);

        return new self($value);
    }
}
