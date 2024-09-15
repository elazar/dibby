<?php

namespace Elazar\Dibby\Email;

interface EmailAdapter
{
    public function sendEmail(
        string $from,
        string $to,
        string $subject,
        string $textBody,
        string $htmlBody,
    ): bool;
}
