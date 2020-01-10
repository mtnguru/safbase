<?php

namespace Drupal\az_content\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a 'Book navigation' block.
 *
 * @Block(
 *   id = "az_donate_block",
 *   admin_label = @Translation("AZ Donate Block"),
 *   category = @Translation("AZ Content")
 * )
 */
class AzDonateBlock extends BlockBase {

  public function blockForm($form, FormStateInterface $form_state) {
    $node = (isset($this->configuration['nid']))
      ? \Drupal::entityTypeManager()->getStorage('node')->load($this->configuration['nid'])
      : NULL;
    $form['nid'] = [
      '#type' => 'entity_autocomplete',
      '#target_type' => 'node',
      '#selection_handler' => 'default',
      '#default_value' => $node,
      '#selection_settings' => [
        'target_bundles' => ['snippet'],
      ],
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['nid'] = $form_state->getValue('nid');
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $config = $this->getConfiguration();
    if ($config['nid']) {
      $node = \Drupal::entityTypeManager()->getStorage('node')->load($config['nid']);

      if ($node) {
        return [
          '#theme' => 'block_donate',
          '#attributes' => ['class' => ['donate-block', 'block-border']],
          '#description' => $node->body->value,
          '#more_url' => \Drupal::service('path.alias_manager')->getAliasByPath('/node/'.$node->id()),
        ];
      }
    }
    return [
      '#type' => 'container',
      '#attributes' => ['class' => ['donate-block', 'block-border']],
      'description' => ['#markup' => 'No node selected to display'],
    ];
  }
}

