<?php

declare(strict_types=1);

namespace App\Tests\UI\Http\Rest\Response;

use App\Application\Query\Collection;
use App\Application\Query\Item;
use App\Infrastructure\User\Query\UserView;
use App\Domain\User\ValueObject\Email;
use App\UI\Http\Rest\Response\JsonApiFormatter;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class JsonApiFormatterTest extends TestCase
{

    /**
     * @test
     *
     * @group unit
     */
    public function format_collection()
    {

        $users = [
            self::createUserView(Uuid::uuid4(), Email::fromString('asd1@asd.asd')),
            self::createUserView(Uuid::uuid4(), Email::fromString('asd2@asd.asd'))
        ];

        $response = JsonApiFormatter::collection(new Collection(1, 10, 2, $users));

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
     */
    public function format_one_output()
    {
        $userView = self::createUserView(Uuid::uuid4(), Email::fromString('demo@asd.asd'));

        $response = JsonApiFormatter::one(new Item($userView));
        
        self::assertArrayHasKey('data', $response);
        self::assertEquals('UserView', $response['data']['type']);
        self::assertCount(2, $response['data']['attributes']);
    }

    private static function createUserView(UuidInterface $uuid, Email $email): UserView
    {
        $view = UserView::deserialize([
            'uuid' => $uuid->toString(),
            'credentials' => [
                'email' => $email->toString(),
                'password' => 'ljalsjdlajsdljlajsd'
            ]
        ]);

        return $view;
    }
}
