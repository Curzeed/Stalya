<?php
namespace App\Security;

use App\Entity\User;
use Symfony\Component\Security\Core\Exception\AccountStatusException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class onAuthCheck implements  UserCheckerInterface {

    public function checkPreAuth(UserInterface $user)
    {
        if (!$user instanceof User) return;

        if(!$user->isVerified()) throw new CustomUserMessageAccountStatusException("Vous devez valider votre e-mail avant de vous connecter");

    }

    public function checkPostAuth(UserInterface $user)
    {
        // TODO: Implement checkPostAuth() method.
    }
}
