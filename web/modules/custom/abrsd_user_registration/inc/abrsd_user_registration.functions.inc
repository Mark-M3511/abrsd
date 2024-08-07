<?php
/**
 * @file abrsd_user_registration.functions.inc
 * Contains helper functions for the abrsd_user_registration module.
 */

use Drupal\user\Entity\User;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\webform\Entity\WebformSubmission;
use Drupal\file\Entity\File;
use Drupal\image\Entity\ImageStyle;

 /**
 * Sets the default value for a form element based on submission data or user field value.
 *
 * @param array &$form_elements
 *   The array of form elements.
 * @param array $submission_data
 *   The array of submission data.
 * @param User $user
 *   The User object.
 * @param string $key
 *   The key used to identify the form element.
 * @param string $prefix
 *  The prefix used to identify the container element.
 */
function _set_default_value(
    array &$form_elements,
    array $submission_data,
    User $user,
    string $key,
    string $prefix = 'field_'
) {
    // Get the container element by splitting the key
    $container = explode('|', $key)[0] ?? 'container';
    // Get the element id by splitting the key
    $skey = explode('|', $key)[1] ?? $key;
    // if $skey == 'container|signup_email' set the field key to mail, otherwise use the value of $skey
    $fkey = ($skey == 'signup_email') ? 'mail' : $skey;
    $fkey = ($fkey == 'mail') ? $fkey : $prefix . $fkey;
    // Set the default value
    if (array_key_exists($skey, $submission_data) && !empty($submission_data[$skey])) {
        $form_elements[$container][$skey]['#default_value'] = $submission_data[$skey];
    } elseif (isset($user->{$fkey}) && isset($user->{$fkey}->value)) {
        $form_elements[$container][$skey]['#default_value'] = $user->{$fkey}->value;
    }
}

/**
 * Implements hook_form_FORM_ID_alter() for the user_pass form.
 *
 * Modifies the user_pass form by changing the title of the 'name' form element
 * and the label of the 'submit' button. It also adds a submit callback function
 * '_abrsd_user_registration_user_pass_redirect'.
 *
 * @param array $form
 *   An associative array containing the structure of the form.
 * @param \Drupal\Core\Form\FormStateInterface $form_state
 *   The current state of the form.
 */
function _abrsd_user_registration_user_pass_config(array &$form, FormStateInterface &$form_state)
{
    // Check the current URL for the mode parameter
    $mode = \Drupal::request()->query->get('mode');
    if (empty($mode)) {
        // Check if the 'name' form element exists
        if (isset($form['name'])) {
            // Replace the title of the 'name' form element
            $form['name']['#title'] = t('Enter the email address registered with your account');
        }
        // / Check if the 'submit' action exists
        if (isset($form['actions']['submit'])) {
            // Change the label of the 'submit' button
            $form['actions']['submit']['#value'] = t('Continue');
        }
        // Add a submit callback function
        $form['#submit'][] = '_abrsd_user_registration_user_pass_redirect';
    } else {
        // Check if the mode is 'reset_message'
        if ($mode === 'reset_message') {
            // Change the title of the form
            $form['#title'] = t('Check your email');
            // Hide the submit button and name field
            $form['name']['#access'] = FALSE;
            $form['mail']['#access'] = FALSE;
            $form['actions']['submit']['#access'] = FALSE;
            // Add a message to the form using an inline template
            $form['message'] = [
                '#type' => 'inline_template',
                '#template' => '
                   <p class="user-pass-message text-center">{{ message_1 }}</p>
                   <p class="user-pass-message text-center">{{ message_2 }}</p>
                   <p class="user-pass-message text-center">{{ message_3 }}</p>
                   <p class="user-pass-message text-center">{{ message_4 }}</p>
                   ',
                '#context' => [
                    'message_1' => t('Check your email to reset your password.'),
                    'message_2' => t("If there's an ABRSD Peel account asscciated with your email you will receive a link in your inbox."),
                    'message_3' => t("If you don't receive an email, please check your spam folder or <a href=\"/user/password\" title=\"Reset your password\">try again</a>."),
                    'message_4' => t("Still need help? <a href=\"/contact-us\" title=\"Contact Us\">Contact us.</a>."),
                ],
                // '#weight' => -1,
                '#prefix' => '<div id="reset-message" class="reset-message">',
                '#suffix' => '</div>',
            ];
        }
    }
}

