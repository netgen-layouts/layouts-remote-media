<?php

declare(strict_types=1);

namespace Netgen\Layouts\RemoteMedia\Tests\TestCase;

use Netgen\Layouts\RemoteMedia\Tests\Validator\ValidatorFactory;
use Netgen\RemoteMedia\API\ProviderInterface;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

trait ValidatorTestCaseTrait
{
    private function createValidator(
        ?ProviderInterface $provider = null,
    ): ValidatorInterface {
        $provider ??= self::createStub(ProviderInterface::class);

        return Validation::createValidatorBuilder()
            ->setConstraintValidatorFactory(
                new ValidatorFactory($provider),
            )
            ->getValidator();
    }
}
