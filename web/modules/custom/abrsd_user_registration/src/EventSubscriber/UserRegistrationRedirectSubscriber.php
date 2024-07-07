<?php

namespace Drupal\abrsd_user_registration\EventSubscriber;

use Drupal\abrsd_user_registration\Plugin\WebformHandler\UserRegistration;
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
use Drupal\abrsd_user_registration\Helper\UserRegistrationHelper;
use Drupal\Core\Path\PathValidator;

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
     * The request object.
     *
     * @var \Symfony\Component\HttpFoundation\Request
     */
    protected $request;

    /**
     * The path validator service.
     *
     * @var \Drupal\Core\Path\PathValidator
     */
    protected $pathValidator;

    /**
     * The post data.
     *
     * @var array
     */
    protected $postData;


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
        SessionInterface $session,
        PathValidator $path_validator
    ) {
        $this->configFactory = $config_factory;
        $this->logger = $logger->get('abrsd_user_registration');
        $this->currentUser = $currentUser;
        $this->session = $session;
        $this->pathValidator = $path_validator;
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
        $path_validator = $container->get('path.validator');

        return new static(
            $config_factory,
            $logger,
            $currentUser,
            $session,
            $path_validator
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
            $this->request = $event->getRequest();
            $path_info = $this->request?->getPathInfo();

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
                    $response_code = Response::HTTP_TEMPORARY_REDIRECT;
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
            // Get the URL path from the request.
            $this->request = $event->getRequest();
            // Get the URL path from the request
            $path_info = $this->request?->getPathInfo();
            // Get the post data from the request
            $this->postData = $this->request?->request->all();
            // Get the path parts from the path info
            $path_parts = explode('/', ltrim($path_info, " \n\r\t\v\0/"));
            // Use the path validator service to get the URL object if the path is valid. This method
            // may return false if the path is not valid.
            $url_object = $this->pathValidator->getUrlIfValid($path_info);
            // If the URL object is not null, get the route name and parameters
            $url_param_uid = NULL;
            $arr_route_names = ['entity.user.canonical', 'entity.user.edit_form'];
            if (is_object($url_object) && in_array($url_object->getRouteName(), $arr_route_names, TRUE)) {
                // This is a user path, extract the user ID from the route parameters.
                $url_param_uid = $url_object->getRouteParameters()['user'];
            }
            // Check if the user ID in the route parameters is the same as the current user ID
            if ($url_param_uid === $this->currentUser->id()) {
                // Get the password fields from the post data. The password values will be present
                // in the post data when the user is changing their password. These values will be
                // present after the user submits the password reset form or Drupal's user edit form.
                // Otherwise, the password values will be null.
                $pass_1 = $this->postData['pass']['pass1'] ?? NULL;
                $pass_2 = $this->postData['pass']['pass2'] ?? NULL;
                // Check if the password fields are not empty and match
                if (($pass_1 == NULL && $pass_2 == NULL)) {
                    // Check if the path is not a user path then return so that the user can take action
                    if (isset($path_parts[0]) && $path_parts[0] !== 'user') {
                        return;
                    }
                    // Check if the path is an edit path then return so that the user can edit their password for example
                    if (isset($path_parts[2]) && $path_parts[2] === 'edit') {
                        return;
                    }
                }
                // Check if the 'user_pass_reset' key exists in the session
                if ($this->session->get('user_pass_reset')) {
                    // Remove the 'user_pass_reset' key from the session
                    $this->session->remove('user_pass_reset');
                }
                // Redirect the user to the profile page if they have the Comment Contributor role
                $user = User::load($this->currentUser->id());
                // Check if user is authenticated
                if ($user?->isAuthenticated()) {
                    if ($pass_1 === $pass_2) {
                        UserRegistrationHelper::updatePassword($user, $pass_1);
                        $response = new RedirectResponse('/user/profile');
                        $event->setResponse($response);
                    } else {
                        throw new \Exception('Passwords do not match.');
                    }
                }
            } elseif ($url_param_uid === 'reset') {
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
