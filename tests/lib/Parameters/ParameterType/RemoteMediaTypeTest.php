<?php

declare(strict_types=1);

namespace Netgen\Layouts\RemoteMedia\Tests\Parameters\ParameterType;

use Netgen\Layouts\Parameters\ParameterDefinition;
use Netgen\Layouts\RemoteMedia\Parameters\ParameterType\RemoteMediaType;
use Netgen\Layouts\RemoteMedia\Tests\Validator\RemoteMediaValidatorFactory;
use Netgen\Layouts\Tests\Parameters\ParameterType\ParameterTypeTestTrait;
use Netgen\RemoteMedia\API\ProviderInterface;
use Netgen\RemoteMedia\API\Values\RemoteResource;
use Netgen\RemoteMedia\Exception\RemoteResourceNotFoundException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\OptionsResolver\Exception\InvalidArgumentException;
use Symfony\Component\Validator\Validation;

#[CoversClass(RemoteMediaType::class)]
final class RemoteMediaTypeTest extends TestCase
{
    use ParameterTypeTestTrait;

    private MockObject&ProviderInterface $providerMock;

    protected function setUp(): void
    {
        $this->providerMock = $this->createMock(ProviderInterface::class);

        $this->type = new RemoteMediaType($this->providerMock);
    }

    public function testGetIdentifier(): void
    {
        self::assertSame('remote_media', $this->type::getIdentifier());
    }

    /**
     * @param mixed[] $options
     * @param mixed[] $resolvedOptions
     */
    #[DataProvider('validOptionsDataProvider')]
    public function testValidOptions(array $options, array $resolvedOptions): void
    {
        $parameter = $this->getParameterDefinition($options);
        self::assertSame($resolvedOptions, $parameter->options);
    }

    /**
     * @param mixed[] $options
     */
    #[DataProvider('invalidOptionsDataProvider')]
    public function testInvalidOptions(array $options): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->getParameterDefinition($options);
    }

    /**
     * Provider for testing valid parameter attributes.
     *
     * @return mixed[]
     */
    public static function validOptionsDataProvider(): iterable
    {
        return [
            [
                [],
                [
                    'allowed_types' => [],
                ],
            ],
            [
                [
                    'allowed_types' => ['image'],
                ],
                [
                    'allowed_types' => ['image'],
                ],
            ],
        ];
    }

    /**
     * Provider for testing invalid parameter attributes.
     *
     * @return mixed[]
     */
    public static function invalidOptionsDataProvider(): iterable
    {
        return [
            [
                [
                    'undefined_value' => 'Value',
                ],
            ],
        ];
    }

    public function testValidationValid(): void
    {
        $this->providerMock
            ->expects($this->once())
            ->method('loadFromRemote')
            ->with(self::identicalTo('upload|image|folder/test_resource'))
            ->willReturn(new RemoteResource(
                remoteId: 'upload|image|folder/test_resource',
                type: RemoteResource::TYPE_IMAGE,
                url: 'https://cloudinary.com/test/upload/folder/test_resource',
                md5: '5d7a812a020b40e23411edbc83cb809f',
            ));

        $parameter = $this->getParameterDefinition([], true);
        $validator = Validation::createValidatorBuilder()
            ->setConstraintValidatorFactory(new RemoteMediaValidatorFactory($this->providerMock))
            ->getValidator();

        $errors = $validator->validate(
            'upload||image||folder|test_resource',
            $this->type->getConstraints($parameter, 'upload||image||folder|test_resource'),
        );

        self::assertCount(0, $errors);
    }

    public function testValidationValidWithNonRequiredValue(): void
    {
        $this->providerMock
            ->expects($this->never())
            ->method('loadFromRemote');

        $parameter = $this->getParameterDefinition();
        $validator = Validation::createValidatorBuilder()
            ->setConstraintValidatorFactory(new RemoteMediaValidatorFactory($this->providerMock))
            ->getValidator();

        $errors = $validator->validate(null, $this->type->getConstraints($parameter, null));
        self::assertCount(0, $errors);
    }

    public function testValidationInvalid(): void
    {
        $this->providerMock
            ->expects($this->once())
            ->method('loadFromRemote')
            ->with(self::identicalTo('upload|image|folder/test_resource'))
            ->willThrowException(new RemoteResourceNotFoundException('upload|image|folder/test_resource'));

        $parameter = $this->getParameterDefinition([], true);
        $validator = Validation::createValidatorBuilder()
            ->setConstraintValidatorFactory(new RemoteMediaValidatorFactory($this->providerMock))
            ->getValidator();

        $errors = $validator->validate(
            'upload||image||folder|test_resource',
            $this->type->getConstraints($parameter, 'upload||image||folder|test_resource'),
        );

        self::assertNotCount(0, $errors);
    }

    #[DataProvider('emptyDataProvider')]
    public function testIsValueEmpty(mixed $value, bool $isEmpty): void
    {
        self::assertSame($isEmpty, $this->type->isValueEmpty(new ParameterDefinition(), $value));
    }

    /**
     * @return mixed[]
     */
    public static function emptyDataProvider(): iterable
    {
        return [
            [null, true],
            [
                new RemoteResource(
                    remoteId: 'upload|image|folder/test_resource',
                    type: RemoteResource::TYPE_IMAGE,
                    url: 'https://cloudinary.com/test/upload/folder/test_resource',
                    md5: 'f1b602d42f9760d1c658f780f12109df',
                ),
                false,
            ],
        ];
    }

    public function testGetValueObject(): void
    {
        $remoteResource = new RemoteResource(
            remoteId: 'upload|image|folder/test_resource',
            type: RemoteResource::TYPE_IMAGE,
            url: 'https://cloudinary.com/test/upload/folder/test_resource',
            md5: '5d7a812a020b40e23411edbc83cb809f',
        );

        $this->providerMock
            ->expects($this->once())
            ->method('loadFromRemote')
            ->with(self::identicalTo('upload|image|folder/test_resource'))
            ->willReturn($remoteResource);

        /** @var \Netgen\Layouts\RemoteMedia\Parameters\ParameterType\RemoteMediaType $type */
        $type = $this->type;

        self::assertSame($remoteResource, $type->getValueObject('upload||image||folder|test_resource'));
    }

    public function testGetValueObjectWithNoPage(): void
    {
        $this->providerMock
            ->expects($this->once())
            ->method('loadFromRemote')
            ->with(self::identicalTo('upload|image|folder/test_resource'))
            ->willThrowException(new RemoteResourceNotFoundException('upload|image|folder/test_resource'));

        /** @var \Netgen\Layouts\RemoteMedia\Parameters\ParameterType\RemoteMediaType $type */
        $type = $this->type;

        self::assertNull($type->getValueObject('upload||image||folder|test_resource'));
    }
}
