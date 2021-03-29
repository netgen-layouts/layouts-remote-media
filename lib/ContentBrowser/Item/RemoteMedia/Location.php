<?php

declare(strict_types=1);

namespace Netgen\Layouts\RemoteMedia\ContentBrowser\Item\RemoteMedia;

use InvalidArgumentException;
use Netgen\ContentBrowser\Item\LocationInterface;
use function array_pop;
use function array_shift;
use function array_slice;
use function count;
use function explode;
use function implode;
use function in_array;

final class Location implements LocationInterface
{
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
    private $resourceType;

    /**
     * @var string|null
     */
    private $folder;

    /**
     * @var string|null
     */
    private $parentId;

    private function __construct(
        string $id,
        string $name,
        string $resourceType,
        ?string $folder = null,
        ?string $parentId = null
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->resourceType = $resourceType;
        $this->folder = $folder;
        $this->parentId = $parentId;
    }

    public static function createFromId(string $id): self
    {
        $idParts = explode('|', $id);

        if (count($idParts) === 0) {
            throw new InvalidArgumentException('Provided ID ' . $id . ' is invalid');
        }

        $resourceType = array_shift($idParts);

        if (!in_array($resourceType, self::SUPPORTED_TYPES, true)) {
            throw new InvalidArgumentException('Provided ID ' . $id . ' is invalid');
        }

        $name = $resourceType;
        $folder = null;
        $parentId = null;

        if (count($idParts) > 0) {
            $folder = implode('/', $idParts);
            $name = array_pop($idParts);

            $parentId = count($idParts) > 0
                ? $resourceType . '|' . implode('|', $idParts)
                : $resourceType;
        }

        return new self($id, $name, $resourceType, $folder, $parentId);
    }

    public static function createAsSection(string $resourceType, ?string $sectionName = null)
    {
        if (!in_array($resourceType, self::SUPPORTED_TYPES, true)) {
            throw new InvalidArgumentException('Provided resource type ' . $resourceType . ' is invalid');
        }

        return new self(
            $resourceType,
            $sectionName ?: $resourceType,
            $resourceType
        );
    }

    public static function createFromFolder(string $folderPath, string $folderName, string $resourceType = self::RESOURCE_TYPE_ALL)
    {
        $folders = explode('/', $folderPath);
        $folder = implode('/', $folders);

        $id = $resourceType . '|' . implode('|', $folders);
        $parentId = $resourceType;

        if (count($folders) > 1) {
            $parentId .= '|' . implode('|', array_slice($folders, 0, -1));
        }

        return new self($id, $folderName, $resourceType, $folder, $parentId);
    }

    public function getLocationId()
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getParentId()
    {
        return $this->parentId;
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
