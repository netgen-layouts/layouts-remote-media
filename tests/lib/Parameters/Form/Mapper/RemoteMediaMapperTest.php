<?php

declare(strict_types=1);

namespace Netgen\Layouts\RemoteMedia\Tests\Parameters\Form\Mapper;

use Netgen\ContentBrowser\Form\Type\ContentBrowserType;
use Netgen\Layouts\Parameters\ParameterDefinition;
use Netgen\Layouts\RemoteMedia\Parameters\Form\Mapper\RemoteMediaMapper;
use Netgen\Layouts\RemoteMedia\Parameters\ParameterType\RemoteMediaType as ParameterType;
use PHPUnit\Framework\TestCase;

final class RemoteMediaMapperTest extends TestCase
{
    private RemoteMediaMapper $mapper;

    protected function setUp(): void
    {
        $this->mapper = new RemoteMediaMapper();
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\Parameters\Form\Mapper\RemoteMediaMapper::getFormType
     */
    public function testGetFormType(): void
    {
        self::assertSame(ContentBrowserType::class, $this->mapper->getFormType());
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\Parameters\Form\Mapper\RemoteMediaMapper::mapOptions
     */
    public function testMapOptions(): void
    {
        self::assertSame(
            [
                'item_type' => 'remote_media',
                'required' => false,
                'custom_params' => [
                    'allowed_types' => [],
                ],
            ],
            $this->mapper->mapOptions(ParameterDefinition::fromArray(
                [
                    'type' => new ParameterType(),
                    'isRequired' => false,
                ],
            )),
        );
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\Parameters\Form\Mapper\RemoteMediaMapper::mapOptions
     */
    public function testMapOptionsWithFilter(): void
    {
        self::assertSame(
            [
                'item_type' => 'remote_media',
                'required' => false,
                'custom_params' => [
                    'allowed_types' => ['image', 'video'],
                ],
            ],
            $this->mapper->mapOptions(ParameterDefinition::fromArray(
                [
                    'type' => new ParameterType(),
                    'isRequired' => false,
                    'options' => [
                        'allowed_types' => ['image', 'video'],
                    ],
                ],
            )),
        );
    }
}
