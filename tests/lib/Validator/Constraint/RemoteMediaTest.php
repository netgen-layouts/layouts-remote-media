<?php

declare(strict_types=1);

namespace Netgen\Layouts\RemoteMedia\Tests\Validator\Constraint;

use Netgen\Layouts\RemoteMedia\Validator\Constraint\RemoteMedia;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(RemoteMedia::class)]
final class RemoteMediaTest extends TestCase
{
    public function testValidatedBy(): void
    {
        $constraint = new RemoteMedia();
        self::assertSame('netgen_remote_media', $constraint->validatedBy());
    }
}
