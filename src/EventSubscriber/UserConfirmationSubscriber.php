<?php
declare(strict_types=1);

namespace App\EventSubscriber;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\UserConfirmation;
use App\Exception\InvalidConfirmationTokenException;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class UserConfirmationSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly LoggerInterface $logger
    )
    {
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::VIEW => ['confirmUser', EventPriorities::POST_VALIDATE]
        ];
    }

    public function confirmUser(ViewEvent $event)
    {
        $request = $event->getRequest();

        if ('_api_/users/confirm_post' !== $request->get('_route')) {
            return;
        }

        /** @var UserConfirmation $confirmationToken */
        $confirmationToken = $event->getControllerResult();

        $this->logger->debug('Fetching user by confirmation token');
        $user = $this->userRepository->findOneBy(
            [
                'confirmationToken' => $confirmationToken->confirmationToken
            ]
        );

        if ($user === null) {
            $this->logger->debug('User by confirmation token not found');
            throw new InvalidConfirmationTokenException();
        }

        $user->setEnabled(true);
        $user->setConfirmationToken(null);
        $this->entityManager->flush();

        $event->setResponse(new JsonResponse(null, Response::HTTP_OK));

        $this->logger->debug('Confirmed user by confirmation token');
    }

}