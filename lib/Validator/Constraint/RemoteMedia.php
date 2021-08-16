<?php

declare(strict_types=1);

namespace Netgen\Layouts\RemoteMedia\Validator\Constraint;

use Symfony\Component\Validator\Constraint;

final class RemoteMedia extends Constraint
{
    public string $message = 'netgen_remote_media.remote_media.resource_not_found';

    public function validatedBy(): string
    {
        return 'netgen_remote_media';
    }
}
