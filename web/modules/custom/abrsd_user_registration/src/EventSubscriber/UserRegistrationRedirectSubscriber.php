<?php

namespace Drupal\abrsd_user_registration\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Event subscriber for redirecting user/register routes.
 */
class UserRegistrationRedirectSubscriber implements EventSubscriberInterface
{

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        // The higher the number, the earlier the method is called.
        $events = [];
        $events[KernelEvents::REQUEST][] = ['redirectUserRegister', 100];
        return $events;
    }

    /**
     * This method is called whenever the KernelEvents::REQUEST event is
     * dispatched.
     *
     * @param \Symfony\Component\HttpKernel\Event\RequestEvent $event
     */
    public function redirectUserRegister(RequestEvent $event)
    {
        $request = $event->getRequest();
        // Only redirect for anonymous users.
        if ($request->getPathInfo() == '/user/register' && \Drupal::currentUser()->isAnonymous()) {
            $response = new RedirectResponse('/create-profile', 301);
            $response->setPrivate();
            $event->setResponse($response);
        }
    }
}
