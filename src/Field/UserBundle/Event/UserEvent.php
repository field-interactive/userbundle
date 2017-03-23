<?php

namespace Field\UserBundle\Event;

use Field\UserBundle\Entity\User;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Request;

class UserEvent extends Event
{
    /**
     * This event allows you to access the response which will be sent.
     *
     * @Event("Field\UserBundle\Event\UserEvent")
     */

    const USER_UPDATED = 'user.updated';
    const REGISTRATION_COMPLETED = 'user.registration.completed';
    const RESETTING_PASSWORD = 'user.resetting.password';
    const ADMIN_RESETTING_PASSWORD = 'admin.resetting.password';

    /**
     * @var null|Request
     */
    protected $request;

    /**
     * @var User
     */
    protected $user;

    /**
     * UserEvent constructor.
     *
     * @param User $user
     * @param Request|null  $request
     */
    public function __construct(User $user, Request $request = null)
    {
        $this->user = $user;
        $this->request = $request;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }
}