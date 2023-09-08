<?php
declare(strict_types=1);

namespace App\Security;

use Lexik\Bundle\JWTAuthenticationBundle\Security\Authenticator\JWTAuthenticator;
use Symfony\Component\Security\Core\User\UserInterface;

/** @TODO: Token expires after somebody changed the password. Udemy Abschnitt 12: Video Nummer: 86 */
class TokenAuthenticator extends JWTAuthenticator
{
    protected function loadUser(array $payload, string $identity): UserInterface
    {
        return parent::loadUser($payload, $identity);

        /** @todo: if you want to delete the token you have to implement the functionality here */
        var_dump($payload);
        die;
    }

}