<?php

namespace App\DataFixtures;

use App\Entity\Subscribtion;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class SubscriptionFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        foreach ($this->getSubscriptionData() as [$user_id, $plan, $valid_to, $payment_status, $free_plan_used]) {
            $subscribtion = new Subscribtion();
            $subscribtion->setPlan($plan);
            $subscribtion->setValidTo($valid_to);
            $subscribtion->setPaymentStatus($payment_status);
            $subscribtion->setFreePlanUsed($free_plan_used);

            $user = $manager->getRepository(User::class)->find($user_id);
            $user->setSubscription($subscribtion);
            $manager->persist($user);
        };
        $manager->flush();
    }
    private function getSubscriptionData(): array
    {
        return [
            [
                4, //user
                Subscribtion::getPlanDataNameByIndex(2),
                (new \DateTime())->modify('+ 100 year'),
                'paid',
                false
            ], //super admin
            [
                5, //user
                Subscribtion::getPlanDataNameByIndex(0),
                (new \DateTime())->modify('+ 1 month'),
                'paid',
                true
            ],
            [
                1, //user
                Subscribtion::getPlanDataNameByIndex(1),
                (new \DateTime())->modify('+ 1 minute'),
                'paid',
                false
            ],
        ];
    }
}
