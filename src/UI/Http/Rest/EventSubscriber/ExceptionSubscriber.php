<?php

declare(strict_types=1);

namespace UI\Http\Rest\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Throwable;

final class ExceptionSubscriber implements EventSubscriberInterface
{
    public function __construct(private readonly string $environment, private readonly array $exceptionToStatus = [])
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => 'onKernelException',
        ];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $request = $event->getRequest();
        if ($request->getContentTypeFormat() !== 'json') {
            return;
        }

        $exception = $event->getThrowable();

        $response = new JsonResponse();
        $response->headers->set('Content-Type', 'application/vnd.api+json');
        $response->setStatusCode($this->determineStatusCode($exception));
        $response->setData($this->getErrorMessage($exception));

        $event->setResponse($response);
    }

    private function getErrorMessage(Throwable $exception): array
    {
        $error = [
            'error' => [
                'title' => \str_replace('\\', '.', $exception::class),
                'detail' => $this->getExceptionMessage($exception),
                'code' => $exception->getCode(),
            ],
        ];

        if ('dev' === $this->environment) {
            $error = [...$error, ...[
                'meta' => [
                    'file' => $exception->getFile(),
                    'line' => $exception->getLine(),
                    'message' => $exception->getMessage(),
                    'trace' => $exception->getTrace(),
                    'traceString' => $exception->getTraceAsString(),
                ],
            ]];
        }

        return $error;
    }

    private function getExceptionMessage(Throwable $exception): string
    {
        return $exception->getMessage();
    }

    private function determineStatusCode(Throwable $exception): int
    {
        $exceptionClass = $exception::class;

        foreach ($this->exceptionToStatus as $class => $status) {
            if (\is_a($exceptionClass, $class, true)) {
                return $status;
            }
        }

        // Process HttpExceptionInterface after `exceptionToStatus` to allow overrides from config
        if ($exception instanceof HttpExceptionInterface) {
            return $exception->getStatusCode();
        }

        // Default status code is always 500
        return Response::HTTP_INTERNAL_SERVER_ERROR;
    }
}
