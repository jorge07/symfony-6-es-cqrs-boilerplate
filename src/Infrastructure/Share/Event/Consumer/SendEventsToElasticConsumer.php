<?php

declare(strict_types=1);

namespace App\Infrastructure\Share\Event\Consumer;

use App\Infrastructure\Share\Event\Query\EventElasticRepository;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;

class SendEventsToElasticConsumer implements ConsumerInterface
{
    public function execute(AMQPMessage $msg): void
    {
        $this->eventElasticRepository->store(unserialize($msg->body));
    }

    public function __construct(EventElasticRepository $eventElasticRepository)
    {
        $this->eventElasticRepository = $eventElasticRepository;
    }

    /**
     * @var EventElasticRepository
     */
    private $eventElasticRepository;
}
