<?php

declare(strict_types=1);

namespace Netgen\Layouts\RemoteMedia\Tests\Item\ValueConverter;

use Netgen\Layouts\RemoteMedia\Item\ValueConverter\RemoteMediaValueConverter;
use Netgen\RemoteMedia\API\Values\RemoteResource;
use PHPUnit\Framework\TestCase;
use stdClass;

final class RemoteMediaValueConverterTest extends TestCase
{
    private RemoteMediaValueConverter $valueConverter;

    protected function setUp(): void
    {
        $this->valueConverter = new RemoteMediaValueConverter();
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\Item\ValueConverter\RemoteMediaValueConverter::supports
     */
    public function testSupports(): void
    {
        self::assertTrue(
            $this->valueConverter->supports(
                RemoteResource::createFromParameters(['resourceId' => 'test_resource']),
            ),
        );

        self::assertFalse($this->valueConverter->supports(new stdClass()));
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\Item\ValueConverter\RemoteMediaValueConverter::getValueType
     */
    public function testGetValueType(): void
    {
        self::assertSame(
            'remote_media',
            $this->valueConverter->getValueType(
                RemoteResource::createFromParameters(['resourceId' => 'test_resource']),
            ),
        );
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\Item\ValueConverter\RemoteMediaValueConverter::getId
     */
    public function testGetId(): void
    {
        self::assertSame(
            'folder/test_resource',
            $this->valueConverter->getId(
                RemoteResource::createFromParameters(['resourceId' => 'folder/test_resource']),
            ),
        );
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\Item\ValueConverter\RemoteMediaValueConverter::getRemoteId
     */
    public function testGetRemoteId(): void
    {
        self::assertSame(
            'folder/test_resource',
            $this->valueConverter->getRemoteId(
                RemoteResource::createFromParameters(['resourceId' => 'folder/test_resource']),
            ),
        );
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\Item\ValueConverter\RemoteMediaValueConverter::getName
     */
    public function testGetName(): void
    {
        self::assertSame(
            'test_resource',
            $this->valueConverter->getName(
                RemoteResource::createFromParameters(['resourceId' => 'folder/test_resource']),
            ),
        );
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\Item\ValueConverter\RemoteMediaValueConverter::getIsVisible
     */
    public function testGetIsVisible(): void
    {
        self::assertTrue(
            $this->valueConverter->getIsVisible(
                RemoteResource::createFromParameters(['resourceId' => 'folder/test_resource']),
            ),
        );
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\Item\ValueConverter\RemoteMediaValueConverter::getObject
     */
    public function testGetObject(): void
    {
        $object = RemoteResource::createFromParameters(['resourceId' => 'folder/test_resource']);

        self::assertSame($object, $this->valueConverter->getObject($object));
    }
}
