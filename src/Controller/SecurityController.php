<?php


namespace App\Controller;


use App\Controller\Traits\SaveSubscription;
use App\Entity\Subscribtion;
use App\Entity\User;
use App\Form\UserType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    use SaveSubscription;

    /**
     * @Route("/register/{plan}", name="register")
     * @param Request $request
     * @param UserPasswordEncoderInterface $encoder
     * @param SessionInterface $session
     * @param $plan
     * @return Response
     */
    public function register(Request $request, UserPasswordEncoderInterface $encoder, SessionInterface $session, $plan)
    {
        //when user enters the page catch get request and set plan to session storage
        if ($request->isMethod('GET')) {
            $session->set('planName', $plan);
            $session->set('planPrice', Subscribtion::getPlanDataPriceByName($plan));
        }

        $user = new User;
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $user->setName($request->request->get('user')['name']);
            $user->setLastName($request->request->get('user')['last_name']);
            $user->setEmail($request->request->get('user')['email']);
            $password = $encoder->encodePassword($user,
                $request->request->get('user')['password']['first']);
            $user->setPassword($password);
            $user->setRoles(['ROLE_USER']);

            //subscription
            $date = new \DateTime();
            $date->modify('+1 month');
            $subscription = new Subscribtion();
            $subscription->setValidTo($date);
            $subscription->setPlan($session->get('planName'));
            $subscription->setFreePlanUsed(false);
            if ($plan == Subscribtion::getPlanDataNameByIndex(0)) {
                $subscription->setFreePlanUsed(true);
                $subscription->setPaymentStatus('paid');
            }
            $user->setSubscription($subscription);


            $em->persist($user);
            $em->flush();

//            Handle set user token and redirect to admin panel
            $this->loginUserAutomatically($user, $password);

            if ($this->isGranted('IS_AUTHENTICATED_REMEMBERED') &&
                $plan == Subscribtion::getPlanDataNameByIndex(0)) //free plan
            {
                $this->saveSubscription($plan, $this->getUser());

                return $this->redirectToRoute('admin_main_page');

            } elseif ($this->isGranted('IS_AUTHENTICATED_REMEMBERED')) {

                return $this->redirectToRoute('payment');
            }
        }

        return $this->render('front/register.html.twig', [
            'form' => $form->createView()
        ]);

    }

    /**
     * @Route("/login", name="login")
     * Parameter stores errors when user provides incorrect data to login form
     * @param AuthenticationUtils $helper
     * @return Response
     */
    public function login(AuthenticationUtils $helper)
    {

        return $this->render('front/login.html.twig', [
            'error' => $helper->getLastAuthenticationError()
        ]);
    }

    /**
     * @throws \Exception
     * @Route("/logout", name="logout")
     */
    public function logout():void
    {
        throw new \Exception('This should never be reached!');
    }


    private function loginUserAutomatically($user, $password)
    {
        $token = new UsernamePasswordToken(
            $user,
            $password,
            'main',
            $user->getRoles()
        );
        $this->get('security.token_storage')->setToken($token);
//        if token exists in the session it means that user is logged in in to the application
        $this->get('session')->set('_security_main', serialize($token));
    }
}