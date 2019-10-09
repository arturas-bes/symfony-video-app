<?php


namespace App\Controller\Traits;


use App\Entity\Subscribtion;

trait SaveSubscription
{
    private function saveSubscription($plan, $user)
    {
        $date = new \DateTime();
        $date->modify('+1 month');
        $subscription = $user->getSubscription();

        if (null === $subscription) {
            $subscription = new Subscribtion();
        }

        if ($subscription->getFreePlanUsed() && $plan == Subscribtion::getPlanDataNameByIndex(0)) {
            return;
        }
        $subscription->setValidTo($date);
        $subscription->setPlan($plan);
        $subscription->setFreePlanUsed(false);


        if ($plan == Subscribtion::getPlanDataNameByIndex(0)) {
            $subscription->setFreePlanUsed(true);
            $subscription->setPaymentStatus('paid');
        }
        $subscription->setPaymentStatus('paid'); //tmp

        $user->getSubscription($subscription);

        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();
    }
}