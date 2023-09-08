<?php
declare(strict_types=1);

namespace App\Security;

use App\Entity\User;
use Symfony\Component\Security\Core\Exception\DisabledException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/** in security.yaml user_checker: App\Security\UserEnabledChecker */
class UserEnabledChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user)
    {
        if (($user instanceof User) === false) {
            return;
        }

        if ($user->getEnabled() === false) {
            throw new DisabledException();
        }
    }

    public function checkPostAuth(UserInterface $user)
    {
    }

}