<?php

/**
 * @file
 * Contains \Drupal\language\AuthRequestServiceProvider.
 */

namespace Drupal\auth_request;

use \Drupal\Core\DependencyInjection\ContainerBuilder;
use \Drupal\Core\DependencyInjection\ServiceProviderBase;
use \Symfony\Component\DependencyInjection\Reference;

/**
 * Overrides the language_manager service to point to language's module one.
 */
class AuthRequestServiceProvider extends ServiceProviderBase {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    $definition = $container->getDefinition('user.auth');
    // Swap out authentication implementation
    $definition->setClass('Drupal\auth_request\AuthRequestAuth')
      ->addArgument(new Reference('http_client'));
  }

}
