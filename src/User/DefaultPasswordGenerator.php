<?php

namespace Elazar\Dibby\User;

class DefaultPasswordGenerator implements PasswordGenerator
{
    public function getPassword(User $user): string
    {
        return $user->getEmail();
    }
}
