<?php

namespace Drupal\abrsd_user_registrat\Plugin\WebformHandler;

use Drupal\webform\Plugin\WebformHandlerBase;
use Drupal\webform\WebformSubmissionInterface;
use Drupal\user\Entity\User;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form submission handler.
 *
 * @WebformHandler(
 *   id = "abrsd_user_registration",
 *   label = @Translation("ABRSD User Registration"),
 *   category = @Translation("ABRSD User Registration"),
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

  public function __construct(array $configuration, $plugin_id, $plugin_definition, LanguageManagerInterface $language_manager, ConfigFactoryInterface $config_factory)
  {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->languageManager = $language_manager;
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public function postSave(WebformSubmissionInterface $webform_submission, $update = TRUE)
  {
    // Get the values from the submission
    $values = $webform_submission->getData();
    if (!$update) {
      // Search for user by email address
      $email = $values['confirm_email_address'];
      $users = \Drupal::entityTypeManager()
        ->getStorage('user')
        ->loadByProperties(['mail' => $email]);
      if (empty($users)) {
        self::createUser($values);
      }
    }
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
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition)
  {
    $language_manager = $container->get('language_manager');
    $config_factory = $container->get('config.factory');
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $language_manager,
      $config_factory
    );
  }

  /**
   * Create a new user.
   *
   * @param array $values
   *   The values from the webform submission.
   */
  private static function createUser(array $values)
  {
    // Create a new user entity
    $result = null;

    $language_id = \Drupal::languageManager()->getCurrentLanguage()->getId();
    // Get a temporary password
    $password = user_password(30);
    $email = $values['confirm_email_address'];

    $required_values = [
      'pass' => $password,
      'mail' => $email,
      'name' => $email,
      'langcode' => $language_id,
    ];

    $other_values = [
      'field_display_name' => $values['user_name'],
      'field_organization' => $values['organization'],
      'field_user_name' => $values['user_name'],
      'field_interests' => $values['interests'],
    ];

    $user = User::create($required_values);

    $default_role = 'comment_contributor';

    $user->addRole($default_role);
    // Optional data
    $user->set('init', $email)
      ->set('status', 0)
      ->set('preferred_langcode', $language_id)
      ->set('preferred_admin_langcode', $language_id);
    // Use the other_values array to set additional fields
    foreach ($other_values as $field_name => $field_value) {
      $user->set($field_name, $field_value);
    }
    // finally save the new entity
    if ($user->enforceIsNew()->save()) {
      $result = $user;
    }

    return $result;
  }
}
