<?php

declare(strict_types=1);

namespace Netgen\Layouts\RemoteMedia\Tests\ContentBrowser\Item\ColumnProvider\RemoteMedia;

use Netgen\Layouts\RemoteMedia\ContentBrowser\Item\ColumnProvider\RemoteMedia\Tags;
use Netgen\Layouts\RemoteMedia\ContentBrowser\Item\RemoteMedia\Item as RemoteMediaItem;
use Netgen\RemoteMedia\API\Values\RemoteResource;
use PHPUnit\Framework\TestCase;

final class TagsTest extends TestCase
{
    private Tags $tagsColumn;

    protected function setUp(): void
    {
        $this->tagsColumn = new Tags();
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Item\ColumnProvider\RemoteMedia\Tags::getValue
     */
    public function testGetValue(): void
    {
        $resource = RemoteResource::createFromParameters(['resourceId' => 'folder/test_resource']);
        $resource->metaData['tags'] = ['tag1', 'tag2', 'tag3'];

        $item = new RemoteMediaItem($resource);

        self::assertSame('tag1, tag2, tag3', $this->tagsColumn->getValue($item));
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Item\ColumnProvider\RemoteMedia\Tags::getValue
     */
    public function testGetValueWithMissingTagsKey(): void
    {
        $resource = RemoteResource::createFromParameters(['resourceId' => 'folder/test_resource']);
        unset($resource->metaData['tags']);

        $item = new RemoteMediaItem($resource);

        self::assertSame('', $this->tagsColumn->getValue($item));
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Item\ColumnProvider\RemoteMedia\Tags::getValue
     */
    public function testGetValueWithNoTags(): void
    {
        $resource = RemoteResource::createFromParameters(['resourceId' => 'folder/test_resource']);

        $item = new RemoteMediaItem($resource);

        self::assertSame('', $this->tagsColumn->getValue($item));
    }
}
