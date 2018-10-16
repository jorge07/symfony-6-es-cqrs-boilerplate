<?php

declare(strict_types=1);

namespace App\Tests\Domain\User\Factory;

use App\Domain\User\Exception\EmailAlreadyExistException;
use App\Domain\User\Factory\UserFactory;
use App\Domain\User\Repository\CheckUserByEmailInterface;
use App\Domain\User\ValueObject\Auth\Credentials;
use App\Domain\User\ValueObject\Auth\HashedPassword;
use App\Domain\User\ValueObject\Email;
use App\Domain\User\ValueObject\Uuid;
use PHPUnit\Framework\TestCase;

class UserFactoryTest extends TestCase implements CheckUserByEmailInterface
{
    /**
     * @test
     *
     * @group unit
     *
     * @throws \Exception
     * @throws \Assert\AssertionFailedException
     */
    public function user_factory_should_create_user_when_email_not_exist()
    {
        $factory = new UserFactory($this);

        $uuid = Uuid::uuid4();
        $email = Email::fromString('as@as.as');

        $user = $factory->register($uuid, new Credentials($email, HashedPassword::encode('password')));

        self::assertSame($user->uuid(), $uuid->toString());
        self::assertSame($user->email(), $email->toString());
    }

    /**
     * @test
     *
     * @group unit
     *
     * @throws \Exception
     * @throws \Assert\AssertionFailedException
     */
    public function user_factory_must_throw_exception_is_email_already_taken()
    {
        $this->expectException(EmailAlreadyExistException::class);

        $this->emailExist = Uuid::uuid4();

        $factory = new UserFactory($this);

        $uuid = Uuid::uuid4();
        $email = Email::fromString('as@as.as');

        $factory->register($uuid, new Credentials($email, HashedPassword::encode('password')));
    }

    public function existsEmail(Email $email): ?Uuid
    {
        return $this->emailExist;
    }

    /** @var Uuid|null */
    private $emailExist;
}
