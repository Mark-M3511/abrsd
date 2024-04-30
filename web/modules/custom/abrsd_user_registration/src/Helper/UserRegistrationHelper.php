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
        UserRegistration $userRegistration
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
                // Send an email to the user of the type 'user_register_pending_approval'
                _user_mail_notify('register_pending_approval', $user);
            }
        } else {
            $this->userRegistration->logger->info('User already exists with Email address: ' . $email);
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
                $uid = $this->userRegistration->searchUserByEmail($email);
                if (!empty($uid)) {
                    $user = User::load($uid);
                    // Update the user account with the provided values
                    $user->set('field_organization', $values['organization'] ?? $user->field_organization->value);
                    $user->set('field_interests', $values['interests'] ?? $user->field_interests->value);
                    $user->set('field_display_name', $values['display_name'] ?? $user->field_display_name->value);
                    $user->set('field_first_name', $values['first_name'] ?? $user->field_first_name->value);
                    $user->set('field_last_name', $values['last_name'] ?? $user->field_last_name->value);
                    $user->set('field_country', $values['country'] ?? $user->field_country->value);
                    $user->set('field_about_me', $values['about_me'] ?? $user->field_about_me->value);
                    $user->set('field_webform_submission_id', $values['sid'] ?? $user->field_webform_submission_id->value);
                    $user->enforceIsNew(FALSE)->save();
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
     */
    private function createUserAccount(array $values)
    {
        $result = null;

        try {
            $language_id = $this->userRegistration->languageManager->getDefaultLanguage()->getId();
            $password = $this->userRegistration->passwordGenerator->generate(30);
            $email = $values['confirm_email_address'];

            $user_values = [
                // 'pass' => $password,
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
}
