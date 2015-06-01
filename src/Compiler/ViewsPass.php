<?php

namespace Drupal\webprofiler\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class ViewsPass implements CompilerPassInterface {

  /**
   * {@inheritdoc}
   */
  public function process(ContainerBuilder $container) {
    $container->setDefinition('views.executable.default', $container->getDefinition('views.executable'));
    $container->register('views.executable', 'Drupal\webprofiler\Views\ViewExecutableFactoryWrapper')
      ->addArgument(new Reference('current_user'))
      ->addArgument(new Reference('request_stack'))
      ->addArgument(new Reference('views.views_data'))
      ->addArgument(new Reference('router.route_provider'));
  }
}
