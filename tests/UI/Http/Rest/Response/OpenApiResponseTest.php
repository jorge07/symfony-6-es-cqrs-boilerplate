<?php

declare(strict_types=1);

namespace App\Tests\UI\Http\Rest\Response;

use App\Application\Query\Collection;
use App\Application\Query\Item;
use App\Domain\Shared\Exception\DateTimeException;
use App\Domain\Shared\ValueObject\DateTime;
use App\Domain\User\ValueObject\Email;
use App\Infrastructure\User\ReadModel\UserView;
use App\UI\Http\Rest\Response\OpenApi;
use Assert\AssertionFailedException;
use Exception;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class OpenApiResponseTest extends TestCase
{
    /**
     * @test
     *
     * @group unit
     *
     * @throws Exception
     * @throws AssertionFailedException
     */
    public function format_collection(): void
    {
        $users = [
            Item::fromSerializable(self::createUserView(Uuid::uuid4(), Email::fromString('asd1@asd.asd'))),
            Item::fromSerializable(self::createUserView(Uuid::uuid4(), Email::fromString('asd2@asd.asd'))),
        ];

        $response = \json_decode(OpenApi::collection(new Collection(1, 10, \count($users), $users))->getContent(), true);

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
     * @throws AssertionFailedException
     * @throws Exception
     */
    public function format_one_output(): void
    {
        $userView = self::createUserView(Uuid::uuid4(), Email::fromString('demo@asd.asd'));

        $response = \json_decode(OpenApi::one(Item::fromSerializable($userView))->getContent(), true);

        self::assertArrayHasKey('data', $response);
        self::assertSame('UserView', $response['data']['type']);
        self::assertCount(2, $response['data']['attributes']);
    }

    /**
     * @throws DateTimeException
     * @throws AssertionFailedException
     */
    private static function createUserView(UuidInterface $uuid, Email $email): UserView
    {
        return UserView::deserialize([
            'uuid' => $uuid->toString(),
            'credentials' => [
                'email' => $email->toString(),
                'password' => 'ljalsjdlajsdljlajsd',
            ],
            'created_at' => DateTime::now()->toString(),
            'updated_at' => DateTime::now()->toString(),
        ]);
    }
}
