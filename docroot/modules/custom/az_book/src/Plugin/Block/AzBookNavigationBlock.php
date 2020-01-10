<?php

namespace Drupal\az_book\Plugin\Block;

use Drupal\az_groups\AzGroupQuery;
use Drupal\book\Plugin\Block\BookNavigationBlock;
use Drupal\Core\Database\Connection;
use Drupal\Core\Url;
use Drupal\node\Entity\Node;

/**
 * Provides a 'Book navigation' block.
 *
 * @Block(
 *   id = "az_book_navigation",
 *   admin_label = @Translation("AZ Book navigation"),
 *   category = @Translation("AZ Menu")
 * )
 */
class AzBookNavigationBlock extends BookNavigationBlock {

  /**
   * Compare two pages for usort().  Sort by weight, then title
   *
   * @param $a
   * @param $b
   * @return bool
   */
  private static function comparePages($a, $b) {
    if ($a->weight == $b->weight) {
      return $a->title <=> $b->title;
    }
    return ($a->weight <=> $b->weight);
  }

  /**
   * Recursively go through the book menu and build the render array for each item.
   * @param $results
   * @param $nid
   * @param $level
   * @return array
   */
  private function buildMenuRecursive($results, $nid, $level) {
    $items = [];
    if (empty($results[$nid]->children)) return $items;

    usort($results[$nid]->children, 'self::comparePages');
    foreach ($results[$nid]->children as $num => $child) {
      $classes = ['menu-item'];
      if (!empty($child->activeTrail)) {
        $classes[] = 'menu-item--active';
      }
      if ($child->moderation_state) {
        $state = $child->moderation_state;
      } else {
        if ($child->status) {
          $state = 'published';
        } else {
          $state = 'draft';
        }
      }
      switch ($state) {
        case 'placeholder':
          $classes[] = 'menu-item--placeholder';
          $classes[] = 'menu-item--unpublished';
          break;
        case 'draft':
          $classes[] = 'menu-item--draft';
          $classes[] = 'menu-item--unpublished';
          break;
        case 'needs_review':
          $classes[] = 'menu-item--needs-review';
          $classes[] = 'menu-item--unpublished';
          break;
        case 'published':
          $classes[] = 'menu-item--published';
          break;
        case 'confidential':
          $classes[] = 'menu-item--confidential';
          $classes[] = 'menu-item--unpublished';
          break;
        case 'archived':
          $classes[] = 'menu-item--archived';
          $classes[] = 'menu-item--unpublished';
          break;
      }
      if (empty($child->children)) {
        $build = [
          'title' => $child->title,
          'link' => $child->link,
        ];
      }
      else {
        if (!empty($child->activeTrail)) {
          $classes[] = 'menu-item--expanded';
        }
        $classes[] = 'menu-item--children';
        $build = [
          'title' => $child->title,
          'link' => $child->link,
          'children' => $this->buildMenuRecursive($results, $child->nid, $level + 1),
        ];
      }
      $build['class'] = join(' ', $classes);
      $items[] = $build;
    }
    return $items;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    // If this is a node page then find which book it is associated with.
    $route = $this->requestStack->getCurrentRequest()->get('_route');
    $activePage = 'book';
    $bid = null;

    // Based on the route, get the $bid and $gid if available
    $is_book_page = false;
    switch ($route) {
      case 'entity.group.canonical':
        $activePage = 'recent-content';
        $group = $this->requestStack->getCurrentRequest()->get('group');
        if ($value = $group->field_directory_book->getValue()) {
          $bid = $value[0]['target_id'];
        }
        break;

      case 'entity.node.canonical':
        $node = $this->requestStack->getCurrentRequest()->get('node');
        $bid = $node->book['bid'];
        if ($gid = AzGroupQuery::nodeInGroup($node)) {
          $group = \Drupal::entityTypeManager()->getStorage('group')->load($gid);
          if (!$bid && $value = $group->field_directory_book->getValue()) {
            $bid = $value[0]['target_id'];
          }
          if ($bid) {
            $is_book_page = true;
          }
        }
        break;
    }

    // If this is a book page query for all book pages and create book menu
    if (!empty($bid)) {

      // Query for all pages in this book
      $query = \Drupal::database()->select('book');
      $query->fields('book');
      for ($i = 1; $i <= 9; $i++) {
        $query->orderBy('p' . $i, 'ASC');
      }
      $query->condition('bid', $bid);

      $query->join('node_field_data', 'nfd', 'nfd.nid = book.nid');
      $query->addField('nfd', 'title');
      $query->addField('nfd', 'status');

      $query->leftJoin('content_moderation_state_field_data', 'cmsfd', 'cmsfd.content_entity_id = book.nid');
      $query->addField('cmsfd', 'moderation_state');

      $results = $query->execute()->fetchAllAssoc('nid');
      if (count($results) < 1) return [];

      if (!empty($node) && $node->getType() == 'book') {

        $result = &$results[$node->id()];
        if ($result) {
          for ($i = 1; $i <= $result->depth; $i++) {
            $n = 'p' . $i;
            if (!empty($results[$result->$n])) {
              $results[$result->$n]->activeTrail = TRUE;
            }
          }
        }
      }

      $showUnpublished = \Drupal::currentUser()->hasPermission('show unpublished book pages');
      $num_published = 0;
      $num_total = 0;
      // Append children to their parent.
      foreach ($results as &$result) {
        if (empty($result)) continue;
        $num_total += 1;
        if ($result->status == 1) {
          $num_published += 1;
        } else if ($result->status == 0 && !$showUnpublished) {
          continue;
        }

        $pid = $result->pid;
        $nid = $result->nid;

        // Create link to book page.
        $options = ['absolute' => TRUE, 'attributes' => ['class' => 'this-class']];
        _az_content_add_SAM_tm($result->title);
        $node_title = \Drupal\Core\Render\Markup::create('<span>' . $result->title . '</span>');
        $result->link = \Drupal\Core\Link::createFromRoute($node_title, 'entity.node.canonical', ['node' => $nid], $options);

        // Add page to parent page.
        if ($pid && !empty($results[$pid])) {
          $results[$pid]->children[$nid] = $results[$nid];
        }
      }

      $build = [
        '#theme' => 'az_book_navigation',
        '#book_title' => $results[$bid]->title,
        '#book_url' => \Drupal::service('path.alias_manager')->getAliasByPath('/node/'.$bid),
        '#book_id' => $bid,
        '#book_pages' => $this->buildMenuRecursive($results, $bid, 1),
        '#attributes' => ['class' => ['item-top']],
        '#hide_unpublished' => (\Drupal::currentUser()->hasPermission('show unpublished book pages')),
      ];

      // If this is a group page.
      if (!empty($group)) {
        $build['#group_url'] = \Drupal::service('path.alias_manager')->getAliasByPath('/group/' . $group->id());
        $build['#group_name'] = $group->label->value;
        $build['#group_id'] = $group->id();
        $logo = $group->field_logo_image->getValue();
        if (count($logo)) {
          $file = \Drupal\file\Entity\File::load($logo[0]['target_id']);
          $build['#group_logo_url'] = \Drupal\image\Entity\ImageStyle::load('900x300')->buildUrl($file->getFileUri());
        }
        // Add the View Recent Content and Submit Ticket links
//      $build['#group_links'] = [
//        'view_content' => [
//          '#type' => 'container',
//          'link' => [
//            '#type' => 'link',
//            '#title' => t('View Recent Content'),
//            '#attributes' => ['title' => t('View recent content for this community.')],
//            '#url' => Url::fromRoute('entity.group.canonical', ['group' => $group->id()], ['absolute' => TRUE]),
//          ],
//        ],
//      ];

        // If this is the Structured Atom model add in the atomizer links
        if ($group->label() == 'Structured Atom Model') {

          // Add link to the Atom Viewer
          $build['#group_links']['atom_viewer'] = [
            '#type' => 'container',
            '#attributes' => ['class' => ['atom-viewer-link']],
            'link' => [
              '#type' => 'link',
              '#title' => t('Explore the Elements in 3D!'),
              '#attributes' => [
                'title' => t('Interactive 3D program to view the elements.'),
                'class' => ['atom-viewer'],
              ],
              '#url' => Url::fromUri('base:atomizer/atom-viewer', [
                'absolute' => TRUE,
              ]),
            ],
          ];

          // Add header to tools links - should I remove this?
          $build['#group_links']['title'] = [
            '#markup' => '<h4>' .  t('Tools') . '</h4>',
          ];

          // Add link to the Atom Builder
          if (\Drupal::currentUser()->hasPermission('atomizer display atom builder')) {
            $build['#group_links']['atom_builder'] = [
              '#type' => 'container',
              '#attributes' => ['class' => ['atom-builder-link']],
              'link' => [
                '#type' => 'link',
                '#title' => t('Atom Builder'),
                '#attributes' => [
                  'title' => t('Interactive program to build atoms according to SAM.'),
                ],
                '#url' => Url::fromUri('base:atomizer/atom-builder', [
                  'absolute' => TRUE,
                ]),
              ],
            ];
          }

          // Add link to the Dynamic Periodic Table
          $build['#group_links']['nuclides'] = [
            '#type' => 'container',
            'link' => [
              '#type' => 'link',
              '#title' => t('View Live Chart of Nuclides'),
              '#attributes' => [
                'title' => t('Dynamic chart that allows users to explore the Chart of Nuclides.'),
                'target' => '_blank',
              ],
              '#url' => Url::fromUri('https://www-nds.iaea.org/relnsd/vcharthtml/VChartHTML.html'),
            ],
          ];

          // Add link to List of Oxidation states of the elements
          $build['#group_links']['oxidation_states'] = [
            '#type' => 'container',
            'link' => [
              '#type' => 'link',
              '#title' => t('View Oxidation States'),
              '#attributes' => [
                'title' => t('List of Oxidation states of the elements'),
                'target' => '_blank',
              ],
              '#url' => Url::fromUri('https://en.wikipedia.org/wiki/List_of_oxidation_states_of_the_elements'),
            ],
          ];

          // Add link to the Dynamic Periodic Table
          $build['#group_links']['periodic_table'] = [
            '#type' => 'container',
            'link' => [
              '#type' => 'link',
              '#title' => t('View Dynamic Periodic Table'),
              '#attributes' => [
                'title' => t('Dynamic periodic table that let\'s you explore properties of the elements.'),
                'target' => '_blank',
              ],
              '#url' => Url::fromUri('https://ptable.com/'),
            ],
          ];
        }
        $build['#misc_links']['submit_ticket'] = [
          '#type' => 'container',
          'link' => [
            '#type' => 'link',
            '#title' => t('Submit a Ticket'),
            '#attributes' => ['title' => t('Use tickets to submit suggestions, questions, complaints or bugs regarding this page or the website.')],
            '#url' => Url::fromRoute('node.add', ['node_type' => 'ticket'], [
              'absolute' => TRUE,
              'query' => [
                'group' => $group->id(),
                'node' => (isset($node)) ? $node->id() : 0
              ],
            ]),
          ],
        ];

      }

      // If this is a book page then mark the book title as active
      if ($is_book_page) {
        $build['#book_title_classes'] = 'menu-item--active';
      }

      // Show SAM status block - shown in sidebar.
      if (isset($group) && $group->label() == 'Structured Atom Model') {
        $node = Node::load(425);
        $body = $node->body->value;
        _az_content_add_SAM_tm($body);
        $body = preg_replace('/([^ ])\&nbsp;([^ ])/', '$1 $2', $body);
        $body = preg_replace('/\$num_published/', $num_published, $body);
        $body = preg_replace('/\$num_total/', $num_total, $body);
        $build['#status'] = $body;
      }

      return $build;
    }
    return [];
  }
}

