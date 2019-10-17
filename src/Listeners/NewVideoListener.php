<?php


namespace App\Listeners;

use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use App\Entity\Video;
use App\Entity\User;

class NewVideoListener
{
    // backslash means global, which means its not needed to provide path for a class
    public function __construct(\Twig_Environment $templating, \Swift_Mailer $mailer)
    {
        // these are put in construct method so there are available in all class methods
        $this->templating = $templating;
        $this->mailer = $mailer;
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();

        // only act on some entity

        if (!$entity instanceof Video) {
            return;
        }

        $em = $args->getObjectManager();
        $users = $em->getRepository(User::class)->findAll();
        foreach ($users as $user) {
            //exit($user->getName().' '.$entity->getTitle());

            $message = (new \Swift_Message('Hello email'))
                ->setFrom('send@example.com')
                ->setTo($user->getEmail())
                ->setBody(
                    $this->templating->render(
                        'emails/new_video.html.twig',
                        [
                            'name' => $user->getName(),
                            'video' => $entity
                        ]
                    ),
                    'text/html'
                );
            $this->mailer->send($message);
        }
    }
}