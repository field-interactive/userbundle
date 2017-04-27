<?php

namespace Field\UserBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Field\UserBundle\Entity\User;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadUserData implements FixtureInterface, ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function load(ObjectManager $manager)
    {
        $user = new User();
        $user->setEmail('admin@field-user-bundle.com');
        $encoder = $this->container->get('security.password_encoder');
        $password = $encoder->encodePassword($user, 'password');
        $user->setPassword($password);
        $user->setName('Admin');
        $user->addRole('ROLE_ADMIN');
        $user->setEnabled(true);

        $manager->persist($user);

        $user = new User();
        $user->setEmail('user@field-user-bundle.com');
        $encoder = $this->container->get('security.password_encoder');
        $password = $encoder->encodePassword($user, 'password');
        $user->setPassword($password);
        $user->setName('User');
        $user->addRole('ROLE_USER');
        $user->setEnabled(true);

        $manager->persist($user);
        $manager->flush();
    }
}
