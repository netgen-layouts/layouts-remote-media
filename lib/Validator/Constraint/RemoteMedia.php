<?php

declare(strict_types=1);

namespace Netgen\Layouts\RemoteMedia\Validator\Constraint;

use Symfony\Component\Validator\Attribute\HasNamedArguments;
use Symfony\Component\Validator\Constraint;

final class RemoteMedia extends Constraint
{
    #[HasNamedArguments]
    public function __construct(
        public string $message = 'netgen_remote_media.remote_media.resource_not_found',
        ?array $groups = null,
        mixed $payload = null,
    ) {
        parent::__construct(null, $groups, $payload);
    }

    public function validatedBy(): string
    {
        return 'netgen_remote_media';
    }
}
