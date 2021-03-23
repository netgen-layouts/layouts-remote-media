<?php

declare(strict_types=1);

namespace Netgen\Layouts\RemoteMedia\ContentBrowser\Item\RemoteMedia;

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

    /**
     * @var string[]
     */
    private $supportedTypes = [
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
     * @var string|null
     */
    private $name;

    /**
     * @var string
     */
    private $folder;

    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $resourceType;

    /**
     * @var string|null
     */
    private $parentId;

    public function __construct(string $id, ?string $name = null)
    {
        $this->id = $id;
        $this->name = $name;

        $idParts = explode('|', $id);

        if ($idParts[0] === self::TYPE_SECTION) {
            $this->type = self::TYPE_SECTION;

            $this->resourceType = self::RESOURCE_TYPE_ALL;
            if (in_array($idParts[1], $this->supportedTypes, true)) {
                $this->resourceType = $idParts[1];
            }

            return;
        }

        if ($idParts[0] === self::TYPE_FOLDER) {
            $this->type = self::TYPE_FOLDER;

            $this->resourceType = self::RESOURCE_TYPE_ALL;
            if (in_array($idParts[1], $this->supportedTypes, true)) {
                $this->resourceType = $idParts[1];
            }

            $this->folder = $idParts[2];
            $this->parentId = self::TYPE_SECTION . '|' . $this->resourceType;
        }
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
        $supportedTypes = [
            self::RESOURCE_TYPE_ALL,
            self::RESOURCE_TYPE_IMAGE,
            self::RESOURCE_TYPE_VIDEO,
            self::RESOURCE_TYPE_RAW,
        ];

        return $this->resourceType;
    }
}
