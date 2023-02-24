<?php

namespace App\Auth\Mailer;

use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mime\Email;
use PgFramework\Renderer\RendererInterface;
use Symfony\Component\Mailer\MailerInterface;

class PasswordResetMailer
{
    private $mailer;
    private $renderer;
    private $from;

    public function __construct(MailerInterface $mailer, RendererInterface $renderer, string $from)
    {
        $this->mailer = $mailer;
        $this->renderer = $renderer;
        $this->from = $from;
    }

    /**
     * @param string $to
     * @param array $params
     * @return void
     * @throws TransportExceptionInterface
     */
    public function send(string $to, array $params): void
    {
        $message = (new Email())
            ->from($this->from)
            ->to($to)
            ->subject('Réinitialisation de votre mot de passe')
            ->text($this->renderer->render('@auth/email/password.text', $params))
            ->html($this->renderer->render('@auth/email/password.html', $params));
        $this->mailer->send($message);
    }
}
