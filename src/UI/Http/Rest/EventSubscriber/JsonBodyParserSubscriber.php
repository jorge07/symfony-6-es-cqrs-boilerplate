<?php

declare(strict_types=1);

namespace App\UI\Http\Rest\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class JsonBodyParserSubscriber implements EventSubscriberInterface
{
    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        if (! $this->isJsonRequest($request)) {

            return;
        }

        $content = $request->getContent();

        if (empty($content)) {

            return;
        }

        if (! $this->transformJsonBody($request)) {
            $response = Response::create('Unable to parse json request.', 400);
            $event->setResponse($response);
        }
    }
    private function isJsonRequest(Request $request): bool
    {
        return 'json' === $request->getContentType();
    }

    private function transformJsonBody(Request $request): bool
    {
        $data = json_decode($request->getContent(), true);

        if (json_last_error() !== JSON_ERROR_NONE) {

            return false;
        }

        if ($data === null) {

            return true;
        }

        $request->request->replace($data);

        return true;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => 'onKernelRequest'
        ];
    }
}
