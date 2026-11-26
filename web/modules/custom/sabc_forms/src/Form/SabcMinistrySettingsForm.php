<?php

namespace Drupal\sabc_forms\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure SABC Ministry settings.
 */
class SabcMinistrySettingsForm extends ConfigFormBase {

  /**
   * Config settings name.
   */
  public const SETTINGS = 'sabc_ministry_settings.settings';

  /**
   * Default Ministry name fallback when no value has been saved yet.
   */
  public const DEFAULT_MINISTRY_NAME = 'Ministry of Post-Secondary Education and Future Skills';

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'sabc_ministry_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      static::SETTINGS,
    ];
  }

  /**
   * Returns the configured Ministry name, or the default fallback.
   *
   * @return string
   *   The Ministry name.
   */
  public static function getMinistryName() {
    $ministry_name = \Drupal::config(static::SETTINGS)->get('ministry_name');
    if (is_string($ministry_name) && trim($ministry_name) !== '') {
      return trim($ministry_name);
    }
    return static::DEFAULT_MINISTRY_NAME;
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['ministry_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Ministry Name'),
      '#description' => $this->t('The Ministry name displayed in SABC emails and status descriptions.'),
      '#required' => TRUE,
      '#maxlength' => 255,
      '#default_value' => static::getMinistryName(),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);

    if (trim((string) $form_state->getValue('ministry_name')) === '') {
      $form_state->setErrorByName('ministry_name', $this->t('Ministry Name cannot be empty.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config(static::SETTINGS)
      ->set('ministry_name', trim((string) $form_state->getValue('ministry_name')))
      ->save();

    parent::submitForm($form, $form_state);
  }

}