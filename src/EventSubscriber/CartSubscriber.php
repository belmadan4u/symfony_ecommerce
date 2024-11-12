<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Session\SessionFactoryInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Twig\Environment;

class CartSubscriber implements EventSubscriberInterface
{
    private $sessionFactory;
    private $twig;

    public function __construct(SessionFactoryInterface $sessionFactory, Environment $twig)
    {
        $this->sessionFactory = $sessionFactory;
        $this->twig = $twig;
    }

    public function onKernelController(ControllerEvent $event)
    {
        $session = $this->sessionFactory->createSession();
        $cart = $session->get('cart', []);
        $cart['cartCount'] = $cart['cartCount'] ?? 0;
        $cart['cartTotal'] = $cart['cartTotal'] ?? 0.0;

        $session->set('cart', $cart);
        $this->twig->addGlobal('cart', $cart);
    }

    public static function getSubscribedEvents()
    {
        return [
            'kernel.controller' => 'onKernelController',
        ];
    }
}
