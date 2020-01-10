<?php

namespace Drupal\az_content\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\node\Entity\Node;

/**
 * Provides a contribute block for the current node.
 *
 * @Block(
 *   id = "az_contribute_block",
 *   admin_label = @Translation("AZ Contribute Block"),
 *   category = @Translation("AZ Content")
 * )
 */
class AzContributeBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $node = \Drupal::routeMatch()->getParameter('node');
    if (!is_object($node)) {
      $node = Node::load($node);
    }

    if ($node) {
      return [
        '#theme' => 'block_contribute',
        '#attributes' => ['class' => ['block-border', 'contribute-block']],
        '#description' => $node->field_contribute->value,
        '#more_url' => \Drupal::service('path.alias_manager')->getAliasByPath('/node/'.$node->id()),
      ];
    }
  }
}

