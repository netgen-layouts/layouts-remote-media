<?php

declare(strict_types=1);

namespace Netgen\Layouts\RemoteMedia\Tests\Core\RemoteMedia\Resolver;

use Netgen\Layouts\RemoteMedia\Core\RemoteMedia\Resolver\NextCursor;
use Netgen\RemoteMedia\API\Search\Query;
use Netgen\RemoteMedia\API\Values\Folder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use RuntimeException;

use function sprintf;

#[CoversClass(NextCursor::class)]
final class NextCursorTest extends TestCase
{
    private const int CACHE_TTL = 3600;

    private const string TEST_CACHE_KEY = 'layoutsremotemedia-cloudinary-nextcursor-test __ ble __ __ a _test$|15||image|test_folder||some tag||||created_at=desc-30';

    private const string TEST_CURSOR = 'k84jh71osdf355asder';

    private MockObject&CacheItemPoolInterface $cache;

    private NextCursor $resolver;

    protected function setUp(): void
    {
        $this->cache = $this->createMock(CacheItemPoolInterface::class);

        $this->resolver = new NextCursor($this->cache, self::CACHE_TTL);
    }

    public function testResolve(): void
    {
        $cacheItemMock = $this->createMock(CacheItemInterface::class);

        $this->cache
            ->expects($this->once())
            ->method('getItem')
            ->with(self::TEST_CACHE_KEY)
            ->willReturn($cacheItemMock);

        $cacheItemMock
            ->expects($this->once())
            ->method('isHit')
            ->willReturn(true);

        $cacheItemMock
            ->expects($this->once())
            ->method('get')
            ->willReturn(self::TEST_CURSOR);

        self::assertSame(self::TEST_CURSOR, $this->resolver->resolve($this->getQuery(), 30));
    }

    public function testResolveWithoutMatch(): void
    {
        $cacheItemMock = $this->createMock(CacheItemInterface::class);

        $this->cache
            ->expects($this->once())
            ->method('getItem')
            ->with(self::TEST_CACHE_KEY)
            ->willReturn($cacheItemMock);

        $cacheItemMock
            ->expects($this->once())
            ->method('isHit')
            ->willReturn(false);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(sprintf("Can't get cursor key for query: %s with offset: 30", $this->getQuery()));

        $this->resolver->resolve($this->getQuery(), 30);
    }

    public function testSave(): void
    {
        $cacheItemMock = $this->createMock(CacheItemInterface::class);

        $this->cache
            ->expects($this->once())
            ->method('getItem')
            ->with(self::TEST_CACHE_KEY)
            ->willReturn($cacheItemMock);

        $cacheItemMock
            ->expects($this->once())
            ->method('set')
            ->with(self::TEST_CURSOR);

        $cacheItemMock
            ->expects($this->once())
            ->method('expiresAfter')
            ->with(self::CACHE_TTL);

        $this->resolver->save($this->getQuery(), 30, self::TEST_CURSOR);
    }

    private function getQuery(): Query
    {
        return new Query(
            query: 'test {} ble () /\ a @test$',
            types: ['image'],
            folders: [Folder::fromPath('test_folder')],
            tags: ['some tag'],
            limit: 15,
        );
    }
}
