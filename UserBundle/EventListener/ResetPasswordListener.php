<?php

namespace Field\UserBundle\EventListener;

use Field\UserBundle\Entity\User;
use Field\UserBundle\Event\UserEvent;
use Swift_Mailer;
use Swift_Message;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ResetPasswordListener implements EventSubscriberInterface
{
    private $twig;
    private $mailer;
    private $router;
    private $contactMail;
    private $contactMailName;
    private $secret;

    #$contactMail, $contactMailName, $secret

    public function __construct(Swift_Mailer $mailer, UrlGeneratorInterface $router, TwigEngine $twig, $contactMail, $contactMailName, $secret)
    {
        $this->mailer = $mailer;
        $this->router = $router;
        $this->twig = $twig;
        $this->contactMail = $contactMail;
        $this->contactMailName = $contactMailName;
        $this->secret = $secret;
    }

    /**
     * Returns an array of event names this subscriber wants to listen to.
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return array(
            UserEvent::RESETTING_PASSWORD
        );
    }

    public function onResettingPassword(UserEvent $event)
    {
        $token = $this->generateAuthenticationToken($event->getUser());

        $this->sendConfirmationEmail($event->getUser(), $token);
    }

    public function sendConfirmationEmail(User $user, $token)
    {
        $confirmationUrl = $this->router->generate('resetting_password_reset', array('token' => $token), UrlGeneratorInterface::ABSOLUTE_URL);

        $message = \Swift_Message::newInstance()
            ->setSubject('Reset your password')
            ->setFrom($this->contactMail, $this->contactMailName)
            ->setTo($user->getEmail(), $user->getName())
            ->setBody(
                $this->twig->render(
                    'resetting/resettingPassword_confirmation.html.twig',
                    array('user' => $user, 'confirmationUrl' => $confirmationUrl)
                ),
                'text/html'
            )
        ;
        $this->mailer->send($message);
    }

    private function generateAuthenticationToken(User $user)
    {
        $email = $user->getEmail();

        $token = hash_hmac('md5', $email, $this->secret);

        return base64_encode($email."|".$token);
    }
}