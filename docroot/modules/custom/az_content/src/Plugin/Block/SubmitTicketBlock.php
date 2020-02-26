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
        $entityType = 'group';
        $activePage = 'recent-content';
        $group = \Drupal::service('request_stack')->getCurrentRequest()->get('group');
        if ($value = $group->field_directory_book->getValue()) {
          $bid = $value[0]['target_id'];
        }
        break;

      case 'entity.node.canonical':
        $entityType = 'contentType';
        $node = \Drupal::service('request_stack')->getCurrentRequest()->get('node');
      	$id = $node->id();
        break;

      case 'entity.user.canonical':
        $entityType = 'user';
        $user = \Drupal::service('request_stack')->getCurrentRequest()->get('user');
        $id = $user->id();
        break;

      case 'layout_builder.overrides.node.review':
      case 'layout_builder.overrides.node.view':
      case 'layout_builder.defaults.node.view':
      case 'node.add':
      case 'entity.node.edit_form':
      case 'view.tickets.page_1':
      case 'system.403':
      case 'system.404':
        return null;

      default:
        \Drupal::logger('az_control')->notice(t('az_content::SubmitTicketBlock unknown route ' . $route));
        return null;
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
          ],
        ]),
      ],
    ];

    /*
    $build['add_media_link'] = [
      '#type' => 'container',
      'link' => [
        '#type' => 'link',
        '#title' => t('Add Media'),
        '#attributes' => ['title' => t('Add media to this page')],
        '#url' => Url::fromRoute('entity.media_type.add_form', null, [
          'absolute' => TRUE,
          'query' => [
//          'group' => $group->id(),
            'entityType' => $entityType,
            'id' => $id,
          ],
        ]),
      ],
    ];
    */
    return $build;
  }
}

