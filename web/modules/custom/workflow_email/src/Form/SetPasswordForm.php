<?php

namespace Drupal\workflow_email\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\user\Entity\User;

/**
 * Form for setting a new password after OTP verification.
 */
class SetPasswordForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'workflow_email_set_password_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $session = \Drupal::request()->getSession();
    $email = $session->get('workflow_email_user_email');

    if (!$email) {
      \Drupal::messenger()->addError($this->t('Session expired. Please register again.'));
      $form_state->setRedirect('user.register');
      return [];
    }

    $form['new_pass'] = [
      '#type' => 'password_confirm',
      '#title' => $this->t('New Password'),
      '#required' => TRUE,
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Set Password'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $session = \Drupal::request()->getSession();
    $email = $session->get('workflow_email_user_email');
  
    if (!$email) {
      \Drupal::messenger()->addError($this->t('Session expired. Please register again.'));
      $form_state->setRedirect('user.register');
      return;
    }
  
    $password = $form_state->getValue('new_pass');
    if ($password) {
      // ✅ Create user here (only after setting password)
      $user = User::create([
        'name' => explode('@', $email)[0],
        'mail' => $email,
        'status' => 1,
      ]);
      $user->setPassword($password);
      $user->save();
  
      // Clear session
      $session->remove('workflow_email_user_email');
  
      // Auto login user
      user_login_finalize($user);
  
      \Drupal::messenger()->addStatus($this->t('Your password has been set. Welcome!'));
      $form_state->setRedirect('<front>');
    } else {
      \Drupal::messenger()->addError($this->t('Failed to set password. Please try again.'));
    }
  }
  
}
