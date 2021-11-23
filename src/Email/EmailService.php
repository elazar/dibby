<?php

namespace Elazar\Dibby\Email;

use Elazar\Dibby\Template\TemplateEngine;

class EmailService
{
    public function __construct(
        private EmailAdapter $emailAdapter,
        private TemplateEngine $templateEngine,
        private string $fromEmail,
        private string $baseUrl,
    ) { }

    public function sendPasswordResetEmail(string $toEmail, string $resetToken): bool
    {
        $data = [
            'baseUrl' => $this->baseUrl,
            'resetToken' => $resetToken,
        ];

        $textBody = $this->templateEngine->render('email/password-reset-text', $data);
        $htmlBody = $this->templateEngine->render('email/password-reset-html', $data);

        return $this->emailAdapter->sendEmail(
            from: $this->fromEmail,
            to: $toEmail,
            subject: 'Dibby Password Reset',
            textBody: $textBody,
            htmlBody: $htmlBody,
        );
    }
}
