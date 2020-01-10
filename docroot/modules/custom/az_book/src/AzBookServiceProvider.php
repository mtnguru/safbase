<?php

namespace Drupal\az_book;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/** 
 * Service Provider for Entity Reference Revisions.
 */ 
class AzBookServiceProvider extends ServiceProviderBase {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    $definition = $container->getDefinition('book.manager');
    $definition->setClass('Drupal\az_book\AzBookManager');
  }
}   
