<?php

declare(strict_types=1);

namespace App\Tests\UI\Http\Rest\Response;

use App\Domain\Shared\ValueObject\DateTime;
use App\Domain\User\Event\UserWasCreated;
use App\Domain\User\ValueObject\Auth\Credentials;
use App\Domain\User\ValueObject\Auth\HashedPassword;
use App\Domain\User\ValueObject\Email;
use App\Infrastructure\Share\Bus\Query\Collection;
use App\Infrastructure\Share\Bus\Query\ItemFactory;
use App\Infrastructure\User\Query\Projections\UserView;
use App\UI\Http\Rest\Response\JsonApiFormatter;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class JsonApiFormatterTest extends TestCase
{
    /**
     * @test
     *
     * @group unit
     *
     * @throws \Exception
     * @throws \Assert\AssertionFailedException
     */
    public function format_collection(): void
    {
        $users = [
            self::createUserView(Uuid::uuid4(), Email::fromString('asd1@asd.asd')),
            self::createUserView(Uuid::uuid4(), Email::fromString('asd2@asd.asd')),
        ];

        $response = JsonApiFormatter::collection(new Collection(1, 10, \count($users), $users));

        self::assertArrayHasKey('data', $response);
        self::assertArrayHasKey('meta', $response);
        self::assertArrayHasKey('total', $response['meta']);
        self::assertArrayHasKey('page', $response['meta']);
        self::assertArrayHasKey('size', $response['meta']);
        self::assertCount(2, $response['data']);
    }

    /**
     * @test
     *
     * @group unit
     *
     * @throws \Assert\AssertionFailedException
     * @throws \Exception
     */
    public function format_one_output(): void
    {
        $userView = self::createUserView(Uuid::uuid4(), Email::fromString('demo@asd.asd'));

        $mockNormalizer = $this->createStub(NormalizerInterface::class);
        $mockNormalizer
            ->method('normalize')
            ->willReturn([
                'uuid' => $userView->uuid(),
                'credentials' => [
                    'email' => $userView->email(),
                ],
            ]);
        $factory = new ItemFactory($mockNormalizer);

        $response = JsonApiFormatter::one($factory->create($userView));

        self::assertArrayHasKey('data', $response);
        self::assertSame('UserView', $response['data']['type']);
        self::assertCount(2, $response['data']['attributes']);
    }

    /**
     * @throws \App\Domain\Shared\Exception\DateTimeException
     * @throws \Assert\AssertionFailedException
     */
    private static function createUserView(UuidInterface $uuid, Email $email): UserView
    {
        return UserView::fromUserWasCreated(new UserWasCreated(
            $uuid,
            new Credentials(
                $email,
                HashedPassword::fromHash('hashed_password')
            ),
            DateTime::now()
        ));
    }
}
