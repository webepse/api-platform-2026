<?php
namespace App\Events;

use App\Entity\User;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use ApiPlatform\Symfony\EventListener\EventPriorities;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class PasswordEncoderSubscriber implements EventSubscriberInterface
{
    public function __construct(private UserPasswordHasherInterface $passwordHasher)
    {}

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::VIEW => ['encodePassword', EventPriorities::PRE_WRITE]
        ];
    }
    
    public function encodePassword(ViewEvent $event)
    {
        // récup l'objet désérialisé 
        $user = $event->getControllerResult();
        // réup la méthode GET, POST ,...
        $method = $event->getRequest()->getMethod();

        // création du filtre
        // vérifier que l'objet soit un user et que la méthode soit POST
        if($user instanceof User && $method === "POST")
        {
            $hash = $this->passwordHasher->hashPassword($user, $user->getPassword());
            $user->setPassword($hash);
        }
    }
}
