<?php

namespace UserBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use UserBundle\Entity\User;
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
        $user->setEmail('sven.krefeld@myboom.de');
        $encoder = $this->container->get('security.password_encoder');
        $password = $encoder->encodePassword($user, 'myboom');
        $user->setPassword($password);
        $user->setName('Sven Krefeld');
        $user->addRole('ROLE_ADMIN');
        $user->setEnabled(true);

        $manager->persist($user);
        $manager->flush();
    }
}