/**
 * Redirects the user after submitting the user password form.
 *
 * This function is called when the user submits the user password form.
 * It redirects the user to a specific route with optional query parameters.
 *
 * @param array &$form
 *   The form array.
 * @param \Drupal\Core\Form\FormStateInterface $form_state
 *   The form state object.
 */
function _abrsd_user_registration_user_pass_redirect(array &$form, FormStateInterface $form_state)
{
    // Redirect the user to the user password reset form with the 'mode' query parameter set to 'reset_message'
    $form_state->setRedirectUrl(Url::fromRoute('user.pass', [], ['query' => ['mode' => 'reset_message']]));
}

/**
 * Modifies the format selector UI element.
 *
 * This function is called to modify the format selector UI element in a form.
 * It removes the format, help, and guidelines elements and hides the format selector.
 *
 * @param array $element
 *   The form element to be modified.
 * @param \Drupal\Core\Form\FormStateInterface $form_state
 *   The current state of the form.
 *
 * @return array
 *   The modified form element.
 */
function _modify_format_selector_ui(array $element, FormStateInterface $form_state): array
{
    if (isset($element[0]['format'])) {
        $el = &$element[0]['format'];
        // Remove format, help, guidelines elements
        // unset($element[0]['format']['guidelines']);
        // unset($element[0]['format']['help']);
        // Hide the format selector
        $el['format']['#access'] = FALSE;
    }

    return $element;
}

/**
 * Loads the profile form values for a user registration.
 *
 * This function is responsible for loading the default values for the profile form
 * elements based on the submission data retrieved from a webform entity.
 *
 * @param array &$form_elements
 *   The array of form elements.
 * @param FormStateInterface &$form_state
 *   The form state object.
 * @param int|null $sid
 *   The submission ID.
 * @param User $user
 *   The user object.
 *
 * @throws \Exception
 *   If an error occurs during the process.
 */
function _abrsd_user_registration_load_profile_form_values(
    array &$form_elements,
    FormStateInterface &$form_state,
    ?int $sid,
    User $user
) {
    try {
        if (!empty($sid)) {
            // Load the submission from the webform entity
            $submission = WebformSubmission::load($sid);
            // Element data
            $submission_data = $submission->getData();
            // Define form elements - the format conteiner|element is used to identify the form elements
            // The words container, container_01, container_02 represent the elements used to group or
            // contain the field elements. The field names appear after the | character.
            $form_elements_keys = [
                'container|first_name', 'container|last_name', 'container|country',
                'container|signup_email', 'container|organization', 'container_01|interests',
                'container_01|about_me', 'container|display_name', 'container|mail',
                'container|province', 'container|state', 'container|postal_code',
                'container|zip',
            ];
            // Set default values for form elements
            foreach ($form_elements_keys as $key) {
                $default_value = ($key != 'container|mail') ? 'field_' : '';
                _set_default_value($form_elements, $submission_data, $user, $key, $default_value);
            }
            // Get the last updated time of the user object
            $mark_up = _get_custom_form_markup($user->getChangedTime(), t('Your profile was last updated') . ': ');
            // $mark_up .= '<span class="loader d-none mb-4"></span>';
            $form_elements['container']['last_updated'] = [
                '#type' => 'item',
                '#markup' => $mark_up,
                '#weight' => -2,
            ];
            // Check if the profile picture has been removed
            $user_pic_removed = _abrsd_user_registration_user_picture_removed($form_state);
            // If the profile picture is not empty add the profile picture display
            if (!empty($submission_data['profile_picture'])) {
                // Add the profile picture
                $form_elements['container']['container_02']['profile_picture']['#default_value'] = [$submission_data['profile_picture']];
                // Add the webform submission ID
                $file_url = _abrsd_user_registration_user_picture($user);
                // Add the profile picture display if $file_url is not null
                if (!empty($file_url)) {
                    $form_elements['container']['container_02']['profile_picture_display'] = [
                        '#type' => 'inline_template',
                        '#template' => '<img src="{{ url }}" class="{{ css_class }}" alt="Profile picture" />',
                        '#context' => [
                            'url' => $file_url,
                            'css_class' => $user_pic_removed ? 'hidden' : 'me-2',
                        ],
                        '#weight' => -1,
                        // '#access' => $user_pic_removed ? FALSE : TRUE,
                        '#prefix' => '<div id="profile-picture-display">',
                        '#suffix' => '</div>',
                    ];
                }
            }
        }
    } catch (\Exception $e) {
        // Log any errors
        \Drupal::logger('abrsd_user_registration')->error($e->getMessage());
    }
}

