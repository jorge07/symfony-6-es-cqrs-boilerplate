<?php

declare(strict_types = 1);

namespace App\UI\Http\Rest\EventSubscriber;

use App\Domain\Shared\Query\Exception\NotFoundException;
use Broadway\Repository\AggregateNotFoundException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class ExceptionSubscriber
{
    /**
     * @var string
     */
    private $environment;

    public function __construct()
    {
        $this->environment = \getenv('APP_ENV');
    }

    /**
     * Method to handle kernel exception.
     *
     * @param GetResponseForExceptionEvent $event
     *
     * @throws \InvalidArgumentException
     * @throws \UnexpectedValueException
     * @throws \LogicException
     */
    public function onKernelException(GetResponseForExceptionEvent $event): void
    {
        $exception = $event->getException();

        $response = new JsonResponse();
        $response->headers->set('Content-Type', 'application/vnd.api+json');
        $response->setStatusCode($this->getStatusCode($exception));
        $response->setData($this->getErrorMessage($exception, $response));

        $event->setResponse($response);
    }

    private function getStatusCode(\Exception $exception): int
    {
        return $this->determineStatusCode($exception);
    }

    private function getErrorMessage(\Exception $exception, Response $response): array
    {
        $error = [
            'errors'=> [
                'title'   => get_class($exception),
                'detail'   => $this->getExceptionMessage($exception),
                'code'      => $exception->getCode(),
                'status'    => $response->getStatusCode(),
            ]
        ];

        if ($this->environment === 'dev') {

            $error['errors'] = [
                'meta' => [
                    'error' => '',
                    'file'          => $exception->getFile(),
                    'line'          => $exception->getLine(),
                    'message'       => $exception->getMessage(),
                    'trace'         => $exception->getTrace(),
                    'traceString'   => $exception->getTraceAsString(),
                ],
            ];
        }

        return $error;
    }

    private function getExceptionMessage(\Exception $exception): string
    {
        return $this->environment === 'dev'
            ? $exception->getMessage()
            : $this->getMessageForProductionEnvironment($exception);
    }

    private function getMessageForProductionEnvironment(\Exception $exception): string
    {
        $message = $exception->getMessage();

        return $message;
    }

    private function determineStatusCode(\Exception $exception): int
    {
        // Default status code is always 500
        $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR;

        if ($exception instanceof HttpExceptionInterface) {
            $statusCode = $exception->getStatusCode();
        } elseif ($exception instanceof \InvalidArgumentException) {
            $statusCode = 400;
        } elseif ($exception instanceof AggregateNotFoundException || $exception instanceof NotFoundException) {
            $statusCode = 404;
        }

        return $statusCode;
    }
}