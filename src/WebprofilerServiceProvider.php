<?php

/**
 * @file
 * Contains \Drupal\webprofiler\WebprofilerServiceProvider.
 */

namespace Drupal\webprofiler;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;
use Drupal\webprofiler\Compiler\EventPass;
use Drupal\webprofiler\Compiler\ProfilerPass;
use Drupal\webprofiler\Compiler\ServicePass;
use Drupal\webprofiler\Compiler\StoragePass;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Defines a service profiler for the webprofiler module.
 */
class WebprofilerServiceProvider extends ServiceProviderBase {

  /**
   * {@inheritdoc}
   */
  public function register(ContainerBuilder $container) {
    // Add a compiler pass to discover all data collector services.
    $container->addCompilerPass(new ProfilerPass());

    $container->addCompilerPass(new StoragePass());
    $container->addCompilerPass(new EventPass(), PassConfig::TYPE_AFTER_REMOVING);
    $container->addCompilerPass(new ServicePass(), PassConfig::TYPE_AFTER_REMOVING);

    // Replace the regular form_builder service with a traceable one.
    $container->getDefinition('form_builder')
      ->setClass('Drupal\webprofiler\Form\FormBuilderWrapper');

    // Add ViewsDataCollector only if Views module is enabled.
    if (FALSE !== $container->hasDefinition('views.executable')) {
      $container->getDefinition('views.executable')
        ->setClass('Drupal\webprofiler\Views\ViewExecutableFactoryWrapper');

      $container->register('webprofiler.views', 'Drupal\webprofiler\DataCollector\ViewsDataCollector')
        ->addArgument(new Reference(('views.executable')))
        ->addArgument(new Reference(('entity.manager')))
        ->addTag('data_collector', [
          'template' => '@webprofiler/Collector/views.html.twig',
          'id' => 'views',
          'title' => 'Views',
          'priority' => 75,
        ]);
    }

    // Add BlockDataCollector only if Block module is enabled.
    if (FALSE !== $container->hasDefinition('plugin.manager.block')) {
      $container->register('webprofiler.blocks', 'Drupal\webprofiler\DataCollector\BlocksDataCollector')
        ->addArgument(new Reference(('entity.manager')))
        ->addTag('data_collector', [
          'template' => '@webprofiler/Collector/blocks.html.twig',
          'id' => 'blocks',
          'title' => 'Blocks',
          'priority' => 78,
        ]);
    }
  }
}
