<?php

namespace App\Controller;

use App\Controller\Traits\SaveSubscription;
use App\Entity\Subscribtion;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class SubscriptionController extends AbstractController
{
    use SaveSubscription;
    /**
     * @Route("/pricing", name="pricing")
     */
    public function pricing()
    {
        return $this->render('front/pricing.html.twig', [
            'name' => Subscribtion::getPlanDataNames(),
            'price' => Subscribtion::getPlanDataPrices()
        ]);
    }

    /**
     * @Route("/payment/{paypal}", name="payment", defaults={"paypal":false})
     */
    public function payment($paypal, SessionInterface $session)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        if ($paypal) {
            $this->saveSubscription($session->get('planName'), $this->getUser());

            return $this->redirectToRoute('admin_main_page');
        }
        return $this->render('front/payment.html.twig', [
        ]);
    }
}
