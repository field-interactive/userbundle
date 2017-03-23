<?php

namespace Field\UserBundle\Controller;

use Field\UserBundle\Entity\User;
use Field\UserBundle\Event\UserEvent;
use Field\UserBundle\Form\PasswordConfirmType;
use Field\UserBundle\Form\ProfileType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
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

        return $this->render('@FieldUser/profile/show.html.twig', array(
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

            $uow = $em->getUnitOfWork();
            $uow->computeChangeSets();
            $updatedFields = $uow->getEntityChangeSet($user);

            if (array_key_exists('email', $updatedFields)) {

                $session = $request->getSession();
                $session->set('updatedFields', $updatedFields);

                return $this->redirectToRoute('profile_confirm_edit');
            }

            $em->persist($user);
            $em->flush();

            $userUpdatedEvent = new UserEvent($user, $request);

            $this->get('event_dispatcher')->dispatch(UserEvent::USER_UPDATED, $userUpdatedEvent);

            $this->addFlash(
                'success',
                'The profile has been updated'
            );

            return $this->redirectToRoute('profile_show');
        }

        return $this->render(
            '@FieldUser/profile/edit.html.twig',
            array('form' => $form->createView())
        );
    }

    /**
     * @Route("/edit/confirm", name="profile_confirm_edit")
     */
    public function confirmEditAction(Request $request)
    {
        $user = $this->getUser();
        if (!is_object($user) || !$user instanceof User) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }

        $form = $this->createForm(PasswordConfirmType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em = $this->getDoctrine()->getManager();

            $session = $request->getSession();
            $updatedFields = $session->get('updatedFields');

            $user->setEmail($updatedFields['email'][1]);

            $em->persist($user);
            $em->flush();

            $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
            $this->get('security.token_storage')->setToken($token);
            $this->get('session')->set('_security_main', serialize($token));

            $userUpdatedEvent = new UserEvent($user, $request);

            $this->get('event_dispatcher')->dispatch(UserEvent::USER_UPDATED, $userUpdatedEvent);

            $this->addFlash(
                'success',
                'The profile has been updated'
            );

            return $this->redirectToRoute('profile_show');
        }

        return $this->render(
            '@FieldUser/profile/confirm_edit.html.twig',
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

        $form = $this->createForm('Field\UserBundle\Form\ChangePasswordType');

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $password = $this->get('security.password_encoder')
                ->encodePassword($user, $form->getData()->getNewPassword());
            $user->setPassword($password);

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            $userUpdatedEvent = new UserEvent($user, $request);

            $this->get('event_dispatcher')->dispatch(UserEvent::USER_UPDATED, $userUpdatedEvent);

            $this->addFlash(
                'success',
                'Your password has been changed'
            );

            return $this->redirectToRoute('profile_show');
        }

        return $this->render(
            '@FieldUser/profile/changePassword.html.twig',
            array('form' => $form->createView())
        );
    }

    /**
     * Deletes/Anonymize the personal data
     *
     * @Route("/delete", name="profile_delete")
     */
    public function deleteAction(Request $request)
    {
        $user = $this->getUser();
        if (!is_object($user) || !$user instanceof User) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }

        $form = $this->createForm(PasswordConfirmType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em = $this->getDoctrine()->getManager();

            $session = $request->getSession();

            $user->setName('anonymous');
            $user->setEmail('anonymous@anonymous-'.md5($user->getEmail().random_bytes(10)).'.com');
            $user->setLocked(true);

            $em->persist($user);
            $em->flush();

            $userUpdatedEvent = new UserEvent($user, $request);

            $this->get('event_dispatcher')->dispatch(UserEvent::USER_UPDATED, $userUpdatedEvent);

            $this->get('security.token_storage')->setToken(null);
            $session->invalidate();

            $this->addFlash(
                'success',
                'Your personal data are deleted'
            );

            return $this->redirectToRoute('default');
        }

        return $this->render(
            '@FieldUser/profile/delete.html.twig',
            array('form' => $form->createView())
        );
    }
}