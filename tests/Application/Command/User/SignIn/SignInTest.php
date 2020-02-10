<?php

declare(strict_types=1);

namespace App\Tests\Application\Command\User\SignIn;

use App\Application\Command\User\SignIn\SignInCommand;
use App\Application\Command\User\SignUp\SignUpCommand;
use App\Domain\User\Event\UserSignedIn;
use App\Domain\User\Exception\InvalidCredentialsException;
use App\Tests\Application\ApplicationTestCase;
use App\Tests\Infrastructure\Share\Event\EventCollectorListener;
use Broadway\Domain\DomainMessage;
use Ramsey\Uuid\Uuid;

final class SignInTest extends ApplicationTestCase
{
    /**
     * @test
     *
     * @group integration
     *
     * @throws \Assert\AssertionFailedException
     */
    public function user_sign_up_with_valid_credentials(): void
    {
        $command = new SignInCommand(
            'asd@asd.asd',
            'qwerqwer'
        );

        $this->handle($command);

        /** @var EventCollectorListener $eventCollector */
        $eventCollector = $this->service(EventCollectorListener::class);
        /** @var DomainMessage[] $events */
        $events = $eventCollector->popEvents();

        self::assertInstanceOf(UserSignedIn::class, $events[1]->getPayload());
    }

    /**
     * @test
     *
     * @group integration
     *
     * @dataProvider invalidCredentials
     *
     * @throws \Assert\AssertionFailedException
     */
    public function user_sign_up_with_invalid_credentials_must_throw_domain_exception(string $email, string $pass): void
    {
        $this->expectException(InvalidCredentialsException::class);

        $command = new SignInCommand($email, $pass);

        $this->handle($command);
    }

    public function invalidCredentials(): array
    {
        return [
          [
              'email' => 'asd@asd.asd',
              'pass' => 'qwerqwer123',
          ],
          [
              'email' => 'asd@asd.com',
              'pass' => 'qwerqwer',
          ],
        ];
    }

    /**
     * @throws \Exception
     * @throws \Assert\AssertionFailedException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $command = new SignUpCommand(
            Uuid::uuid4()->toString(),
            'asd@asd.asd',
            'qwerqwer'
        );

        $this->handle($command);
    }
}
