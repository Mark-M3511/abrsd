<?php

namespace Drupal\abrsd_user_registration\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Event subscriber for redirecting user/register routes.
 */
class UserRegistrationRedirectSubscriber implements EventSubscriberInterface
{
    /**
     * The configuration factory.
     *
     * @var \Drupal\Core\Config\ConfigFactoryInterface
     */
    protected $configFactory;

    /**
     * The logger.
     *
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;


    /**
     * Constructs a new UserRegistrationRedirectSubscriber object.
     *
     * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
     *   The configuration factory.
     */
    public function __construct(
        ConfigFactoryInterface $config_factory,
    ) {
        $this->configFactory = $config_factory;
    }

    /**
     * The configuration factory.
     *
     * @var \Drupal\Core\Config\ConfigFactoryInterface
     */
    public static function create(ContainerInterface $container)
    {
        $config_factory = $container->get('config.factory');
        $request_stack =  $container->get('request_stack');
        $logger = $container->get('logger.factory')->get('abrsd_user_registration');
        return new static(
            $config_factory,
            $request_stack,
            $logger
        );
    }


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
        $allowed_paths = [
            '/user/register',
            '/register',
        ];
        // $request = $event->getRequest();
        $request = $event->getRequest();
        // Check if the current path is in the allowed paths.
        if (!in_array($request->getPathInfo(), $allowed_paths, true)) {
            return;
        }

        // Get the redirect path from the config
        $redirect_path = $this->getRedirectPathFromConfig('/user/register', 'redirects');

        // Only redirect for anonymous users.
        if (\Drupal::currentUser()->isAnonymous()) {
            $response = new RedirectResponse($redirect_path, 301);
            $event->setResponse($response);
        }
    }

    /**
     * Retrieves the redirect path from a configuration based on the given path and configuration name.
     *
     * @param string $path The path to retrieve the redirect for.
     * @param string $config_name The name of the configuration to retrieve the redirect from.
     * @return string|null The redirect path if found, or null if not found.
     */
    public function getRedirectPathFromConfig(string $path, string $config_name)
    {
        $config = $this->configFactory->get('abrsd_user_registration.settings');
        $redirect_maps = $config->get($config_name);

        if (isset($redirect_maps[$path])) {
            return $redirect_maps[$path];
        }

        return null;
    }
}
