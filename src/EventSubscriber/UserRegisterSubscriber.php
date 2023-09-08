<?php
declare(strict_types=1);

namespace App\EventSubscriber;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\User;
use App\Security\TokenGenerator;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Part\TextPart;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/** Former PasswordHashSubscriber to hash the given password before the user has been persisted to database */
class UserRegisterSubscriber implements EventSubscriberInterface
{

    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly TokenGenerator $tokenGenerator,
        private readonly MailerInterface $mailer
    )
    {
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::VIEW => ['userRegistered', EventPriorities::PRE_WRITE]
        ];
    }

    public function userRegistered(ViewEvent $event)
    {
        $user = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();

        if (
            ($user instanceof User) === false ||
            in_array($method, [Request::METHOD_POST]) === false) {
            return;
        }

        // It is an User, we need to hash password here!
        $user->setPassword(
            $this->passwordHasher->hashPassword($user, $user->getPassword())
        );

        $user->setConfirmationToken(
            $this->tokenGenerator->getRandomSecureToken()
        );

        // sending emails
        // @todo: if you want to use mails, configure mailing. Abschnitt 13 - Video Nummer 94
//        $message = (new Email())
//            ->subject('Hello From API PLATFORM!')
//            ->from('dogan_can@mail.de')
//            ->to('dogan_can@mail.de')
//            ->text('Hello, how are you?');
//
//        $this->mailer->send($message);
    }

}