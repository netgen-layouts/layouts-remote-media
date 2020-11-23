<?php

declare(strict_types=1);

namespace Netgen\Layouts\RemoteMedia\ContentBrowser\Item\Image;

use Netgen\ContentBrowser\Item\LocationInterface;

final class Location implements LocationInterface
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var null|string
     */
    protected $parentName;

    public function __construct(string $name, ?string $parentName = null)
    {
        $this->name = $name;
        $this->parentName = $parentName;
    }

    public function getLocationId()
    {
        return $this->name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getParentId()
    {
        return $this->parentName;
    }
}
