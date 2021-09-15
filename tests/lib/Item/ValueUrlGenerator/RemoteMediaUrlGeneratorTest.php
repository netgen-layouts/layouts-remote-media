<?php

declare(strict_types=1);

namespace Netgen\Layouts\RemoteMedia\Tests\Item\ValueUrlGenerator;

use Netgen\Layouts\RemoteMedia\Item\ValueUrlGenerator\RemoteMediaValueUrlGenerator;
use Netgen\RemoteMedia\API\Values\RemoteResource;
use PHPUnit\Framework\TestCase;

final class RemoteMediaUrlGeneratorTest extends TestCase
{
    private RemoteMediaValueUrlGenerator $urlGenerator;

    protected function setUp(): void
    {
        $this->urlGenerator = new RemoteMediaValueUrlGenerator();
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\Item\ValueUrlGenerator\RemoteMediaValueUrlGenerator::generate
     */
    public function testGenerate(): void
    {
        $resource = RemoteResource::createFromParameters([
            'resourceId' => 'folder/test_resource',
            'resourceType' => 'video',
        ]);
        $resource->secure_url = 'https://cloudinary.com/test/folder/test_resource';

        self::assertSame('https://cloudinary.com/test/folder/test_resource', $this->urlGenerator->generate($resource));
    }
}
