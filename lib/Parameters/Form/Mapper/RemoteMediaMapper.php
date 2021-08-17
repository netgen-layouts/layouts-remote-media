<?php

declare(strict_types=1);

namespace Netgen\Layouts\RemoteMedia\Parameters\Form\Mapper;

use Netgen\ContentBrowser\Form\Type\ContentBrowserType;
use Netgen\Layouts\Parameters\Form\Mapper;
use Netgen\Layouts\Parameters\ParameterDefinition;

final class RemoteMediaMapper extends Mapper
{
    public function getFormType(): string
    {
        return ContentBrowserType::class;
    }

    public function mapOptions(ParameterDefinition $parameterDefinition): array
    {
        return [
            'item_type' => 'remote_media',
            'required' => $parameterDefinition->isRequired(),
            'custom_params' => [
                'allowed_types' => $parameterDefinition->hasOption('allowed_types')
                    ? $parameterDefinition->getOption('allowed_types')
                    : [],
            ],
        ];
    }
}
