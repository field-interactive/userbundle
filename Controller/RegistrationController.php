<?php

namespace Field\UserBundle\Controller;

use Field\UserBundle\Event\UserEvent;
use Field\UserBundle\Form\RegisterType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\AuthenticationServiceException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\SecurityEvents;

/**
 * @Route("/register")
 */
class RegistrationController extends Controller
{
    /**
     * @Route("/", name="registration_register")
     */
    public function registerAction(Request $request)
    {
        $class = $this->container->getParameter('user_class');

        $user = new $class();
        $form = $this->createForm(RegisterType::class, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $password = $this->get('security.password_encoder')
                ->encodePassword($user, $user->getPassword());
            $user->setPassword($password);

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            $registrationCompletedEvent = new UserEvent($user, $request);

            $this->get('event_dispatcher')->dispatch(UserEvent::REGISTRATION_COMPLETED, $registrationCompletedEvent);

            $this->addFlash(
                'success',
                'An email has been sent to '.$user->getEmail().'. It contains an activation link you must click to activate your account.'
            );

            return $this->redirectToRoute('default');
        }

        return $this->render(
            '@FieldUser/registration/register.html.twig',
            array('form' => $form->createView())
        );
    }

    /**
     * @Route("/confirm/{token}", name="registration_confirm")
     */
    public function confirmAction(Request $request, $token)
    {
        $confirmation = explode('|', base64_decode($token));

        $em = $this->getDoctrine()->getManager();

        $user = $em->getRepository($this->container->getParameter('user_class'))->findOneBy(array('email' => $confirmation[0]));

        $expireDate = new \DateTime('- 7 days');

        if (!is_object($user) || !$user instanceof UserInterface) {
            throw new NotFoundHttpException(sprintf('The user does not exist'));
        } elseif ($user->isEnabled()) {
            throw new AuthenticationServiceException(sprintf('The user is already enabled'));
        } elseif ($user->getCreated() < $expireDate) {
            throw new AccessDeniedHttpException(sprintf('The token is no longer available'));
        }

        $compareToken = hash_hmac('md5', $user->getEmail(), $this->getParameter('secret'));

        if ($confirmation[1] !== $compareToken) {
            throw new AccessDeniedHttpException(sprintf('The token does not exist'));
        }

        $user->addRole('ROLE_USER');
        $user->setEnabled(true);

        $em->persist($user);
        $em->flush();

        $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
        $this->get('security.token_storage')->setToken($token);
        $this->get('session')->set('_security_main', serialize($token));

        $loginEvent = new InteractiveLoginEvent($request, $token);
        $this->get('event_dispatcher')->dispatch(SecurityEvents::INTERACTIVE_LOGIN, $loginEvent);

        $this->addFlash(
            'success',
            'Your account is now activated!'
        );

        return $this->redirectToRoute('default');
    }
}