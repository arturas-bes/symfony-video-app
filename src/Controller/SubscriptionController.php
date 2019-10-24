<?php
namespace App\Controller;
use App\Entity\Subscribtion;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
class SubscriptionController extends AbstractController
{
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
     * @Route("/payment", name="payment")
     */
    public function payment(SessionInterface $session)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        if($session->get('planName') == 'enterprise')
        {
            $subscribe = Subscribtion::EnterprisePlan;
        }
        else
        {
            $subscribe = Subscribtion::ProPlan;
        }
        return $this->render('front/payment.html.twig',['subscribe'=>$subscribe]);
    }
}