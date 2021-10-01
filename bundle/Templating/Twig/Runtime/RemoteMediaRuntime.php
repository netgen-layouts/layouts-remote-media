<?php

declare(strict_types=1);

namespace Netgen\Bundle\LayoutsRemoteMediaBundle\Templating\Twig\Runtime;

use Netgen\RemoteMedia\API\Values\RemoteResource;
use Netgen\RemoteMedia\API\Values\Variation;
use Netgen\RemoteMedia\Core\RemoteMediaProvider;
use Twig\Extension\AbstractExtension;

final class RemoteMediaRuntime extends AbstractExtension
{
    protected RemoteMediaProvider $provider;

    public function __construct(RemoteMediaProvider $provider)
    {
        $this->provider = $provider;
    }

    public function getBlockVariation(RemoteResource $resource, string $variation, bool $secure = true): Variation
    {
        return $this->provider->buildVariation($resource, 'netgen_layouts_block', $variation, $secure);
    }

    public function getItemVariation(RemoteResource $resource, string $variation, bool $secure = true): Variation
    {
        return $this->provider->buildVariation($resource, 'netgen_layouts_item', $variation, $secure);
    }

    public function getBlockVideoTag(RemoteResource $resource, ?string $variation = null): string
    {
        return $this->provider->generateVideoTag($resource, 'netgen_layouts_block', $variation ?? '');
    }
}
