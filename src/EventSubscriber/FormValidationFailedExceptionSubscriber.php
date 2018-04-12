<?php

namespace App\EventSubscriber;


use App\Exception\FormValidationFailedException;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class FormValidationFailedExceptionSubscriber implements EventSubscriberInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => 'onKernelException',
        ];
    }

    public function onKernelException(GetResponseForExceptionEvent $event): void
    {
        $exception = $event->getException();

        if (!$exception instanceof FormValidationFailedException) {
            return;
        }

        $message = [
            'errors' =>
                $this->serializeFormError(
                    $exception->getForm()
                )
        ];

        $response = new JsonResponse(
            $message,
            Response::HTTP_BAD_REQUEST
        );

        $event->setResponse($response);
    }

    private function serializeFormError(FormInterface $form): array
    {
        $errors = [
            'fields' => [],
            'form' => []
        ];

        foreach ($form->getErrors() as $error) {
            $errors['form'][$form->getName()][] = $error->getMessage();
        }

        foreach ($form as $child /** @var FormInterface $child */ ) {
            foreach ($child->getErrors() as $error) {
                $errors['fields'][$child->getName()][] = $error->getMessage();
            }
        }

        return $errors;
    }
}