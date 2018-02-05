<?php

declare(strict_types=1);

namespace App\Tests\Domain\User\Factory;

use App\Domain\User\Exception\EmailAlreadyExistException;
use App\Domain\User\Factory\UserFactory;
use App\Domain\User\Repository\UserCollectionInterface;
use App\Domain\User\ValueObject\Auth\Credentials;
use App\Domain\User\ValueObject\Auth\HashedPassword;
use App\Domain\User\ValueObject\Email;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class UserFactoryTest extends TestCase implements UserCollectionInterface
{
    /**
     * @test
     *
     * @group unit
     */
    public function user_factory_should_create_user_when_email_not_exist()
    {
        $factory = new UserFactory($this);

        $uuid = Uuid::uuid4();
        $email = Email::fromString('as@as.as');

        $user = $factory->register($uuid, new Credentials($email, HashedPassword::encode('password')));

        self::assertEquals($user->uuid(), $uuid->toString());
        self::assertEquals($user->email(), $email->toString());
    }

    /**
     * @test
     *
     * @group unit
     */
    public function user_factory_must_throw_exception_is_email_already_taken()
    {
        self::expectException(EmailAlreadyExistException::class);

        $this->emailExist = Uuid::uuid4();

        $factory = new UserFactory($this);

        $uuid = Uuid::uuid4();
        $email = Email::fromString('as@as.as');

        $factory->register($uuid, new Credentials($email, HashedPassword::encode('password')));
    }

    public function existsEmail(Email $email): ?UuidInterface
    {
        return $this->emailExist;
    }

    /** @var UuidInterface|null */
    private $emailExist;

}
