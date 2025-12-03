<?php

declare(strict_types=1);

namespace Netgen\Layouts\RemoteMedia\API\Values;

use Netgen\RemoteMedia\API\Values\RemoteResourceLocation;
use Netgen\RemoteMedia\API\Values\TimestampableTrait;

/**
 * @final
 */
class RemoteMediaItem
{
    use TimestampableTrait;

    private ?int $id = null;

    public function __construct(
        private string $value,
        private RemoteResourceLocation $remoteResourceLocation,
    ) {}

    final public function getId(): ?int
    {
        return $this->id;
    }

    final public function getValue(): string
    {
        return $this->value;
    }

    final public function getRemoteResourceLocation(): RemoteResourceLocation
    {
        return $this->remoteResourceLocation;
    }
}
