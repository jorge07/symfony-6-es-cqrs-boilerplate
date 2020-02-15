<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\Share\Event\Query;

use App\Domain\Shared\Exception\DateTimeException;
use App\Domain\Shared\ValueObject\DateTime as DomainDateTime;
use App\Domain\User\Event\UserWasCreated;
use App\Infrastructure\Share\Event\Query\EventElasticRepository;
use PHPUnit\Framework\TestCase;

class EventElasticRepositoryTest extends TestCase
{
    /**
     * @test
     *
     * @group integration
     *
     * @throws \Assert\AssertionFailedException
     * @throws DateTimeException
     */
    public function an_event_should_be_stored_in_elastic(): void
    {
        $data = [
            'uuid' => $uuid = 'e937f793-45d8-41e9-a756-a2bc711e3172',
            'credentials' => [
                'email' => 'lol@lol.com',
                'password' => 'lkasjbdalsjdbalsdbaljsdhbalsjbhd987',
                ],
            'created_at' => DomainDateTime::now()->toString(),
        ];

        $event = UserWasCreated::deserialize($data);

        $this->repo->store($event);
        $this->repo->refresh();

        $result = $this->repo->search([
            'query' => [
                'match' => [
                    'type' => UserWasCreated::class,
                ],
            ],
        ]);

        self::assertSame(1, $result['hits']['total']);
    }

    protected function setUp(): void
    {
        $this->repo = new EventElasticRepository(
            [
                'hosts' => [
                    'elasticsearch',
                ],
            ]
        );
    }

    protected function tearDown(): void
    {
        $this->repo->delete();

        $this->repo = null;
    }

    /** @var EventElasticRepository|null */
    private $repo;
}
