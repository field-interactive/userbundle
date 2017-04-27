<?php

namespace Field\UserBundle\EventListener\Admin;

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

    #$contactMail, $contactMailName, $secret

    public function __construct(Swift_Mailer $mailer, UrlGeneratorInterface $router, TwigEngine $twig, $contactMail, $contactMailName)
    {
        $this->mailer = $mailer;
        $this->router = $router;
        $this->twig = $twig;
        $this->contactMail = $contactMail;
        $this->contactMailName = $contactMailName;
    }

    /**
     * Returns an array of event names this subscriber wants to listen to.
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return array(
            UserEvent::ADMIN_RESETTING_PASSWORD
        );
    }

    public function onResettingPassword(UserEvent $event)
    {
        $this->sendConfirmationEmail($event->getUser());
    }

    public function sendConfirmationEmail(User $user)
    {
        $url = $this->router->generate('profile_change_password', array(), UrlGeneratorInterface::ABSOLUTE_URL);

        $message = \Swift_Message::newInstance()
            ->setSubject('Your password has been reset')
            ->setFrom($this->contactMail, $this->contactMailName)
            ->setTo($user->getEmail(), $user->getName())
            ->setBody(
                $this->twig->render(
                    '@FieldUser/admin/resettingPassword.html.twig',
                    array('user' => $user, 'url' => $url)
                ),
                'text/html'
            )
        ;
        $this->mailer->send($message);
    }
}