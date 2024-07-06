<?php

namespace Drupal\abrsd_user_registration\Helper;

use Drupal\abrsd_user_registration\Plugin\WebformHandler\UserRegistration;
use Drupal\user\Entity\User;
use Drupal\webform\WebformSubmissionInterface;
use Drupal\file\Entity\File;


class UserRegistrationHelper
{
    /*
    * @var \Drupal\user\Entity\User
    */
    private $user;

    /*
    * @var array
    */
    private $webform_submission;

    /*
     * @var \Drupal\abrsd_user_registration\Plugin\WebformHandler\UserRegistration
     */
    private $userRegistration;

    /**
     * UserRegistrationHelper constructor.
     *
     * @param \Drupal\user\Entity\User $user
     * @param \Drupal\webform\WebformSubmissionInterface $webform_submission
     * @param \Drupal\abrsd_user_registration\Plugin\WebformHandler\UserRegistration $userRegistration
     */
    public function __construct(
        User $user,
        WebformSubmissionInterface $webform_submission,
        UserRegistration $userRegistration,
        public int $accountStatus = 1
    ) {
        $this->user = $user;
        $this->webform_submission = $webform_submission;
        $this->userRegistration = $userRegistration;
    }

    /**
     * Add a new user account.
     */
    public function addUserAccount()
    {
        $values = $this->webform_submission->getData();
        $values['sid'] = $this->webform_submission->id();
        $email = $values['confirm_email_address'];
        // Create a new user account
        if (!$this->userRegistration->userExists) {
            $user = $this->createUserAccount($values);
            if ($user) {
                $this->userRegistration->logger->info('User created: ' . $user->mail->value);
                // Send an email to the user of the type 'register_no_approval_required'
                _user_mail_notify('register_no_approval_required', $user);
            }
        } else {
            $this->userRegistration->logger->info('User already exists with email address: ' . $email);
        }
    }

    /**
     * Updates the user account with the provided values.
     *
     * @throws \Exception
     *   If there is an error updating the user account.
     */
    public function updateUserAccount()
    {
        try {
            // Get the form values
            $values = $this->webform_submission->getData();
            // Get the entity id
            $values['sid'] = $this->webform_submission->id();
            // Get the user entity
            $user = $this->user;
            // if current user is admin load the user entity using the email address
            $email = '';
            if ($this->userRegistration->currentUser->hasPermission('post comments')) {
                if (isset($values['confirm_email_address'])) {
                    // Get the email from the Registration form
                    $email = $values['confirm_email_address'];
                } else {
                    // Get the email from the Profile Update form
                    $email = $values['signup_email'] ?: $user->mail->value;
                }
            } else {
                // Make sure the current user is the owner of the account
                if ($this->userRegistration->currentUser->id() !== $user->id()) {
                    throw new \Exception('You are not authorized to update this account.');
                }
            }

            $uid = 0;
            if (!empty($email)) {
                $uid = static::searchUserByEmail($email);
                if (!empty($uid)) {
                    $user = User::load($uid);
                    // Update the user account with the provided values
                    $user->set('mail', $values['mail'] ?? $user->mail->value);
                    $user->set('name', $values['mail'] ?? $user->name->value);
                    $user->set('field_organization', $values['organization'] ?? $user->field_organization->value);
                    $user->set('field_interests', $values['interests'] ?? $user->field_interests->value);
                    $user->set('field_display_name', $values['display_name'] ?? $user->field_display_name->value);
                    $user->set('field_first_name', $values['first_name'] ?? $user->field_first_name->value);
                    $user->set('field_last_name', $values['last_name'] ?? $user->field_last_name->value);
                    $user->set('field_country', $values['country'] ?? $user->field_country->value);
                    $user->set('field_about_me', $values['about_me'] ?? $user->field_about_me->value);
                    $user->set('field_webform_submission_id', $values['sid'] ?? $user->field_webform_submission_id->value);
                    // Save the province or state value based on the selected country
                    $this->saveProvinceStateUser($user, $values);
                    // Save the postal or zip code based on the country
                    $this->savePostalZipUser($user, $values);
                    // Save the user entity
                    $user->enforceIsNew(FALSE)->save();
                    // Update the user profile picture
                    $this->updateProfilePicture($values['profile_picture'], $user);
                }
            }
        } catch (\Exception $e) {
            $this->userRegistration->logger->error($e->getMessage());
        }
    }

    /**
     * Updates the profile picture of a user.
     *
     * @param int|null $fid The file ID of the profile picture.
     * @param User $user The user object to update.
     *
     * @throws \Exception If an error occurs while updating the profile picture.
     */
    public function updateProfilePicture(?int $fid, User $user)
    {
        try {
            if ($fid) {
                $file = File::load($fid);
                if ($file) {
                    $user->set('user_picture', $file->id());
                }
            } else {
                $user->set('user_picture', NULL);
            }
            $user->enforceIsNew(FALSE)->save();
        } catch (\Exception $e) {
            $this->userRegistration->logger->error($e->getMessage());
        }
    }

