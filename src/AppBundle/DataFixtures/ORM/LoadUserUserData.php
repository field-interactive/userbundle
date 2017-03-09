<?php

namespace AppBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use AppBundle\Entity\User;
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
        $user->setRoles(array('ROLE_USER', 'ROLE_ADMIN'));
        $user->setEnabled(true);
        $user->setLastLogin(new \DateTime());
        $user->setExpiresAt((new \DateTime())->modify('+5 years'));
        $user->setCredentialsExpireAt((new \DateTime())->modify('+1 year'));

        $manager->persist($user);
        $manager->flush();
    }
}