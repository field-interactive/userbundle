<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Entity\User;
use AppBundle\Event\UserEvent;
use AppBundle\Form\PasswordConfirmType;
use AppBundle\Form\ProfileType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/admin")
 */
class UserController extends Controller
{
    /**
     * List all user
     *
     * @Route("/", name="admin_user_index")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $users = $em->getRepository('AppBundle:User')->findAll();

        return $this->render('admin/index.html.twig', array(
            'users' => $users,
        ));
    }

    /**
     * Show the user
     *
     * @Route("/{id}", name="admin_user_show")
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $user = $em->getRepository('AppBundle:User')->find($id);

        return $this->render('admin/show.html.twig', array(
            'user' => $user,
        ));
    }

    /**
     * Edit the user
     *
     * @Route("/{id}/edit", name="admin_user_edit")
     */
    public function editAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $user = $em->getRepository('AppBundle:User')->find($id);

        $form = $this->createForm(ProfileType::class, $user);
        $form->setData($user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em->persist($user);
            $em->flush();

            $this->addFlash(
                'success',
                'The user has been updated'
            );

            return $this->redirectToRoute('admin_user_show', array('id' => $user->getId()));
        }

        return $this->render('admin/edit.html.twig', array(
            'form' => $form->createView(),
            'user' => $user,
        ));
    }

    /**
     * Reset the password
     *
     * @Route("/{id}/reset-password", name="admin_reset_password")
     */
    public function changePasswordAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $user = $em->getRepository('AppBundle:User')->find($id);

        $random = random_bytes(10);
        $plainPassword = substr(md5($random), 0, 8);
        $user->setPassword($plainPassword);

        $adminResettingPasswordEvent = new UserEvent($user, $request);

        $this->get('event_dispatcher')->dispatch(UserEvent::ADMIN_RESETTING_PASSWORD, $adminResettingPasswordEvent);

        $password = $this->get('security.password_encoder')
            ->encodePassword($user, $plainPassword);
        $user->setPassword($password);

        $em->persist($user);
        $em->flush();


        $this->addFlash(
            'success',
            'The password has been reset'
        );

        return $this->redirectToRoute('admin_user_edit', array('id' => $user->getId()));
    }

    /**
     * Deletes/Anonymize the personal data
     *
     * @Route("{id}/delete", name="admin_user_delete")
     */
    public function deleteAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $user = $em->getRepository('AppBundle:User')->find($id);

        $user->setName('anonymous');
        $user->setEmail('anonymous@anonymous-'.md5($user->getEmail()).'.com');
        $user->setLocked(true);

        $em->persist($user);
        $em->flush();

        $this->addFlash(
            'success',
            'The personal data are deleted'
        );

        return $this->redirectToRoute('admin_user_index');
    }

    /**
     * Deactivate the user
     *
     * @Route("{id}/deactivate", name="admin_user_deactivate")
     */
    public function deactivateAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $user = $em->getRepository('AppBundle:User')->find($id);
        $user->setLocked(true);

        $em->persist($user);
        $em->flush();

        $this->addFlash(
            'success',
            'The user has been deactivated'
        );

        return $this->redirectToRoute('admin_user_edit', array('id' => $user->getId()));
    }
}