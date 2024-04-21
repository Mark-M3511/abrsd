<?php

namespace Drupal\abrsd_user_registration\Plugin\WebformHandler;

use Drupal\webform\Plugin\WebformHandlerBase;
use Drupal\webform\WebformSubmissionInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Password\PasswordGeneratorInterface;
use Psr\Log\LoggerInterface;
use Drupal\user\Entity\User;

/**
 * Form submission handler.
 *
 * @WebformHandler(
 *   id = "abrsd_user_registration",
 *   label = @Translation("ABRSD User Registration"),
 *   category = @Translation("Form Handlers"),
 *   description = @Translation("Handles user registrations for the ABRSD website."),
 *   cardinality = \Drupal\webform\Plugin\WebformHandlerInterface::CARDINALITY_SINGLE,
 *   results = \Drupal\webform\Plugin\WebformHandlerInterface::RESULTS_PROCESSED,
 * )
 */
final class UserRegistration extends WebformHandlerBase
{

  /**
   * @var \Drupal\Core\Language\LanguageManagerInterface
   *   The language manager service.
   */
  protected $languageManager;

  /**
   * @var \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The configuration factory service.
   */
  protected $configFactory;

  /**
   * @var PasswordGeneratorInterface $passwordGenerator
   *   The password generator service used for generating passwords.
   */
  protected $passwordGenerator;

  /**
   * @var \Psr\Log\LoggerInterface
   *   The logger service.
   */
  protected $logger;

  /**
   * @var bool
   *   A flag indicating whether the user already exists.
   */
  private $userExists;

  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    LanguageManagerInterface $language_manager,
    ConfigFactoryInterface $config_factory,
    PasswordGeneratorInterface $password_generator,
    LoggerInterface $logger
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->languageManager = $language_manager;
    $this->configFactory = $config_factory;
    $this->passwordGenerator = $password_generator;
    $this->logger = $logger;
    $this->userExists = FALSE;
  }

  /**
   * Creates a new instance of the UserRegistration class.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The container interface.
   * @param array $configuration
   *   An array of configuration settings.
   * @param string $plugin_id
   *   The plugin ID.
   * @param mixed $plugin_definition
   *   The plugin definition.
   *
   * @return static
   *   A new instance of the UserRegistration class.
   */
  public static function create(
    ContainerInterface $container,
    array $configuration,
    $plugin_id,
    $plugin_definition
  ) {
    $language_manager = $container->get('language_manager');
    $config_factory = $container->get('config.factory');
    $password_generator = $container->get('password_generator');
    $logger = $container->get('logger.factory')->get('abrsd_user_registration');
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $language_manager,
      $config_factory,
      $password_generator,
      $logger
    );
  }

  /**
   * Implements hook_webform_submission_presave() for the User Registration webform handler.
   *
   * This function is called before saving a webform submission.
   * It checks if a user with the submitted email address already exists
   * and throws an exception if so.
   *
   * @param \Drupal\webform\WebformSubmissionInterface $storage
   *   The webform submission storage object.
   *
   * @throws \Exception
   *   Thrown if a user with the submitted email or display_name address already exists.
   */
  public function preSave(WebformSubmissionInterface $storage)
  {
    try {
      // Call the parent preSave method
      parent::preSave($storage);

      // Get the email address from the current entity.
      $email = $storage->getElementData('confirm_email_address');

      // Check if a user with this email already exists
      $query = \Drupal::entityTypeManager()
        ->getStorage('user')
        ->getQuery()->accessCheck(FALSE)
        ->condition('mail', $email);
      $uids = $query->execute();

      $this->userExists = !empty($uids);

      // Set the user created ELement value to the userExists flag
      $storage->setElementData('user_created', !$this->userExists);
    } catch (\Exception $e) {
      $this->logger->error($e->getMessage());
      throw $e;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function postSave(WebformSubmissionInterface $webform_submission, $update = TRUE)
  {
    // Get the values from the submission
    $values = $webform_submission->getData();
    if (!$update) {
      try {
        // Search for the submitter's email address in the Drupal users table (mail field)
        $email = $values['confirm_email_address'];
        // If no user is found, create a new user
        if (!$this->userExists) {
          // Get the entity id
          $values['sid'] = $webform_submission->id();
          $user = $this->createUserAccount($values);
          if ($user) {
            $this->logger->info('User created: ' . $user->mail->value);
            // Send an email to the user of the type 'user_register_pending_approval'
            _user_mail_notify('register_pending_approval', $user);
          }
        } else {
          $this->logger->info('User already exists with Email address: ' . $email);
        }
      } catch (\Exception $e) {
        $this->logger->error($e->getMessage());
      }
    }
  }

  /**
   * Create a new user.
   *
   * @param array $values
   *   The values from the webform submission.
   */
  private function createUserAccount(array $values)
  {
    $result = null;

    try {
      $language_id = $this->languageManager->getCurrentLanguage()->getId();
      $password = $this->passwordGenerator->generate(30);
      $email = $values['confirm_email_address'];

      $user_values = [
        'pass' => $password,
        'mail' => $email,
        'name' => $email,
        'langcode' => $language_id,
        'init' => $email,
        'status' => 0,
        'preferred_langcode' => $language_id,
        'preferred_admin_langcode' => $language_id,
        'field_organization' => $values['organization'],
        'field_interests' => $values['interests'],
        'field_display_name' => $values['display_name'],
        'field_first_name' => $values['first_name'],
        'field_last_name' => $values['last_name'],
        'field_country' => $values['country'],
        'field_about_me' => $values['about_me'],
        'field_webform_submission_id' => $values['sid'],
      ];

      $user = User::create($user_values);

      $default_role = 'comment_contributor';
      $custom_role = $this->configFactory->get('abrsd_user_registration.settings')
        ->get('roles')[1] ?? $default_role;

      $user->addRole($custom_role);

      if ($user->enforceIsNew()->save()) {
        $result = $user;
      }
    } catch (\Exception $e) {
      $this->logger->error($e->getMessage());
      // throw $e;
    }

    return $result;
  }
}
