<?php

namespace App\Mail\Transport;

use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\MessageConverter;
use Unosend\Unosend;

class UnosendTransport extends AbstractTransport
{
    protected Unosend $client;

    public function __construct(
        string $apiKey,
        ?EventDispatcherInterface $dispatcher = null,
        ?LoggerInterface $logger = null
    ) {
        parent::__construct($dispatcher, $logger);
        $this->client = new Unosend($apiKey);
    }

    protected function doSend(SentMessage $message): void
    {
        $email = MessageConverter::toEmail($message->getOriginalMessage());
        $envelope = $message->getEnvelope();

        $payload = $this->buildPayload($email, $envelope);

        $this->client->emails->send($payload);
    }

    protected function buildPayload(Email $email, Envelope $envelope): array
    {
        $from = $envelope->getSender();

        $payload = [
            'from' => [
                'email' => $from->getAddress(),
                'name' => $from->getName() ?: null,
            ],
            'to' => $this->formatAddresses($email->getTo()),
            'subject' => $email->getSubject(),
        ];

        if ($email->getTextBody()) {
            $payload['text'] = $email->getTextBody();
        }

        if ($email->getHtmlBody()) {
            $payload['html'] = $email->getHtmlBody();
        }

        if ($cc = $email->getCc()) {
            $payload['cc'] = $this->formatAddresses($cc);
        }

        if ($bcc = $email->getBcc()) {
            $payload['bcc'] = $this->formatAddresses($bcc);
        }

        if ($replyTo = $email->getReplyTo()) {
            $payload['reply_to'] = $this->formatAddresses($replyTo)[0] ?? null;
        }

        return $payload;
    }

    /**
     * @param Address[] $addresses
     */
    protected function formatAddresses(array $addresses): array
    {
        return array_map(function (Address $address) {
            return [
                'email' => $address->getAddress(),
                'name' => $address->getName() ?: null,
            ];
        }, $addresses);
    }

    public function __toString(): string
    {
        return 'unosend';
    }
}
