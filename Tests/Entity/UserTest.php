<?php

namespace Field\UserBundle\Tests\Entity;

use Field\UserBundle\Entity\User;

class UserTest extends \PHPUnit\Framework\TestCase
{
    public function testEmail()
    {
        $user = $this->getUser();
        $this->assertNull($user->getEmail());

        $user->setEmail('john@doe.com');
        $this->assertSame('john@doe.com', $user->getEmail());
    }

    public function testName()
    {
        $user = $this->getUser();
        $this->assertNull($user->getName());

        $user->setName('John Doe');
        $this->assertSame('John Doe', $user->getName());
    }

    public function testRoles()
    {
        $user = $this->getUser();
        $defaultrole = User::ROLE_DEFAULT;
        $newrole = 'ROLE_X';
        $this->assertTrue(in_array($defaultrole, $user->getRoles()));
        $user->addRole($defaultrole);
        $this->assertTrue(in_array($defaultrole, $user->getRoles()));
        $user->addRole($newrole);
        $this->assertTrue(in_array($newrole, $user->getRoles()));
    }

    public function testIsEnabled()
    {
        $user = $this->getUser();
        $this->assertFalse($user->isEnabled());
        $user->setEnabled(true);
        $this->assertTrue($user->isEnabled());
    }

    /**
     * @return User
     */
    protected function getUser()
    {
        return $this->getMockForAbstractClass('Field\UserBundle\Entity\User');
    }
}
