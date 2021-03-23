<?php

declare(strict_types=1);

namespace Netgen\Layouts\RemoteMedia\ContentBrowser\Item\RemoteMedia;

use InvalidArgumentException;
use Netgen\ContentBrowser\Item\LocationInterface;
use function explode;
use function in_array;

final class Location implements LocationInterface
{
    public const TYPE_SECTION = 'section';

    public const TYPE_FOLDER = 'folder';

    public const RESOURCE_TYPE_ALL = 'all';

    public const RESOURCE_TYPE_IMAGE = 'image';

    public const RESOURCE_TYPE_VIDEO = 'video';

    public const RESOURCE_TYPE_RAW = 'raw';

    public const SUPPORTED_TYPES = [
        self::RESOURCE_TYPE_ALL,
        self::RESOURCE_TYPE_IMAGE,
        self::RESOURCE_TYPE_VIDEO,
        self::RESOURCE_TYPE_RAW,
    ];

    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $resourceType;

    /**
     * @var string
     */
    private $folder;

    /**
     * @var string
     */
    private $parentId;

    private function __construct(
        string $id,
        string $name,
        string $type,
        string $resourceType,
        ?string $folder = null,
        ?string $parentId = null
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->type = $type;
        $this->resourceType = $resourceType;
        $this->folder = $folder;
        $this->parentId = $parentId;
    }

    public static function createFromId(string $id, ?string $name = null): self
    {
        $idParts = explode('|', $id);

        if ($idParts[0] === self::TYPE_SECTION) {
            $resourceType = self::RESOURCE_TYPE_ALL;
            if (in_array($idParts[1], self::SUPPORTED_TYPES, true)) {
                $resourceType = $idParts[1];
            }

            if ($name === null) {
                $name = $resourceType;
            }

            return new self($id, $name, self::TYPE_SECTION, $resourceType);
        }

        if ($idParts[0] === self::TYPE_FOLDER) {
            $resourceType = self::RESOURCE_TYPE_ALL;
            if (in_array($idParts[1], self::SUPPORTED_TYPES, true)) {
                $resourceType = $idParts[1];
            }

            $folder = $idParts[2];
            $parentId = self::TYPE_SECTION . '|' . $resourceType;

            if ($name === null) {
                $name = $folder;
            }

            return new self($id, $name, self::TYPE_FOLDER, $resourceType, $folder, $parentId);
        }

        throw new InvalidArgumentException('Provided ID ' . $id . ' is invalid');
    }

    public function getLocationId()
    {
        return $this->id;
    }

    public function getName(): string
    {
        if ($this->name !== null) {
            return $this->name;
        }

        return $this->isSection() ? $this->resourceType : $this->folder;
    }

    public function getParentId()
    {
        return $this->parentId;
    }

    public function isSection(): bool
    {
        return $this->type === self::TYPE_SECTION;
    }

    public function isFolder(): bool
    {
        return $this->type === self::TYPE_FOLDER;
    }

    public function getFolder(): ?string
    {
        return $this->folder;
    }

    public function getResourceType(): string
    {
        return $this->resourceType;
    }
}
