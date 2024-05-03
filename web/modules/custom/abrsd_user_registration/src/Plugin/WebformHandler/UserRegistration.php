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
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\abrsd_user_registration\Helper\UserRegistrationHelper;

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
  public $languageManager;

  /**
   * @var \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The configuration factory service.
   */
  public $configFactory;

  /**
   * @var PasswordGeneratorInterface $passwordGenerator
   *   The password generator service used for generating passwords.
   */
  public $passwordGenerator;

  /**
   * @var \Psr\Log\LoggerInterface
   *   The logger service.
   */
  public $logger;

  /**
   * @var bool
   *   A flag indicating whether the user already exists.
   */
  public $userExists;

  /**
   * @var \Drupal\Core\Session\AccountProxyInterface
   *   The current user.
   */
  public $currentUser;

  /**
   * @var \Drupal\file\Entity\File;
   *   The file system service.
   */
  private $fileEntity;

  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    LanguageManagerInterface $language_manager,
    ConfigFactoryInterface $config_factory,
    PasswordGeneratorInterface $password_generator,
    LoggerInterface $logger,
    AccountProxyInterface $account_proxy,
    EntityTypeManagerInterface $fileEntity
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->languageManager = $language_manager;
    $this->configFactory = $config_factory;
    $this->passwordGenerator = $password_generator;
    $this->logger = $logger;
    $this->userExists = FALSE;
    $this->currentUser = $account_proxy;
    $this->fileEntity = $fileEntity;
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
    $curren_user = $container->get('current_user');
    $fileEntity = $container->get('entity_type.manager');
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $language_manager,
      $config_factory,
      $password_generator,
      $logger,
      $curren_user,
      $fileEntity
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
      // Get the form id
      $form_id = $storage->getWebform()->id();
      // Check if the form id is 'user_registration'
      if ($form_id == 'user_registration') {
        // Get the email address from the current entity.
        $email = $storage->getElementData('confirm_email_address');
        // Check if a user with this email already exists
        $uid = $this->searchUserByEmail($email);
        // Set the userExists flag to TRUE if a user with the email exists
        $this->userExists = !empty($uid);
        // Set the user created ELement value to the userExists flag
        if ($storage->getElementData('user_created') === NULL) {
          $storage->setElementData('user_created', !$this->userExists);
        }
      }
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
    try {
      // Call the parent's post save method
      parent::postSave($webform_submission, $update);
      // Get the webforim id
      $form_id = $webform_submission->getWebform()->id();
      // Get the current user from the account proxy property
      $account = $this->currentUser->getAccount();
      // Load the user entity
      $user = User::load($account->id());
      $reg_helper = new UserRegistrationHelper($user, $webform_submission, $this);
      switch ($form_id) {
        case 'user_registration':
          if (!$update) {
            $reg_helper->addUserAccount();
          }
          break;
        case 'user_profile':
          if (!$update && $user->isAuthenticated()) {
            $reg_helper->updateUserAccount();
          }
          break;
      }
    } catch (\Exception $e) {
      $this->logger->error($e->getMessage());
    }
  }

  /**
   * Searches for a user by email.
   *
   * @param string $email
   *   The email address to search for.
   *
   * @return int|null
   *   The user ID if a user with the given email is found, NULL otherwise.
   */
  public function searchUserByEmail(string $email): ?int
  {
    // Query the user entity for the email address
    $query = \Drupal::entityTypeManager()
      ->getStorage('user')
      ->getQuery()->accessCheck(FALSE)
      ->condition('mail', $email);
    $uids = $query->execute();

    // return the id
    return !empty($uids) ? reset($uids) : NULL;
  }

  /**
   * Get the submission ID.
   *
   * @param int $uid
   *   The user ID.
   *
   * @return int|null
   *   The submission ID if found, NULL otherwise.
   */
  public static function getSubmissionId(int $uid = -1): ?int
  {
    $result = NULL;
    try {
      if ($uid == -1) {
        $uid = \Drupal::currentUser()->id();
      }
      $query = \Drupal::entityTypeManager()
        ->getStorage('user')
        ->getQuery()->accessCheck(FALSE)
        ->condition('uid', $uid);
      $uids = $query->execute();
      $user = User::load(reset($uids));
      $result = (int)$user->field_webform_submission_id->value;
    } catch (\Exception $e) {
      \Drupal::logger('abrsd_user_registration')->error($e->getMessage());
    }

    return $result;
  }
}
