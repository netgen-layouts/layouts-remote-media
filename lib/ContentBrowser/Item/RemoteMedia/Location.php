<?php

declare(strict_types=1);

namespace Netgen\Layouts\RemoteMedia\ContentBrowser\Item\RemoteMedia;

use InvalidArgumentException;
use Netgen\ContentBrowser\Item\LocationInterface;
use Netgen\RemoteMedia\API\Values\Folder;
use Netgen\RemoteMedia\API\Values\RemoteResource;

use function array_pop;
use function array_shift;
use function count;
use function explode;
use function implode;
use function in_array;
use function sprintf;
use function str_replace;

final class Location implements LocationInterface
{
    public const string RESOURCE_TYPE_ALL = 'all';

    public const array SUPPORTED_TYPES = [
        RemoteResource::TYPE_IMAGE,
        RemoteResource::TYPE_AUDIO,
        RemoteResource::TYPE_VIDEO,
        RemoteResource::TYPE_DOCUMENT,
        RemoteResource::TYPE_OTHER,
    ];

    public string $name {
        get {
            if ($this->entryName !== null) {
                return $this->entryName;
            }

            $idParts = explode('||', $this->locationId);

            if (count($idParts) === 1) {
                return $this->locationId;
            }

            array_shift($idParts);
            $folderPath = array_shift($idParts);
            $pathArray = explode('|', $folderPath ?? '|');

            return array_pop($pathArray);
        }
    }

    public ?string $parentId {
        get {
            $folder = $this->folder;
            if (!$folder instanceof Folder) {
                return null;
            }

            $parent = $folder->getParent();
            if (!$parent instanceof Folder) {
                return $this->type;
            }

            return self::createFromFolder($parent, $this->type)->locationId;
        }
    }

    public ?Folder $folder {
        get {
            $idParts = explode('||', $this->locationId);

            if (count($idParts) <= 1) {
                return null;
            }

            return Folder::fromPath(str_replace('|', '/', $idParts[1]));
        }
    }

    public string $type {
        get {
            $idParts = explode('||', $this->locationId);

            return array_shift($idParts);
        }
    }

    private function __construct(
        private(set) string $locationId,
        private ?string $entryName = null,
    ) {
        $this->validateId($this->locationId);
    }

    public static function createFromId(string $id): self
    {
        return new self($id);
    }

    public static function createFromFolder(Folder $folder, string $type = self::RESOURCE_TYPE_ALL): self
    {
        $folders = explode('/', $folder->getPath());
        $id = $type . '||' . implode('|', $folders);

        return new self($id, $folder->getName());
    }

    public static function createAsSection(string $type, ?string $sectionName = null, ?string $folder = null): self
    {
        return new self(
            $folder !== null
                ? sprintf('%s||%s', $type, str_replace('/', '|', $folder))
                : $type,
            $sectionName ?? $type,
        );
    }

    private function validateId(string $id): void
    {
        $idParts = explode('||', $id);
        $type = array_shift($idParts);

        if ($type !== self::RESOURCE_TYPE_ALL && !in_array($type, self::SUPPORTED_TYPES, true)) {
            throw new InvalidArgumentException('Provided ID ' . $id . ' is invalid');
        }
    }
}
