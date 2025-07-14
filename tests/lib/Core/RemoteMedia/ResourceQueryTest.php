<?php

declare(strict_types=1);

namespace Netgen\Layouts\RemoteMedia\Tests\Core\RemoteMedia;

use Netgen\Layouts\RemoteMedia\Core\RemoteMedia\ResourceQuery;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ResourceQuery::class)]
final class ResourceQueryTest extends TestCase
{
    public function testFromValue(): void
    {
        $resourceQuery = ResourceQuery::createFromValue('upload||image||folder|subfolder|resource.jpg');

        self::assertSame('upload||image||folder|subfolder|resource.jpg', $resourceQuery->getValue());
        self::assertSame('upload|image|folder/subfolder/resource.jpg', $resourceQuery->getRemoteId());
    }

    public function testFromRemoteId(): void
    {
        $resourceQuery = ResourceQuery::createFromRemoteId('upload|image|folder/subfolder/resource.jpg');

        self::assertSame('upload||image||folder|subfolder|resource.jpg', $resourceQuery->getValue());
        self::assertSame('upload|image|folder/subfolder/resource.jpg', $resourceQuery->getRemoteId());
    }
}
