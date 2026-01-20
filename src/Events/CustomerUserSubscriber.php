<?php
    namespace App\Events;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\Customer;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;

    class CustomerUserSubscriber implements EventSubscriberInterface
    {
        public function __construct(private Security $security)
        {}

        public static function getSubscribedEvents()
        {
            return [
                KernelEvents::VIEW => ['setUserForCustomer', EventPriorities::PRE_VALIDATE]
            ];
        }

        public function setUserForCustomer(ViewEvent $event)
        {
            $customer = $event->getControllerResult();
            $method = $event->getRequest()->getMethod();

            if($customer instanceof Customer && $method === "POST")
            {
                $user = $this->security->getUser();
                $customer->setUser($user);
            }
        }
    }



?>