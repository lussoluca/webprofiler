<?php

namespace Drupal\webprofiler\Compiler;

use Drupal\Core\StreamWrapper\PublicStream;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 * Class ProfilerPass
 *
 * @package Drupal\webprofiler\Compiler
 */
class ProfilerPass implements CompilerPassInterface {

  /**
   * @param ContainerBuilder $container
   *
   * @throws \InvalidArgumentException
   */
  public function process(ContainerBuilder $container) {
    // replace the class for form_builder service
    $form_builder = $container->getDefinition('form_builder');
    $form_builder->setClass('Drupal\webprofiler\Form\ProfilerFormBuilder');

    // configure the profiler service
    if (FALSE === $container->hasDefinition('profiler')) {
      return;
    }

    $definition = $container->getDefinition('profiler');

    $collectors = new \SplPriorityQueue();
    $order = PHP_INT_MAX;
    foreach ($container->findTaggedServiceIds('data_collector') as $id => $attributes) {
      $priority = isset($attributes[0]['priority']) ? $attributes[0]['priority'] : 0;
      $template = NULL;

      if (isset($attributes[0]['template'])) {
        if (!isset($attributes[0]['id'])) {
          throw new \InvalidArgumentException(sprintf('Data collector service "%s" must have an id attribute in order to specify a template', $id));
        }
        $template = array($attributes[0]['id'], $attributes[0]['template']);
      }

      $collectors->insert(array($id, $template), array($priority, --$order));
    }

    $templates = array();
    foreach ($collectors as $collector) {
      $definition->addMethodCall('add', array(new Reference($collector[0])));
      $templates[$collector[0]] = $collector[1];
    }

    $container->setParameter('data_collector.templates', $templates);

    // set parameter to store the public folder path
    $path = 'file://' . DRUPAL_ROOT . '/' . PublicStream::basePath() . '/profiler';
    $container->setParameter('data_collector.storage', $path);
  }
}
