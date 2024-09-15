<?php

namespace Elazar\Dibby\User;

interface PasswordGenerator
{
    public function getPassword(User $user): string;
}
