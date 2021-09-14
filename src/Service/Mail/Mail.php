<?php


namespace App\Service\Mail;


use App\Service\Monolog\Log;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Email;


class Mail
{
    public static function sendEmail($subject,$text)
    {
        $transport = Transport::fromDsn($_ENV['MAILER_DSN']);
        $mailer = new Mailer($transport);
        $email = (new Email())
            ->from($_ENV['MAILER_FROM'])
            ->to($_ENV['MAILER_TO'])
            ->priority(Email::PRIORITY_HIGH)
            ->subject($subject)
            ->text($text);

        try {
            $mailer->send($email);
        } catch (TransportExceptionInterface $e) {
            $logger = Log::getInstance();
            $logger->error("Message not send. Code Error: ".$e->getCode()." Message: ". $e->getMessage());
        }

    }
}