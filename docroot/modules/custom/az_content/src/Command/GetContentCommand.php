<?php

/**
 * Command to load content with AJAX.
 */

namespace Drupal\az_content\Command;
use Drupal\Core\Ajax\CommandInterface;

class GetContentCommand implements CommandInterface {
  public function __construct($set, $content) {
    $this->set = $set;
    $this->content = $content;
  }

  public function render() {
    return array(
      'command' => 'GetContentCommand',
      'set' => $this->set,
      'content' => $this->content,
    );
  }
}
