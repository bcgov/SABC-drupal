<?php

namespace Drupal\sabc_forms\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Markup;

class SabcPrivateDataForm extends ConfigFormBase
{


  /**
   * Returns a unique string identifying the form.
   *
   * The returned ID should be a unique string that can be a valid PHP function
   * name, since it's used in hook implementation names such as
   * hook_form_FORM_ID_alter().
   *
   * @return string
   *   The unique string identifying the form.
   */
  public function getFormId()
  {
    return 'sabc_private_data_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'sabc_private_data_form.settings'
      ];
  }

  /**
   * Form constructor.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   The form structure.
   */
  public function buildForm(array $form, FormStateInterface $form_state)
  {
    $config = $this->config('sabc_private_data_form.settings');

    $form['path_aeit_library'] = [
      '#type' => 'textfield',
      '#title' => $this->t('The path to the AEIT library'),
      '#description' => $this->t('The AEIT library contains classes and other services to make calls to various web services.'),
      '#required' => TRUE,
      '#default_value' => $config->get('path_aeit_library') ?: '',
    ];

    $form['path_bccgg_file'] = [
      '#type' => 'textfield',
      '#title' => $this->t('The path to the FINAL_BCCGG_ALL_INSTITUTIONS_CSV file'),
      '#required' => TRUE,
      '#default_value' => $config->get('path_bccgg_file') ?: '',
    ];

    $form['captcha_sitekey_1'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Captcha key 1'),
      '#description' => $this->t('Key used in Institution Designation Application form.'),
      '#required' => TRUE,
      '#default_value' => $config->get('captcha_sitekey_1') ?: '',
    ];

    $form['captcha_sitekey_2'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Captcha key 2'),
      '#description' => $this->t('Key used in Institution Designation Application form.'),
      '#required' => TRUE,
      '#default_value' => $config->get('captcha_sitekey_2') ?: '',
    ];

    $form['mosswb'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('MOSS Web Service'),
    ];

    $form['mosswb']['mosswb_login'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Login'),
      '#required' => TRUE,
      '#default_value' => $config->get('mosswb.login') ?: '',
    ];

    $form['mosswb']['mosswb_password'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Password'),
      '#required' => TRUE,
      '#default_value' => $config->get('mosswb.password') ?: '',
    ];

    $form['pctia'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('PCTIA Web Service'),
    ];

    $form['pctia']['pctia_login'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Login'),
      '#required' => TRUE,
      '#default_value' => $config->get('pctia.login') ?: '',
    ];

    $form['pctia']['pctia_password'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Password'),
      '#required' => TRUE,
      '#default_value' => $config->get('pctia.password') ?: '',
    ];

    $form['emails'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Emails used in the website'),
    ];

    $form['emails']['aved_webservices'] = [
      '#type' => 'textfield',
      '#title' => $this->t('AVED Web Services'),
      '#required' => TRUE,
      '#default_value' => $config->get('aved_webservices') ?: '',
    ];

    $form['emails']['aved_systemuser'] = [
      '#type' => 'textfield',
      '#title' => $this->t('AVED System User'),
      '#required' => TRUE,
      '#default_value' => $config->get('aved_systemuser') ?: '',
    ];

    $form['emails']['request_it'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Request IT'),
      '#required' => TRUE,
      '#default_value' => $config->get('request_it') ?: '',
    ];

    $form['emails']['prod_email'] = [
      '#type' => 'textfield',
      '#title' => $this->t('PROD'),
      '#required' => TRUE,
      '#default_value' => $config->get('prod_email') ?: '',
    ];

    $form['emails']['uat_email'] = [
      '#type' => 'textfield',
      '#title' => $this->t('UAT'),
      '#required' => TRUE,
      '#default_value' => $config->get('uat_email') ?: '',
    ];

    $form['emails']['dev_email'] = [
      '#type' => 'textfield',
      '#title' => $this->t('DEV'),
      '#required' => TRUE,
      '#default_value' => $config->get('dev_email') ?: '',
    ];

    return parent::buildForm($form, $form_state);
  }


  /**
   * Form submission handler.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function submitForm(array &$form, FormStateInterface $form_state)
  {
    $values = $form_state->getValues();
    $this->config('sabc_private_data_form.settings')
      ->set('path_aeit_library', $values['path_aeit_library'])
      ->set('path_bccgg_file', $values['path_bccgg_file'])
      ->set('captcha_sitekey_1', $values['captcha_sitekey_1'])
      ->set('captcha_sitekey_2', $values['captcha_sitekey_2'])
      ->set('mosswb.login', $values['mosswb_login'])
      ->set('mosswb.password', $values['mosswb_password'])
      ->set('pctia.login', $values['pctia_login'])
      ->set('pctia.password', $values['pctia_password'])
      ->set('aved_webservices', $values['aved_webservices'])
      ->set('aved_systemuser', $values['aved_systemuser'])
      ->set('request_it', $values['request_it'])
      ->set('prod_email', $values['prod_email'])
      ->set('uat_email', $values['uat_email'])
      ->set('dev_email', $values['dev_email'])
      ->save();

    parent::submitForm($form, $form_state);

    \Drupal::messenger()->addMessage(t("Form saved successfully."));
  }

}
