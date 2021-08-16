<?php

declare(strict_types=1);

namespace Netgen\Layouts\RemoteMedia\Validator;

use Cloudinary\Api\NotFound as CloudinaryNotFoundException;
use Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Value as RemoteMediaValue;
use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\RemoteMediaProvider;
use Netgen\Layouts\RemoteMedia\Core\RemoteMedia\ResourceQuery;
use Netgen\Layouts\RemoteMedia\Validator\Constraint\RemoteMedia;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use function is_string;

final class RemoteMediaValidator extends ConstraintValidator
{
    /**
     * @var \Netgen\Bundle\RemoteMediaBundle\RemoteMedia\RemoteMediaProvider
     */
    private $provider;

    public function __construct(RemoteMediaProvider $provider)
    {
        $this->provider = $provider;
    }

    public function validate($value, Constraint $constraint): void
    {
        if ($value === null) {
            return;
        }

        if (!$constraint instanceof RemoteMedia) {
            throw new UnexpectedTypeException($constraint, RemoteMedia::class);
        }

        if (!is_string($value)) {
            throw new UnexpectedTypeException($value, 'string');
        }

        $query = ResourceQuery::createFromString($value);

        try {
            $resource = $this->provider->getRemoteResource(
                $query->getResourceId(),
                $query->getResourceType(),
            );
        } catch (CloudinaryNotFoundException $e) {
            $resource = null;
        }

        if (!$resource instanceof RemoteMediaValue) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('%resourceId%', $query->getResourceId())
                ->setParameter('%resourceType%', $query->getResourceType())
                ->addViolation();
        }
    }
}
