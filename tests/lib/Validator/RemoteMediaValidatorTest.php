<?php

declare(strict_types=1);

namespace Netgen\Layouts\RemoteMedia\Tests\Validator;

use Cloudinary\Api\NotFound as CloudinaryNotFoundException;
use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\RemoteMediaProvider;
use Netgen\Layouts\RemoteMedia\Tests\Stubs\RemoteMedia as RemoteMediaStub;
use Netgen\Layouts\RemoteMedia\Validator\Constraint\RemoteMedia;
use Netgen\Layouts\RemoteMedia\Validator\RemoteMediaValidator;
use Netgen\Layouts\Tests\TestCase\ValidatorTestCase;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

final class RemoteMediaValidatorTest extends ValidatorTestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $provider;

    protected function setUp(): void
    {
        parent::setUp();

        $this->constraint = new RemoteMedia();
    }

    public function getValidator(): ConstraintValidatorInterface
    {
        $this->provider = $this->createMock(RemoteMediaProvider::class);

        return new RemoteMediaValidator($this->provider);
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\Validator\RemoteMediaValidator::__construct
     * @covers \Netgen\Layouts\RemoteMedia\Validator\RemoteMediaValidator::validate
     */
    public function testValidateValid(): void
    {
        $this->provider
            ->expects(self::once())
            ->method('getRemoteResource')
            ->with(self::identicalTo('folder/test_resource'), self::identicalTo('image'))
            ->willReturn(new RemoteMediaStub('folder/test_resource'));

        $this->assertValid(true, 'image|folder|test_resource');
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\Validator\RemoteMediaValidator::__construct
     * @covers \Netgen\Layouts\RemoteMedia\Validator\RemoteMediaValidator::validate
     */
    public function testValidateNull(): void
    {
        $this->provider
            ->expects(self::never())
            ->method('getRemoteResource');

        $this->assertValid(true, null);
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\Validator\RemoteMediaValidator::__construct
     * @covers \Netgen\Layouts\RemoteMedia\Validator\RemoteMediaValidator::validate
     */
    public function testValidateNonExisting(): void
    {
        $this->provider
            ->expects(self::once())
            ->method('getRemoteResource')
            ->with(self::identicalTo('folder/test_resource'), self::identicalTo('image'))
            ->willThrowException(new CloudinaryNotFoundException());

        $this->assertValid(false, 'image|folder|test_resource');
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\Validator\RemoteMediaValidator::validate
     */
    public function testValidateThrowsUnexpectedTypeExceptionWithInvalidConstraint(): void
    {
        $this->expectException(UnexpectedTypeException::class);
        $this->expectExceptionMessage('Expected argument of type "Netgen\Layouts\RemoteMedia\Validator\Constraint\RemoteMedia", "Symfony\Component\Validator\Constraints\NotBlank" given');

        $this->constraint = new NotBlank();
        $this->assertValid(true, 'value');
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\Validator\RemoteMediaValidator::validate
     */
    public function testValidateThrowsUnexpectedTypeExceptionWithInvalidValue(): void
    {
        $this->expectException(UnexpectedTypeException::class);
        $this->expectExceptionMessage('Expected argument of type "string", "array" given');

        $this->assertValid(true, []);
    }
}