/**
 * Gets the last updated markup.
 *
 * This function is responsible for generating the last updated markup for the user profile form.
 *
 * @param int $time_stamp
 *   The timestamp to be displayed.
 * @param string $message
 *   The message to be displayed.
 *
 * @return string
 *   The HTML to be displayed.
 */
function _get_custom_form_markup(int $time_stamp, string $message): string
{
    $mark_up = '';
    try {
        $mark_up = '<p class="last-updated d-flex justify-content-between align-items-center px-3 py-2">';
        $mark_up .= '<span class="form-message">';
        $mark_up .= $message;
        $mark_up .= ':&nbsp;&nbsp;' . date('F j, Y \a\t g:i a', $time_stamp);
        $mark_up .= '</span>';
        $mark_up .= '<span class="loader d-none"></span>';
        $mark_up .= '</p>';
    } catch (\Exception $e) {
        // Log any errors
        \Drupal::logger('abrsd_user_registration')->error($e->getMessage());
    }

    return $mark_up;
}

/**
 * Get the user picture.
 *
 * @param int $uid
 *   The user ID.
 *
 * @return string|null
 *   The URL of the user picture if found, NULL otherwise.
 */
function _abrsd_user_registration_user_picture(User $user): ?string
{
    $result = NULL;
    try {
        // Get the file ID of the user picture
        $fid = $user->get('user_picture')->target_id;
        // Load the file entity
        if (!empty($fid)) {
            $file = File::load($fid);
            if ($file) {
                // Get the URL of the image with the desired style.
                $result = ImageStyle::load('thumbnail')->buildUrl($file->getFileUri());
            }
        }
    } catch (\Exception $e) {
        // Log any errors
        \Drupal::logger('abrsd_user_registration')->error($e->getMessage());
    }

    return $result;
}

/**
 * Checks if the user picture has been removed from the form.
 *
 * @param \Drupal\Core\Form\FormStateInterface $form_state
 *   The form state object.
 *
 * @return bool
 *   TRUE if the user picture has been removed, FALSE otherwise.
 */
function _abrsd_user_registration_user_picture_removed(FormStateInterface $form_state): bool
{
    $result = FALSE;
    try {
        // Check if the user picture has been removed
        $user_pic = $form_state->getValue('profile_picture');
        $result = is_array($user_pic) && !isset($user_pic['fid']);
    } catch (\Exception $e) {
        // Log any errors
        \Drupal::logger('abrsd_user_registration')->error($e->getMessage());
    }

    return $result;
}

/**
 * Generates a unique username.
 *
 * This function is called to generate a unique username based on the base username.
 *
 * @param string $base_username
 *   The base username.
 *
 * @return string
 *   The unique username.
 */
function _generate_unique_username($base_username) {
    // Ensure base username is between 4 and 20 characters, using substr and str_pad if necessary
    $base_username = substr($base_username, 0, 20);
    $base_username = strlen($base_username) < 5 ? str_pad($base_username, 5, "0") : $base_username;

    $username = $base_username;
    // If the username is an email address, remove the domain part and ensure it's within the length limits
    if (strpos($username, '@') !== FALSE) {
        $username = explode('@', $username)[0];
        $username = substr($username, 0, 20);
        $username = strlen($username) < 5 ? str_pad($username, 5, "01") : $username;
    }

    $i = 1;
    // Load the user storage
    $user_storage = \Drupal::entityTypeManager()->getStorage('user');
    // Loop until a unique username is found
    while ($user_storage->loadByProperties(['field_display_name' => $username])) {
        // If the username exists, append a number and check again, ensuring it doesn't exceed 20 characters
        $temp_username = $base_username . $i;
        $username = strlen($temp_username) > 20 ? substr($temp_username, 0, 20 - strlen((string)$i)) . $i : $temp_username;
        $i++;
    }

    return $username;
}