    /**
     * Create a new user.
     *
     * @param array $values
     *   The values from the webform submission.
     * @return \Drupal\user\Entity\User|null
     */
    private function createUserAccount(array $values): ?User
    {
        $result = null;

        try {
            $language_id = $this->userRegistration->languageManager->getDefaultLanguage()->getId();
            $password = $this->userRegistration->passwordGenerator->generate(30);
            $email = $values['confirm_email_address'];

            $user_values = [
                'mail' => $email,
                'name' => $email,
                'langcode' => $language_id,
                'init' => $email,
                'status' => $this->accountStatus,
                'preferred_langcode' => $language_id,
                'preferred_admin_langcode' => $language_id,
                'field_organization' => $values['organization'],
                'field_interests' => $values['interests'],
                'field_display_name' => generate_unique_username($email),
                'field_first_name' => $values['first_name'],
                'field_last_name' => $values['last_name'],
                'field_country' => $values['country'],
                'field_about_me' => $values['about_me'],
                'field_webform_submission_id' => $values['sid'],
            ];

            // Save the province or state value based on the selected country
            $this->saveProvinceState($user_values, $values);
            // Save the postal or zip code based on the country
            $this->savePostalZip($user_values, $values);

            // Create a new user
            $user = User::create($user_values);
            // Set the user role
            $default_role = 'comment_contributor';
            $custom_role = $this->userRegistration->configFactory->get('abrsd_user_registration.settings')
                ->get('roles')[1] ?? $default_role;
            // Add the custom role to the user
            $user->addRole($custom_role);
            // Hash and set the new password
            $user->setPassword($password);
            // Save the user
            if ($user->enforceIsNew()->save()) {
                $result = $user;
            }
        } catch (\Exception $e) {
            $this->userRegistration->logger->error($e->getMessage());
            // throw $e;
        }

        return $result;
    }

    /**
     * Returns an array mapping country codes to province/state field names.
     *
     * @return array
     *   An array where the keys are country codes and the values are the corresponding
     *   province/state field names.
     */
    public static function getCountryProvStateMap()
    {
        return [
            'CA' => 'province',
            'US' => 'state',
        ];
    }

    /**
     * Returns an array mapping country codes to postal/zip code field names.
     *
     * @return array
     *   An array where the keys are country codes and the values are the corresponding
     *   postal/zip code field names.
     */
    public static function getCountryPostalZip()
    {
        return [
            'CA' => 'postal_code',
            'US' => 'zip',
        ];
    }

    /**
     * Saves the province or state value based on the selected country.
     *
     * @param array &$user_values The array containing user values.
     * @param array $values The array containing the form values.
     * @return void
     */
    private function saveProvinceState(array &$user_values, array $values): void
    {
        $country_prov_state_map = self::getCountryProvStateMap();
        try {
            if (array_key_exists($values['country'], $country_prov_state_map)) {
                $user_values['field_province_state'] = $values[$country_prov_state_map[$values['country']]];
            }
        } catch (\Exception $e) {
            // Handle exception or log error
            $this->userRegistration->logger->error($e->getMessage());
        }
    }

    /**
     * Saves the province or state information for a user based on the selected country.
     *
     * @param User $user The user object to save the province or state information for.
     * @param array $values An array containing the country, province, and state values.
     * @return void
     */
    private function saveProvinceStateUser(User $user, array $values): void
    {
        $country_prov_state_map = self::getCountryProvStateMap();
        try {
            if (array_key_exists($values['country'], $country_prov_state_map)) {
                $field_key = $country_prov_state_map[$values['country']];
                $user->set('field_province_state', $values[$field_key]);
            }
        } catch (\Exception $e) {
            $this->userRegistration->logger->error($e->getMessage());
        }
    }

    /**
     * Saves the postal or zip code based on the country.
     *
     * @param array &$user_values The array containing user values.
     * @param array $values The array containing the form values.
     * @return void
     */
    private function savePostalZip(array &$user_values, array $values): void
    {
        $country_postal_zip_map = self::getCountryPostalZip();
        try {
            if (array_key_exists($values['country'], $country_postal_zip_map)) {
                $user_values['field_postal_zip_code'] = $values[$country_postal_zip_map[$values['country']]];
            }
        } catch (\Exception $e) {
            // Handle exception or log error
            $this->userRegistration->logger->error($e->getMessage());
        }
    }

    /**
     * Saves the postal code or zip code for a user based on the country.
     *
     * @param User $user The user object.
     * @param array $values An array of values containing the country and the postal/zip code.
     * @return void
     */
    private function savePostalZipUser(User $user, array $values): void
    {
        $country_postal_zip_map = self::getCountryPostalZip();
        try {
            if (array_key_exists($values['country'], $country_postal_zip_map)) {
                $field_key = $country_postal_zip_map[$values['country']];
                $user->set('field_postal_zip_code', $values[$field_key]);
            }
        } catch (\Exception $e) {
            $this->userRegistration->logger->error($e->getMessage());
        }
    }

    /**
     * Updates the password for a user.
     *
     * @param \Drupal\user\Entity\User $user
     *   The user entity for which the password needs to be updated.
     * @param string|null $new_password
     *   The new password to be set for the user. If null, the password will not be updated.
     *
     * @throws \Exception
     *   If an error occurs while updating the password.
     */
    static function updatePassword(User $user, ?string $new_password)
    {
        try {
            if (!empty($new_password)) {
                // Save the new password to the user entity
                $user->setPassword($new_password)->enforceIsNew(FALSE)->save();
            }
        } catch (\Exception $e) {
            \Drupal::logger('abrsd_user_registration')->error($e->getMessage());
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
    static public function searchUserByEmail(string $email): ?int
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
}
