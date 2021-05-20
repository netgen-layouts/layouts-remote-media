<?php

declare(strict_types=1);

namespace Netgen\Layouts\RemoteMedia\Tests\Validator;

use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\RemoteMediaProvider;
use Netgen\Layouts\RemoteMedia\Validator\RemoteMediaValidator;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidatorFactory;
use Symfony\Component\Validator\ConstraintValidatorFactoryInterface;

final class RemoteMediaValidatorFactory implements ConstraintValidatorFactoryInterface
{
    private RemoteMediaProvider $provider;

    private ConstraintValidatorFactory $baseValidatorFactory;

    public function __construct(RemoteMediaProvider $provider)
    {
        $this->provider = $provider;
        $this->baseValidatorFactory = new ConstraintValidatorFactory();
    }

    public function getInstance(Constraint $constraint)
    {
        $name = $constraint->validatedBy();

        if ($name === 'netgen_remote_media') {
            return new RemoteMediaValidator($this->provider);
        }

        return $this->baseValidatorFactory->getInstance($constraint);
    }
}
