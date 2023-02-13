<?php

declare(strict_types=1);

namespace Tests\App\Shared\Infrastructure\Event\Query;

use App\Shared\Domain\Exception\DateTimeException;
use App\Shared\Domain\ValueObject\DateTime as DomainDateTime;
use App\User\Domain\Event\UserWasCreated;
use App\Shared\Infrastructure\Event\ReadModel\ElasticSearchEventRepository;
use Assert\AssertionFailedException;
use Broadway\Domain\DomainMessage;
use Broadway\Domain\Metadata;
use PHPUnit\Framework\TestCase;

class EventElasticRepositoryTest extends TestCase
{
    private ?ElasticSearchEventRepository $repository;

    protected function setUp(): void
    {
        $this->repository = new ElasticSearchEventRepository(
            [
                'hosts' => [
                    'elasticsearch',
                ],
            ]
        );
        $this->repository->reboot();
        $this->repository->refresh();
    }

    /**
     * @test
     *
     * @group integration
     *
     * @throws AssertionFailedException
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

        $event = DomainMessage::recordNow($uuid, 1, new Metadata(), UserWasCreated::deserialize($data));

        $this->repository->store($event);
        $this->repository->refresh();

        $result = $this->repository->search([
            'query' => [
                'match' => [
                    'type' => $event->getType(),
                ],
            ],
        ]);

        self::assertSame(1, $result['hits']['total']['value']);
    }

    protected function tearDown(): void
    {
        $this->repository->delete();
        $this->repository = null;
    }
}
