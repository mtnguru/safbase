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
 *   id = "submit_ticket_block",
 *   admin_label = @Translation("AZ Submit Ticket Block"),
 *   category = @Translation("AZ")
 * )
 */
class SubmitTicketBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $route = \Drupal::service('request_stack')->getCurrentRequest()->get('_route');
    $node = null;
    $entityType = 'unknown';
    $id = 0;
    switch ($route) {
      case 'entity.group.canonical':
        $activePage = 'recent-content';
        $group = $this->requestStack->getCurrentRequest()->get('group');
        if ($value = $group->field_directory_book->getValue()) {
          $bid = $value[0]['target_id'];
        }
      	$entityType = 'group';
        break;

      case 'entity.node.canonical':
        $node = \Drupal::service('request_stack')->getCurrentRequest()->get('node');
      	$entityType = 'contentType';
      	$id = $node->id();
        break;

      case 'entity.user.canonical':
        $entityType = 'user';
        break;
        
      default:
        \Drupal::messenger()->addMessage(t('az_content::SubmitTicketBlock unknown route ' . $route),'error');
      	break;
    }

    $build['submit_ticket_link'] = [
      '#type' => 'container',
      'link' => [
        '#type' => 'link',
        '#title' => t('Submit a Ticket'),
        '#attributes' => ['title' => t('Use tickets to submit suggestions, questions, complaints or bugs regarding this page or the website.')],
        '#url' => Url::fromRoute('node.add', ['node_type' => 'ticket'], [
          'absolute' => TRUE,
          'query' => [
//          'group' => $group->id(),
            'entityType' => $entityType,
            'id' => $id,
            'node' => $id
          ],
        ]),
      ],
    ];
    return $build;
  }
}

