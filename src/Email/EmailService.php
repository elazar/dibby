<?php

namespace Elazar\Dibby\Email;

use Elazar\Dibby\{
    RouteConfiguration,
    Template\TemplateEngine,
};

class EmailService
{
    public function __construct(
        private EmailAdapter $emailAdapter,
        private TemplateEngine $templateEngine,
        private RouteConfiguration $routes,
        private string $fromEmail,
        private string $baseUrl,
    ) { }

    public function sendPasswordResetEmail(
        string $toEmail,
        string $userId,
        string $resetToken,
    ): bool {
        $path = $this->routes->getPath('get_reset');
        $resetUrl = $this->baseUrl . $path . '?' . http_build_query([
            'user' => $userId,
            'token' => $resetToken,
        ]);
        $data = [
            'resetUrl' => $resetUrl,
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
