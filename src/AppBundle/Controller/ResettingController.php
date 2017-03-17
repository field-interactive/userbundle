<?php
/**
 * Created by PhpStorm.
 * User: svenkrefeld
 * Date: 17.03.2017
 * Time: 10:23
 */

namespace AppBundle\Controller;


use AppBundle\Entity\User;
use AppBundle\Event\UserEvent;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ResettingController extends Controller
{
    /**
     * Request the resetting of the user password
     *
     * @Route("/request", name="resetting_password_request")
     */
    public function requestAction(Request $request)
    {
        $form = $this->createForm('AppBundle\Form\UserEmailType');

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $email = $form->getData()['email'];

            $user = $this->getDoctrine()
                ->getRepository('AppBundle:User')
                ->findOneBy(array('email' => $email));

            if (!is_object($user) || !$user instanceof User) {
                $form['email']->addError(new FormError('Email could not be found.'));
            } elseif (!$user->isEnabled()) {
                $form['email']->addError(new FormError('Account is disabled.'));
            }

            if ($form->isValid()) {

                $em = $this->getDoctrine()->getManager();

                $user->setCredentialsExpireAt(new \DateTime('+24 hours'));

                $em->persist($user);
                $em->flush();

                $resettingPasswordEvent = new UserEvent($user, $request);

                $this->get('event_dispatcher')->dispatch(UserEvent::RESETTING_PASSWORD, $resettingPasswordEvent);

                $this->addFlash(
                    'success',
                    'An email has been sent to '.$email.'. It contains a link you must click to reset your password.'
                );

                return $this->redirectToRoute('login');
            }
        }

        return $this->render(
            'resetting/resettingPassword_request.html.twig',
            array('form' => $form->createView())
        );
    }

    /**
     * Reset the password
     *
     * @Route("/reset/{token}", name="resetting_password_reset")
     */
    public function resetAction(Request $request, $token)
    {
        $confirmation = explode('|', base64_decode($token));

        $em = $this->getDoctrine()->getManager();

        $user = $em->getRepository('AppBundle:User')->findOneBy(array('email' => $confirmation[0]));

        if (!is_object($user) || !$user instanceof User) {
            throw new NotFoundHttpException(sprintf('The user does not exist'));
        } elseif (is_null($user->getCredentialsExpireAt())) {
            throw new BadRequestHttpException(sprintf('The password resetting was not requested'));
        } elseif ($user->getCredentialsExpireAt() < new \DateTime()) {
            throw new AccessDeniedHttpException(sprintf('The token is no longer available'));
        }

        $compareToken = hash_hmac('md5', $user->getEmail(), $this->getParameter('secret'));

        if ($confirmation[1] !== $compareToken) {
            throw new AccessDeniedHttpException(sprintf('The token does not exist'));
        }

        $form = $this->createForm('AppBundle\Form\ResetPasswordType', $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $password = $this->get('security.password_encoder')
                ->encodePassword($user, $form->getData()->getPassword());
            $user->setPassword($password);
            $user->setCredentialsExpireAt(null);

            $em->persist($user);
            $em->flush();

            $this->addFlash(
                'success',
                'The password has been reset successfully'
            );

            return $this->redirectToRoute('login');
        }

        return $this->render(
            'resetting/resettingPassword_reset.html.twig',
            array('form' => $form->createView())
        );
    }
}