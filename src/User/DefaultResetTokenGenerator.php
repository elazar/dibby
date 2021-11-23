<?php

namespace Elazar\Dibby\User;

class DefaultResetTokenGenerator implements ResetTokenGenerator
{
    /**
     * @var callable
     */
    private $encoder;

    /**
     * @param ?callable $encoder
     */
    public function __construct(
        private int $length = 32,
        $encoder = null,
    ) {
        $this->encoder = $encoder ?? fn(string $s): string => bin2hex($s);
    }

    public function generateToken(): string
    {
        $bytes = random_bytes($this->length);
        return ($this->encoder)($bytes);
    }
}
