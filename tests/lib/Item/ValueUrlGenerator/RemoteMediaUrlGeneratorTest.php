<?php

declare(strict_types=1);

namespace Netgen\Layouts\RemoteMedia\Tests\Item\ValueUrlGenerator;

use Netgen\Layouts\RemoteMedia\Item\ValueUrlGenerator\RemoteMediaValueUrlGenerator;
use Netgen\Layouts\RemoteMedia\Tests\Stubs\RemoteMedia as RemoteMediaStub;
use PHPUnit\Framework\TestCase;

final class RemoteMediaUrlGeneratorTest extends TestCase
{
    /**
     * @var \Netgen\Layouts\RemoteMedia\Item\ValueUrlGenerator\RemoteMediaValueUrlGenerator
     */
    private $urlGenerator;

    protected function setUp(): void
    {
        $this->urlGenerator = new RemoteMediaValueUrlGenerator();
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\Item\ValueUrlGenerator\RemoteMediaValueUrlGenerator::__construct
     * @covers \Netgen\Layouts\RemoteMedia\Item\ValueUrlGenerator\RemoteMediaValueUrlGenerator::generate
     */
    public function testGenerate(): void
    {
        $resource = new RemoteMediaStub('folder/test_resource', 'video');
        $resource->secure_url = 'https://cloudinary.com/test/folder/test_resource';

        self::assertSame('https://cloudinary.com/test/folder/test_resource', $this->urlGenerator->generate($resource));
    }
}
