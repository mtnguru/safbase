<?php

namespace Drupal\az_content\Controller;
use Drupal\az_content\AzStream;
use Drupal\az_content\Command\GetContentCommand;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Controller\ControllerBase;

/**
 * Class ContentController.
 *
 * @package Drupal\az_content\Controller
 */
class ContentController extends ControllerBase {

  /**
   * Create a stream of content.
   */
  public function getContent() {
    $set = json_decode(file_get_contents("php://input"), true);

    switch ($set['type']) {
      case 'entity-table':
      case 'entity-stream':
        $build = AzStream::create($set);
        break;

      case 'entity-render':
        $entity = \Drupal::entityTypeManager()->getStorage($set['entityType'])->load($set['eid']);
        $build = \Drupal::entityTypeManager()->getViewBuilder($set['entityType'])->view($entity, $set['viewMode']);
        break;
    }

    $response = new AjaxResponse();
    $response->addCommand(new GetContentCommand($set, render($build)));
    return $response;
  }
}
