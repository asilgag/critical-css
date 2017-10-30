<?php

namespace Drupal\critical_css;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;

/**
 * Modifies the \Drupal\Core\Asset\CssCollectionRenderer service.
 */
class CriticalCssServiceProvider extends ServiceProviderBase {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    // Overrides asset.css.collection_renderer to preload CSS links.
    $definition = $container->getDefinition('asset.css.collection_renderer');
    $definition->setClass('Drupal\critical_css\Asset\CriticalCssCollectionRenderer');
  }

}
