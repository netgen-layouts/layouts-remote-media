<?php

declare(strict_types=1);

namespace Netgen\Bundle\LayoutsRemoteMediaBundle\Tests\DependencyInjection\CompilerPass;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractContainerBuilderTestCase;
use Netgen\Bundle\LayoutsRemoteMediaBundle\DependencyInjection\CompilerPass\CachePoolPass;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\DependencyInjection\Definition;

#[CoversClass(CachePoolPass::class)]
final class CachePoolPassTest extends AbstractContainerBuilderTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->container->addCompilerPass(new CachePoolPass());
    }

    public function testProcess(): void
    {
        $this->setDefinition('cache.app', new Definition());
        $this->setParameter('netgen_layouts.remote_media.cache.pool_name', 'cache.app');

        $this->compile();

        $this->assertContainerBuilderHasService(
            'netgen_layouts.remote_media.cache.pool',
        );
    }

    public function testProcessWithoutParameter(): void
    {
        $this->compile();

        $this->assertContainerBuilderNotHasService(
            'netgen_layouts.remote_media.cache.pool',
        );
    }
}
