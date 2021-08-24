<?php

declare(strict_types=1);

namespace Netgen\Bundle\LayoutsRemoteMediaBundle\Templating\Twig\Runtime;

use Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Value;
use Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Variation;
use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\RemoteMediaProvider;
use Twig\Extension\AbstractExtension;

final class RemoteMediaRuntime extends AbstractExtension
{
    /**
     * @var \Netgen\Bundle\RemoteMediaBundle\RemoteMedia\RemoteMediaProvider
     */
    protected $provider;

    public function __construct(RemoteMediaProvider $provider)
    {
        $this->provider = $provider;
    }

    public function getBlockVariation(Value $value, string $variation, bool $secure = true): Variation
    {
        return $this->provider->buildVariation($value, 'netgen_layouts_block', $variation, $secure);
    }

    public function getRemoteVideoTagEmbed(Value $value, ?string $variation = null): string
    {
        return $this->provider->generateVideoTag($value, 'netgen_layouts_block', $variation ?: '');
    }
}
