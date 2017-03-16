<?php

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use AppBundle\Form\ProfileType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/profile")
 */
class ProfileController extends Controller
{
    /**
     * Show the user.
     *
     * @Route("/", name="profile_show")
     */
    public function showAction()
    {
        $user = $this->getUser();
        if (!is_object($user) || !$user instanceof User) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }

        return $this->render('profile/show.html.twig', array(
            'user' => $user,
        ));
    }

    /**
     * Edit the user
     *
     * @Route("/edit", name="profile_edit")
     */
    public function editAction(Request $request)
    {
        $user = $this->getUser();
        if (!is_object($user) || !$user instanceof User) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }

        $form = $this->createForm(ProfileType::class, $user);
        $form->setData($user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $em->persist($user);
            $em->flush();

            $this->addFlash(
                'success',
                'The profile has been updated'
            );

            return $this->redirectToRoute('profile_show');
        }

        return $this->render(
            'profile/edit.html.twig',
            array('form' => $form->createView())
        );
    }

    /**
     * Change the password
     *
     * @Route("/change-password", name="profile_change_password")
     */
    public function changePasswordAction(Request $request)
    {
        $user = $this->getUser();
        if (!is_object($user) || !$user instanceof User) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }

        $form = $this->createForm('AppBundle\Form\ChangePasswordType');

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $password = $this->get('security.password_encoder')
                ->encodePassword($user, $form->getData()->getNewPassword());
            $user->setPassword($password);

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            $this->addFlash(
                'success',
                'Your password has been changed'
            );

            return $this->redirectToRoute('profile_show');
        }

        return $this->render(
            'profile/changePassword.html.twig',
            array('form' => $form->createView())
        );
    }
}