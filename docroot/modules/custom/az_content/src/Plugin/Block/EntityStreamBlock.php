<?php

namespace Drupal\az_content\Plugin\Block;

use Drupal\az_content\AzContentQuery;
use Drupal\az_content\AzContentInit;
use Drupal\az_content\AzStream;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Database\Connection;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 *
 * @Block(
 *   id = "entity_stream_block",
 *   admin_label = @Translation("AZ Entity Stream Block"),
 *   category = @Translation("AZ")
 * )
 */
class EntityStreamBlock extends BlockBase {

  public function blockForm($form, FormStateInterface $form_state) {

    // Give the viewer a name so the controls block can connect to it.
    $form['set'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Query settings'),
      '#description' => $this->t('Query settings in yml format.'),
      '#default_value' => isset($this->configuration['set']) ? $this->configuration['set'] : 'default value',
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['set'] = $form_state->getValue('set');
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $set = $this->configuration['set'];

    // Initialize settings that haven't been set.
//  $set['pageNum'] = (isset($set['pageNum'])) ? $set['pageNum'] : 0;
//  $set['pageNumItems'] = (isset($set['pageNumItems'])) ? $set['pageNumItems'] : 10;
//  $set['entityType'] = (isset($set['entityType'])) ? $set['entityType'] : 'node';
//  $set['viewMode'] = (isset($set['viewMode'])) ? $set['viewMode'] : 'teaser';
//  $set['more'] = (isset($set['more'])) ? $set['more'] : 'none';

    // If using a pager set the pager id
    if ($set['more'] == 'pager' && !isset($set['pagerId'])) {
      $pagerId = &drupal_static('azPagerId', 0);
      $set['pagerId'] = $pagerId++;
    }

    // Set up the container classes
    $classes = [
      'content-stream',
      'entity-' . $set['entityType'],
      str_replace('_', '-', $set['viewMode'])
    ];
    if (isset($set['class'])) {
      $classes[] = $set['class'];
    }
    if (isset($set['label'])) {
      $classes[] = 'tab-content';
    } else {
      $classes[] = 'block-content';
    }

    // Create the stream container
    $stream = [
      '#type' => 'container',
      '#attributes' => [
        'id' => $set['id'],
        'class' => $classes,
      ],
    ];

    // Add the title if specified
    if (isset($set['title'])) {
      $stream['title'] = [
        '#type' => 'container',
        '#attributes' => ['class' => ['title-container']],
        'markup' => ['#markup' => '<h2>' . $set['title'] . '</h2>'],
      ];
    }

    // Query for total number matches
    $set['count'] = true;
    switch ($set['entityType']) {
      case 'node':
        $set['totalRows'] = AzContentQuery::nodeQuery($set);
        break;
      case 'comment':
        $set['totalRows'] = AzContentQuery::commentQuery($set);
        break;
      case 'user':
        $set['totalRows'] = AzContentQuery::userQuery($set);
        break;
      case 'media':
        $set['totalRows'] = AzContentQuery::mediaQuery($set);
        break;
    }
    $set['count'] = false;

    if ($set['totalRows'] == 0  && $set['empty'] == 'NO DISPLAY') {
      return null;
    }

    // Build empty container for the stream content - AJAX fills it.
    $stream['content'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['content-container']],
//    'stream' => AzStream::create($set),      // Load the first page.
    ];

    if (isset($set['load']) && $set['load'] == 'immediate') {
      $stream['content']['stream'] = AzStream::create($set);      // Load the first page.
      $set['loaded'] = true;
    };

    AzContentInit::start($set, $stream);
    return $stream;
  }
}

