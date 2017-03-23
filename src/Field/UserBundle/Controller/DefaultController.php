<?php

namespace Field\UserBundle\Controller;

use Field\UserBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="default")
     */
    public function indexAction()
    {
        $user = $this->getUser();

        if (!is_object($user) || !$user instanceof User) {
            return $this->redirectToRoute('login');
        } else {
            return $this->redirectToRoute('profile_show');
        }
    }
}
