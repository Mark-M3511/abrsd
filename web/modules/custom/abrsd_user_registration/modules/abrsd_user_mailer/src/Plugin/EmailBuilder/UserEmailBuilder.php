<?php

namespace Drupal\abrsd_user_mailer\Plugin\EmailBuilder;

use Drupal\symfony_mailer\EmailFactoryInterface;
use Drupal\symfony_mailer\EmailInterface;
use Drupal\symfony_mailer\Entity\MailerPolicy;
use Drupal\symfony_mailer\MailerHelperTrait;
use Drupal\symfony_mailer\Processor\EmailBuilderBase;
use Drupal\symfony_mailer\Processor\TokenProcessorTrait;
use Drupal\user\UserInterface;

/**
 * Defines the Email Builder plug-in for user module.
 *
 * @EmailBuilder(
 *   id = "user",
 *   sub_types = {},
 *   common_adjusters = {},
 *   import = @Translation(),
 * )
 *
 * @todo Notes for adopting Symfony Mailer into Drupal core. This builder can
 * set langcode, to, reply-to so the calling code doesn't need to.
 */
class UserEmailBuilder extends EmailBuilderBase
{

  use TokenProcessorTrait;

  /**
   * Saves the parameters for a newly created email.
   *
   * @param \Drupal\symfony_mailer\EmailInterface $email
   *   The email to modify.
   * @param \Drupal\user\UserInterface $user
   *   The user.
   */
  public function createParams(EmailInterface $email, UserInterface $user = NULL)
  {
    assert($user != NULL);
    $email->setParam('user', $user);
  }

  /**
   * {@inheritdoc}
   */
  public function build(EmailInterface $email)
  {
    // Taken from user_mail callback in  user.module
    $token_service = \Drupal::token();
    $language_manager = \Drupal::languageManager();

    $language = $language_manager->getLanguage($langcode);
    $original_language = $language_manager->getConfigOverrideLanguage();
    $language_manager->setConfigOverrideLanguage($language);
    $mail_config = \Drupal::config('user.mail');

    $language_manager->setConfigOverrideLanguage($original_language);
  }

  /**
   * {@inheritdoc}
   */
  public function import()
  {
  }
}
