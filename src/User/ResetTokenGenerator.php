<?php

namespace Elazar\Dibby\User;

interface ResetTokenGenerator
{
    public function generateToken(): string;
}
