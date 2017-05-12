<?php

namespace Field\UserBundle\Tests;

use Field\UserBundle\Entity\User;

class TestUser extends User
{
    public function __construct()
    {
        parent::__construct();
    }
}
