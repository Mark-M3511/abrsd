<?php

namespace Drupal\abrsd_user_registration\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\user\Entity\User;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

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
     * The current user.
     *
     * @var \Drupal\Core\Session\AccountProxyInterface
     */
    protected $currentUser;

    /**
     * The session service.
     *
     * @var \Drupal\Core\Session\SessionInterface
     */
    protected $session;


    /**
     * Constructs a new UserRegistrationRedirectSubscriber object.
     *
     * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
     *   The configuration factory.
     * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger
     *   The logger.
     * @param \Drupal\Core\Session\AccountInterface $currentUser
     *  The current user.
     * @param \Drupal\Core\Session\SessionInterface $session
     * The session service.
     */
    public function __construct(
        ConfigFactoryInterface $config_factory,
        LoggerChannelFactoryInterface $logger,
        AccountInterface $currentUser,
        SessionInterface $session
    ) {
        $this->configFactory = $config_factory;
        $this->logger = $logger->get('abrsd_user_registration');
        $this->currentUser = $currentUser;
        $this->session = $session;
    }

    /**
     * The configuration factory.
     *
     * @var \Drupal\Core\Config\ConfigFactoryInterface
     */
    public static function create(ContainerInterface $container): self
    {
        $config_factory = $container->get('config.factory');
        $logger = $container->get('logger.factory');
        $currentUser = $container->get('current_user');
        $session = $container->get('session');

        return new static(
            $config_factory,
            $logger,
            $currentUser,
            $session
        );
    }


    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        // This method returns an array of events this subscriber subscribes to. The
        // key is the event name and the value is the method that should be called when
        // the event is dispatched. The higher the number, the earlier the method is called.
        $events = [
            KernelEvents::REQUEST => [
                ['onRedirectUserRegister', 100],
                ['onCommentContributorLoggedIn', 100],
            ],
        ];

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
            '/user/profile',
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
                // Redirect the user to the specified path.
                $response_code = Response::HTTP_MOVED_PERMANENTLY;
                $url = $redirect_path;
                if ($redirect_path === NULL) {
                    $response_code = Response::HTTP_UNAUTHORIZED;
                    $url = '/user/register';
                }
                $response = new RedirectResponse($url, $response_code);
                $event->setResponse($response);
                // If this response code is 301 then log the redirect
                if ($response->getStatusCode() === Response::HTTP_MOVED_PERMANENTLY) {
                    $this->logger->info('Redirecting user from @from to @to', [
                        '@from' => $path_info,
                        '@to' => $redirect_path,
                    ]);
                }
            }
        } catch (\Exception $e) {
            $this->logger->error('Error redirecting user: @error', ['@error' => $e->getMessage()]);
        }
    }

    /**
     * This method is called whenever the KernelEvents::REQUEST event is
     * dispatched. It checks if the current user has the Comment Contributor role
     * then redirects the user to the profile page.
     *
     * @param \Symfony\Component\HttpKernel\Event\RequestEvent $event
     */
    public function onCommentContributorLoggedIn(RequestEvent $event)
    {
        try {

            $path_info = $event->getRequest()?->getPathInfo();
            $path_parts = explode('/', ltrim($path_info, " \n\r\t\v\0/"));

            if (empty($path_parts) || $path_parts[0] !== 'user') {
                return;
            }

            if ($path_parts[1] === $this->currentUser->id()) {
                if ($this->session->get('user_pass_reset')) {
                    // Remove the 'user_pass_reset' key from the session
                    $this->session->remove('user_pass_reset');
                } else {
                    // Redirect the user to the profile page if they have the Comment Contributor role
                    $user = User::load($this->currentUser->id());
                    if ($user?->hasRole('comment_contributor')) {
                        $response = new RedirectResponse('/user/profile');
                        $event->setResponse($response);
                    }
                }
            } elseif ($path_parts[1] === 'reset') {
                // Set a key/value pair in the session to indicate the one-time login link has been used
                $this->session->set('user_pass_reset', TRUE);
            }
        } catch (\Exception $e) {
            $this->logger->error('Error: @error', ['@error' => $e->getMessage()]);
        }
    }

    /**
     * Retrieves the redirect path from a configuration based on the given path and configuration name.
     *
     * @param string $path The path to retrieve the redirect for.
     * @param string $config_name The name of the configuration to retrieve the redirect from.
     * @return string|null The redirect path if found, or null if not found.
     */
    public function getRedirectPathFromConfig(string $path, string $config_name): ?string
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
