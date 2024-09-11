<?php

namespace Drupal\sabc_institution\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;

/**
 * Class InstitutionForm provides a school search function.
 */
class InstitutionForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'institution_form';
  }

  /**
   * Provides a school lookup form.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['message'] = [
      '#type' => 'markup',
      '#markup' => '<div class="result_message"></div>'
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    foreach ($form_state->getValues() as $key => $value) {
      // @TODO: Validate fields.
    }
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Display result.
    foreach ($form_state->getValues() as $key => $value) {
      \Drupal::messenger()->addMessage($key . ': ' . ($key === 'text_format' ? $value['value'] : $value));
    }
  }

  /**
   *
   */
  public function setMessage(array $form, FormStateInterface $form_state) {

    $response = new AjaxResponse();
    $response->addCommand(
      new HtmlCommand(
        '.result_message',
        '<div class="result_message">' . t('The results is @result', ['@result' => ($form_state->getValue('search_for_an_institution'))]) . '</div>'),
    );
    return $response;

  }

}
