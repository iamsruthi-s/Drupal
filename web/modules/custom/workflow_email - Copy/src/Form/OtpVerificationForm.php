<?php

namespace Drupal\workflow_email\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Form to verify OTP.
 */
class OtpVerificationForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'workflow_email_otp_verification_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['otp'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Enter OTP'),
      '#required' => TRUE,
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Verify OTP'),
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
      \Drupal::messenger()->addError($this->t('Session expired. Please try registering again.'));
      $form_state->setRedirect('user.register');
      return;
    }

    $entered_otp = trim($form_state->getValue('otp'));
    $stored_otp = \Drupal::state()->get('workflow_email_otp_' . $email);

    if ($stored_otp && $entered_otp === (string) $stored_otp) {
      // ✅ OTP is correct, proceed to password creation
      \Drupal::state()->delete('workflow_email_otp_' . $email);

      // Redirect to password setup page
      $response = new RedirectResponse(Url::fromUri('internal:/set-password')->toString());
      $response->send();
      exit();
      
    } else {
      \Drupal::messenger()->addError($this->t('Invalid OTP. Please try again.'));
    }
  }
}
