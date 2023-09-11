<?php
declare(strict_types=1);

namespace App\EventSubscriber;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\DataForEmptyBodyValidationInterface;
use App\Exception\EmptyBodyException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class EmptyBodySubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => ['handleEmptyBody', EventPriorities::POST_DESERIALIZE]
        ];
    }

    public function handleEmptyBody(RequestEvent $event)
    {
        $method = $event->getRequest()->getMethod();

        if (in_array($method, [Request::METHOD_POST, Request::METHOD_PUT]) === false) {
            return;
        }

        /** @var DataForEmptyBodyValidationInterface $object */
        $object = $event->getRequest()->get('data');

        $hasData = false;
        foreach ($object->getEmptyBodyData() as $field) {
            if (($field === null) === false) {
                $hasData = true;
            }
        }

        if ($hasData === false) {
            throw new EmptyBodyException();
        }
    }

}