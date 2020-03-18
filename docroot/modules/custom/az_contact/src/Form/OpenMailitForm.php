<?php

namespace Drupal\az_contact\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * OpenMailitForm class.
 */
class OpenMailitForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $options = NULL) {
    $form['checkbox'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Open Modal'),
    ];

    $form['open_mailit_form'] = [
      '#type' => 'link',
      '#title' => $this->t('Open Modal'),
      '#url' => Url::fromRoute('az_contact.open_mailit_form'),
      '#attributes' => [
        'class' => [
          'use-ajax',
          'button',
        ],
      ],
    ];

    // Attach the library for pop-up dialogs/modals.
    $form['#attached']['library'][] = 'core/drupal.dialog.ajax';

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'open_mailit_form';
  }

  /**
   * Gets the configuration names that will be editable.
   * @return array
   *   An array of configuration object names that are editable if called in
   *   conjunction with the trait's config() method.
   */
  protected function getEditableConfigNames() {
    return ['config.open_mailit_form'];
  }

}
