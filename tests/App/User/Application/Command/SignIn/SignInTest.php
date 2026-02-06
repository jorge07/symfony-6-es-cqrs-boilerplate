<?php

declare(strict_types=1);

namespace Tests\App\Application\Command\User\SignIn;

use App\User\Application\Command\SignIn\SignInCommand;
use App\User\Application\Command\SignUp\SignUpCommand;
use App\User\Domain\Event\UserSignedIn;
use App\User\Domain\Exception\InvalidCredentialsException;
use Assert\AssertionFailedException;
use Broadway\Domain\DomainMessage;
use Exception;
use Ramsey\Uuid\Uuid;
use Tests\App\Shared\Application\ApplicationTestCase;
use Tests\App\Shared\Infrastructure\Event\EventCollectorListener;
use Throwable;

final class SignInTest extends ApplicationTestCase
{
    /**
     * @test
     *
     * @group integration
     *
     * @throws Throwable
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
     * @throws AssertionFailedException
     * @throws Throwable
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
     * @throws Exception
     * @throws AssertionFailedException
     * @throws Throwable
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
