<?php

declare(strict_types=1);

namespace Netgen\Bundle\LayoutsRemoteMediaBundle\Tests\DependencyInjection;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use Netgen\Bundle\LayoutsRemoteMediaBundle\DependencyInjection\NetgenLayoutsRemoteMediaExtension;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(NetgenLayoutsRemoteMediaExtension::class)]
final class NetgenLayoutsRemoteMediaExtensionTest extends AbstractExtensionTestCase
{
    public function testItSetsValidContainerParameters(): void
    {
        $this->setParameter('kernel.bundles', []);
        $this->load();

        $this->assertContainerBuilderHasParameter('netgen_layouts.remote_media.cache.pool_name', 'cache.redis');
        $this->assertContainerBuilderHasParameter('netgen_layouts.remote_media.cache.ttl', 4800);
    }

    protected function getContainerExtensions(): array
    {
        return [
            new NetgenLayoutsRemoteMediaExtension(),
        ];
    }

    /**
     * @return array<string,array<string, mixed>>
     */
    protected function getMinimalConfiguration(): array
    {
        return [
            'cache' => [
                'pool' => 'cache.redis',
                'ttl' => 4800,
            ],
        ];
    }
}
