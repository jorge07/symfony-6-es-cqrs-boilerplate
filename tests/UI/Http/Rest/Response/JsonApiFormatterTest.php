<?php

declare(strict_types=1);

namespace App\Tests\UI\Http\Rest\Response;

use App\Domain\User\Query\UserView;
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

        $response = JsonApiFormatter::collection($users);

        self::assertArrayHasKey('data', $response);
        self::assertCount(2, $response['data']);
    }

    /**
     * @test
     *
     * @group unit
     */
    public function format_one_output()
    {
        $response = JsonApiFormatter::one(self::createUserView(Uuid::uuid4(), Email::fromString('demo@asd.asd')));
        
        self::assertArrayHasKey('data', $response);
        self::assertEquals('UserView', $response['data']['type']);
        self::assertCount(2, $response['data']['attributes']);
    }

    private static function createUserView(UuidInterface $uuid, Email $email): UserView
    {
        $view = UserView::deserialize([
            'uuid' => $uuid->toString(),
            'email' => $email->toString()
        ]);

        return $view;
    }
}
