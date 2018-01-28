<?php

declare(strict_types=1);

namespace App\Tests\Application\Command\User\ChangeEmail;

use App\Application\Command\User\ChangeEmail\ChangeEmailCommand;
use App\Application\Command\User\Create\CreateUserCommand;
use App\Application\Query\User\FindByEmail\FindByEmailQuery;
use App\Domain\User\Query\UserRead;
use App\Tests\Application\Command\ApplicationTestCase;
use App\Tests\Infrastructure\Share\Bus\EventCollectorMiddleware;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ChangeEmailHandlerTest extends ApplicationTestCase
{
    /**
     * @test
     *
     * @group integration
     */
    public function update_user_email_should_update_mysql_projection()
    {
        $command = new CreateUserCommand($uuid = Uuid::uuid4()->toString(), 'asd@asd.asd');

        $this
            ->handle($command);

        $email = 'lol@asd.asd';

        $command = new ChangeEmailCommand($uuid, $email);

        $this
            ->handle($command);

        /** @var UserRead $readUser */
        $readUser = $this->ask(new FindByEmailQuery($email));

        self::assertEquals($email, $readUser->email);
    }
}
