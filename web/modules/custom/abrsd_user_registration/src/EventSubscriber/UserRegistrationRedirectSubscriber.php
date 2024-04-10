<?php

namespace Drupal\abrsd_user_registration\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;

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
     * @var Drupal\Core\Logger\LoggerChannelFactoryInterface
     */
    protected $logger;


    /**
     * Constructs a new UserRegistrationRedirectSubscriber object.
     *
     * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
     *   The configuration factory.
     * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger
     *   The logger.
     */
    public function __construct(
        ConfigFactoryInterface $config_factory,
        LoggerChannelFactoryInterface $logger
    ) {
        $this->configFactory = $config_factory;
        $this->logger = $logger->get('abrsd_user_registration');
    }

    /**
     * The configuration factory.
     *
     * @var \Drupal\Core\Config\ConfigFactoryInterface
     */
    public static function create(ContainerInterface $container)
    {
        $config_factory = $container->get('config.factory');
        $logger = $container->get('logger.factory');
        return new static(
            $config_factory,
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
        $events[KernelEvents::REQUEST][] = ['onRedirectUserRegister', 100];
        return $events;
    }

    /**
     * This method is called whenever the KernelEvents::REQUEST event is
     * dispatched.
     *
     * @param \Symfony\Component\HttpKernel\Event\RequestEvent $event
     */
    public function onRedirectUserRegister(RequestEvent $event)
    {
        $allowed_paths = [
            '/user/register',
            '/register',
        ];

        try {
            // Get the URL path from the request.
            $path_info = $event->getRequest()?->getPathInfo();

            // Check if the current path is in the allowed paths.
            if (!in_array($path_info, $allowed_paths, TRUE)) {
                return;
            }

            // Get the redirect path from the config
            $redirect_path = $this->getRedirectPathFromConfig($path_info, 'redirects');

            // Only redirect for anonymous users.
            if (\Drupal::currentUser()->isAnonymous()) {
                $response = new RedirectResponse($redirect_path, 301);
                $event->setResponse($response);
                // Log the redirect.
                $this->logger->info('Redirecting user from @from to @to', [
                    '@from' => $path_info,
                    '@to' => $redirect_path,
                ]);
            }
        } catch (\Exception $e) {
            $this->logger->error('Error redirecting user: @error', ['@error' => $e->getMessage()]);
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
        try {
            $config = $this->configFactory->get('abrsd_user_registration.settings');
            $redirect_maps = $config->get($config_name);
            return $redirect_maps[$path] ?? null;
        } catch (\Exception $e) {
            $this->logger->error('Error getting configuration: @error', ['@error' => $e->getMessage()]);
        }

        return null;
    }
}
