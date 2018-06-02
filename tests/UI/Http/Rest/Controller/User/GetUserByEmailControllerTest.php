<?php

declare(strict_types=1);

namespace App\Tests\UI\Http\Rest\Controller\User;

use App\Domain\User\ValueObject\Auth\Credentials;
use App\Domain\User\ValueObject\Auth\HashedPassword;
use App\Domain\User\ValueObject\Email;
use App\Infrastructure\User\Query\UserView;
use App\Tests\Infrastructure\Share\Event\EventCollectorListener;
use App\Tests\UI\Http\Rest\Controller\JsonApiTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Ramsey\Uuid\Uuid;

class GetUserByEmailControllerTest extends JsonApiTestCase
{
    /**
     * @test
     *
     * @group e2e
     */
    public function invalid_input_parameters_should_return_400_status_code()
    {
        $this->get('/api/user/asd@');

        self::assertEquals(400, $this->client->getResponse()->getStatusCode());

        /** @var EventCollectorListener $eventCollector */
        $eventCollector = $this->client->getContainer()->get(EventCollectorListener::class);

        $events = $eventCollector->popEvents();

        self::assertCount(0, $events);
    }

    /**
     * @test
     *
     * @group e2e
     */
    public function valid_input_parameters_should_return_404_status_code_when_not_exist()
    {
        $this->get('/api/user/asd@asd.asd');

        self::assertEquals(404, $this->client->getResponse()->getStatusCode());

        /** @var EventCollectorListener $eventCollector */
        $eventCollector = $this->client->getContainer()->get(EventCollectorListener::class);

        $events = $eventCollector->popEvents();

        self::assertCount(0, $events);
    }

    /**
     * @test
     *
     * @group e2e
     */
    public function valid_input_parameters_should_return_200_status_code_when_exist()
    {
        $emailString = $this->createReadModelUser();

        $this->get('/api/user/'.$emailString);

        self::assertEquals(200, $this->client->getResponse()->getStatusCode());

        /** @var EventCollectorListener $eventCollector */
        $eventCollector = $this->client->getContainer()->get(EventCollectorListener::class);

        $events = $eventCollector->popEvents();

        self::assertCount(0, $events);
    }

    private function createReadModelUser(): string
    {
        $model = new UserView();
        $model->uuid = Uuid::uuid4();
        $model->credentials = new Credentials(
            Email::fromString($emailString = 'lol@lo.com'),
            HashedPassword::encode('1234567890')
        );

        /** @var EntityManagerInterface $em */
        $em = $this->client->getContainer()->get('doctrine.orm.entity_manager');

        $em->persist($model);
        $em->flush();

        return $emailString;
    }
}
