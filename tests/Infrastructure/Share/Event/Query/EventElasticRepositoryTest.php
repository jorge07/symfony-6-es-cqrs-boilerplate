<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\Share\Event\Query;

use App\Domain\Shared\Exception\DateTimeException;
use App\Domain\Shared\ValueObject\DateTime as DomainDateTime;
use App\Domain\User\Event\UserWasCreated;
use App\Domain\User\ValueObject\Auth\Credentials;
use App\Domain\User\ValueObject\Auth\HashedPassword;
use App\Domain\User\ValueObject\Email;
use App\Infrastructure\Share\Event\Query\EventElasticRepository;
use Broadway\Domain\DateTime;
use Broadway\Domain\DomainMessage;
use Broadway\Domain\Metadata;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class EventElasticRepositoryTest extends TestCase
{
    private ?EventElasticRepository $repository;

    protected function setUp(): void
    {
        $this->repository = new EventElasticRepository(
            [
                'hosts' => [
                    'elasticsearch',
                ],
            ]
        );
    }

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
        $mockNormalizer = $this->createStub(NormalizerInterface::class);
        $this->repository->setNormalizer($mockNormalizer);

        $uuid = Uuid::uuid4();
        $event = new DomainMessage(
            $uuid->toString(),
            1,
            new Metadata(),
            new UserWasCreated(
                $uuid,
                new Credentials(
                    Email::fromString('lol@lol.com'),
                    HashedPassword::fromHash('hashed_password')
                ),
                DomainDateTime::now()
            ),
            DateTime::now()
        );

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
