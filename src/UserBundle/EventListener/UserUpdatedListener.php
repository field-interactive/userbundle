<?php

namespace UserBundle\EventListener;

use UserBundle\Event\UserEvent;
use Doctrine\ORM\EntityManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class UserUpdatedListener implements EventSubscriberInterface
{
    /**
     * Returns an array of event names this subscriber wants to listen to.
     * @return array The event names to listen to
     */
    protected $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public static function getSubscribedEvents()
    {
        return array(
            UserEvent::USER_UPDATED
        );
    }

    public function onUserUpdated(UserEvent $event)
    {
        $user = $event->getUser();

        $user->setUpdated(new \DateTime());

        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }
}