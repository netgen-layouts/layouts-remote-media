<?php

declare(strict_types=1);

namespace Netgen\Layouts\RemoteMedia\Parameters\ParameterType;

use Netgen\Layouts\Parameters\ParameterDefinition;
use Netgen\Layouts\Parameters\ParameterType;
use Netgen\Layouts\Parameters\ValueObjectProviderInterface;
use Netgen\Layouts\RemoteMedia\Core\RemoteMedia\ResourceQuery;
use Netgen\Layouts\RemoteMedia\Validator\Constraint\RemoteMedia as RemoteMediaConstraint;
use Netgen\RemoteMedia\API\ProviderInterface;
use Netgen\RemoteMedia\API\Values\RemoteResource;
use Netgen\RemoteMedia\Exception\RemoteResourceNotFoundException;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

/**
 * Parameter type used to store and validate an ID and type of resource in RemoteMedia.
 */
final class RemoteMediaType extends ParameterType implements ValueObjectProviderInterface
{
    public function __construct(
        private ProviderInterface $provider,
    ) {}

    public static function getIdentifier(): string
    {
        return 'remote_media';
    }

    public function configureOptions(OptionsResolver $optionsResolver): void
    {
        $optionsResolver
            ->define('allowed_types')
            ->required()
            ->default([])
            ->allowedTypes('array');
    }

    public function getValueObject(mixed $value): ?RemoteResource
    {
        $query = ResourceQuery::createFromValue($value);

        try {
            return $this->provider->loadFromRemote($query->remoteId);
        } catch (RemoteResourceNotFoundException) {
            return null;
        }
    }

    protected function getValueConstraints(ParameterDefinition $parameterDefinition, mixed $value): array
    {
        return [
            new Constraints\Type(type: 'string'),
            new RemoteMediaConstraint(),
        ];
    }
}
