<?php

declare(strict_types=1);

namespace App\Tests\Application\Command\Car\Acquire;

use App\Application\Command\Car\Acquire\AcquireCarCommand;
use App\Infrastructure\Car\Query\Projections\CarView;
use App\Tests\Application\ApplicationTestCase;
use App\Tests\Application\Command\User\SignUp\SignUpHandlerTest;
use Doctrine\ORM\EntityManager;
use Ramsey\Uuid\Uuid;

class AcquireHandlerTest extends ApplicationTestCase
{
    /**
     * @test
     *
     * @group integration
     */
    public function acquire_car_handler_with_valid_data_must_persist_with_user_relation(): void
    {
        $createUser = SignUpHandlerTest::createTestUserCommand();
        $acquireCar = new AcquireCarCommand(Uuid::uuid4()->toString(), $createUser->uuid->toString(), new \DateTime());

        $this->handle($createUser);
        $this->handle($acquireCar);

        $this->fireTerminateEvent();

        /** @var EntityManager $em */
        $em = $this->service('doctrine.orm.default_entity_manager');

        /** @var CarView $readModelCar */
        $readModelCar = $em
            ->createQueryBuilder()
            ->select('car')
            ->from(CarView::class, 'car')
            ->where('car.owner = :owner')
            ->setParameter('owner', $createUser->uuid->getBytes())
            ->getQuery()
            ->getSingleResult()
        ;

        self::assertSame($readModelCar->owner->uuid()->toString(), $createUser->uuid->toString());
    }
}
