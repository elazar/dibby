<?php

namespace Elazar\Dibby\Email;

use Laminas\Mail\{
    Header\ContentType,
    Message,
    Transport\Exception\ExceptionInterface,
    Transport\TransportInterface,
};

use Laminas\Mime\{
    Message as MimeMessage,
    Mime,
    Part as MimePart,
};

use Psr\Log\LoggerInterface;

class LaminasEmailAdapter implements EmailAdapter
{
    public function __construct(
        private TransportInterface $transport,
        private LoggerInterface $logger,
    ) { }

    public function sendEmail(
        string $from,
        string $to,
        string $subject,
        string $textBody,
        string $htmlBody,
    ): bool {
        $htmlPart = new MimePart($htmlBody);
        $htmlPart->type = Mime::TYPE_HTML;
        $htmlPart->charset = 'utf-8';
        $htmlPart->encoding = Mime::ENCODING_QUOTEDPRINTABLE;

        $textPart = new MimePart($textBody);
        $textPart->type = Mime::TYPE_TEXT;
        $textPart->charset = 'utf-8';
        $textPart->encoding = Mime::ENCODING_QUOTEDPRINTABLE;

        $body = new MimeMessage;
        $body->setParts([$htmlPart, $textPart]);

        $message = new Message;
        $message->addTo($to);
        $message->addFrom($from);
        $message->setSubject($subject);
        $message->setBody($body);

        $headers = $message->getHeaders();
        /** @var ContentType|false */
        $contentType = $headers->get('Content-Type');
        if ($contentType === false) {
            $contentType = new ContentType;
            $headers->addHeader($contentType);
        }
        $contentType->setType('multipart/related');

        try {
            $this->transport->send($message);
            return true;
        } catch (ExceptionInterface $error) {
            $this->logger->warning('Error sending e-mail', [
                'to' => $to,
                'subject' => $subject,
                'error' => $error,
            ]);
            return false;
        }
    }
}
