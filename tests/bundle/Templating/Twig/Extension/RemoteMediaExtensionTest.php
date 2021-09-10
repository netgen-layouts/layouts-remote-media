<?php

declare(strict_types=1);

namespace Netgen\Bundle\LayoutsRemoteMediaBundle\Tests\Templating\Twig\Extension;

use Netgen\Bundle\LayoutsRemoteMediaBundle\Templating\Twig\Extension\RemoteMediaExtension;
use PHPUnit\Framework\TestCase;
use Twig\TwigFunction;

final class RemoteMediaExtensionTest extends TestCase
{
    private RemoteMediaExtension $extension;

    protected function setUp(): void
    {
        $this->extension = new RemoteMediaExtension();
    }

    /**
     * @covers \Netgen\Bundle\LayoutsRemoteMediaBundle\Templating\Twig\Extension\RemoteMediaExtension::getFunctions
     */
    public function testGetFunctions(): void
    {
        self::assertNotEmpty($this->extension->getFunctions());
        self::assertContainsOnlyInstancesOf(TwigFunction::class, $this->extension->getFunctions());
    }
}
