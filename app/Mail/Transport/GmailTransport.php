<?php

namespace App\Mail\Transport;

use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mime\RawMessage;
use Google_Client;
use Google_Service_Gmail;
use Google_Service_Gmail_Message;

class GmailTransport implements TransportInterface
{
    protected $client;
    protected $gmail;

    public function __construct(Google_Client $client)
    {
        $this->client = $client;
        $this->gmail = new Google_Service_Gmail($client);
    }

    public function send(RawMessage $message, ?Envelope $envelope = null): ?SentMessage
    {
        $rawMessage = $this->getMimeMessage($message);
        $gmailMessage = new Google_Service_Gmail_Message();
        $gmailMessage->setRaw($rawMessage);

        $this->gmail->users_messages->send('me', $gmailMessage);

        return new SentMessage($message, $envelope);
    }

    protected function getMimeMessage(RawMessage $message): string
    {
        $messageString = $message->toString();
        $base64EncodedMessage = base64_encode($messageString);
        return rtrim(strtr($base64EncodedMessage, '+/', '-_'), '=');
    }

    public function __toString(): string
    {
        return 'gmail';
    }
}
