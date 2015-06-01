<?php

/**
 * @file
 * Contains \Drupal\webprofiler\WebprofilerServiceProvider.
 */

namespace Drupal\webprofiler;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;
use Drupal\webprofiler\Compiler\BlockPass;
use Drupal\webprofiler\Compiler\EntityPass;
use Drupal\webprofiler\Compiler\EventPass;
use Drupal\webprofiler\Compiler\ProfilerPass;
use Drupal\webprofiler\Compiler\ServicePass;
use Drupal\webprofiler\Compiler\StoragePass;
use Drupal\webprofiler\Compiler\ViewsPass;
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

    // Add ViewsDataCollector only if Views module is enabled.
    if (FALSE !== $container->hasDefinition('views.executable')) {
      $container->addCompilerPass(new ViewsPass(), PassConfig::TYPE_AFTER_REMOVING);

      $container->register('webprofiler.views', 'Drupal\webprofiler\DataCollector\ViewsDataCollector')
        ->addArgument(new Reference(('views.executable')))
        ->addTag('data_collector', array(
          'template' => '@webprofiler/Collector/views.html.twig',
          'id' => 'views',
          'title' => 'Views',
          'priority' => 75,
        ));
    }

    // Add BlockDataCollector only if Block module is enabled.
    if (FALSE !== $container->hasDefinition('plugin.manager.block')) {
      $container->register('webprofiler.block', 'Drupal\webprofiler\DataCollector\BlockDataCollector')
        ->addArgument(new Reference(('entity.manager')))
        ->addTag('data_collector', array(
          'template' => '@webprofiler/Collector/block.html.twig',
          'id' => 'block',
          'title' => 'Block',
          'priority' => 78,
        ));
    }

    // Replace the existing state service with a wrapper to collect the
    // requested data.
    $container->setDefinition('state.default', $container->getDefinition('state'));
    $container->register('state', 'Drupal\webprofiler\DataCollector\StateDataCollector')
      ->addArgument(new Reference(('state.default')))
      ->addTag('data_collector', array(
        'template' => '@webprofiler/Collector/state.html.twig',
        'id' => 'state',
        'title' => 'State',
        'priority' => 135,
      ));

    // Replace the regular form_builder service with a traceable one.
    $definition = $container->findDefinition('form_builder');
    $definition->setClass('Drupal\webprofiler\Form\FormBuilderWrapper');

    // Replace the regular entity.manager service with a traceable one.
    $definition = $container->findDefinition('entity.manager');
    $definition->setClass('Drupal\webprofiler\Entity\EntityManagerWrapper');

    // Replace the regular asset.js.collection_renderer service
    // with a traceable one.
    $definition = $container->findDefinition('asset.js.collection_renderer');
    $definition->setClass('Drupal\webprofiler\Asset\JsCollectionRendererWrapper');

    // Replace the regular asset.js.collection_renderer service
    // with a traceable one.
    $definition = $container->findDefinition('asset.css.collection_renderer');
    $definition->setClass('Drupal\webprofiler\Asset\CssCollectionRendererWrapper');

    // Replace the regular authentication service
    // with a traceable one.
    $definition = $container->findDefinition('authentication');
    $definition->setClass('Drupal\webprofiler\Authentication\AuthenticationManagerWrapper');
  }
}
