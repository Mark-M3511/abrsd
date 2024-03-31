<?php

namespace Drupal\abrsd_user_registrat\Plugin\WebformHandler;

use Drupal\webform\Plugin\WebformHandlerBase;
use Drupal\webform\WebformSubmissionInterface;
use Drupal\user\Entity\User;

/**
 * Form submission handler.
 * The above code is a simple example of a Webform handler that creates a new user entity when a Webform submission is saved. The handler is defined in the annotation of the class, and the postSave method is called when a Webform submission is saved. The createUser method creates a new user entity using the values from the Webform submission.
 *  The  UserRegistrationHandler  class extends  WebformHandlerBase  and implements the  postSave  method. This method is called after the webform submission is saved. The  postSave  method receives the  WebformSubmissionInterface  object and a boolean value indicating whether the submission is an update.
 *  The  postSave  method gets the values from the submission and calls the  createUser  method to create a new user. The  createUser  method creates a new user entity, sets the username, email, and password, and saves the user.
 *  The  UserRegistrationHandler  class is annotated with the  @WebformHandler  annotation. This annotation provides metadata about the handler, such as the ID, label, category, description, cardinality, and results.
 *  The  UserRegistrationHandler  class is defined in the  UserRegistrationHandler.php  file in the  web/modules/custom/abrsd_user_registration/src/Plugin/WebformHandle  directory.
 *  Step 4: Create a webform handler service
 *  To register the webform handler, create a service definition in the  my_module.services.yml  file.
 *  Path: web/modules/custom/abrsd_user_registration/abrsd_user_registration.services.yml
 *
 * @WebformHandler(
 *   id = "abrsd_user_registration_handler",
 *   label = @Translation("ABRSD User Registration Handler"),
 *   category = @Translation("Custom"),
 *   description = @Translation("Handles user registrations for the ABRSD website."),
 *   cardinality = \Drupal\webform\Plugin\WebformHandlerInterface::CARDINALITY_SINGLE,
 *   results = \Drupal\webform\Plugin\WebformHandlerInterface::RESULTS_PROCESSED,
 * )
 */
class UserRegistration extends WebformHandlerBase
{

  /**
   * {@inheritdoc}
   */
  public function postSave(WebformSubmissionInterface $webform_submission, $update = TRUE)
  {
    // Get the values from the submission
    $values = $webform_submission->getData();
    if (!$update) {
      self::createUser($values);
    }
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
    $email = $values['email_address'];

    $required_values = [
      'pass' => $password,
      'mail' => $email,
      'name' => $email,
      'langcode' => $language_id,
    ];

    $user = User::create($required_values);

    $default_role = 'comment_contributor';

    $user->addRole($default_role);
    // Optional data
    $user->set('init', $email)
      ->set('status', 0)
      ->set('preferred_langcode', $language_id)
      ->set('preferred_admin_langcode', $language_id);
    // Set values for custom fields
    $user->set('field_first_name', $values['first_name']);
    // finally save the new entity
    if ($user->enforceIsNew()->save()) {
      $result = $user;
    }

    return $result;
  }
}
