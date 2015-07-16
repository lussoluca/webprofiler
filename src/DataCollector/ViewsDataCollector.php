<?php

/**
 * @file
 * Contains \Drupal\webprofiler\DataCollector\ViewsDataCollector.
 */

namespace Drupal\webprofiler\DataCollector;

use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\webprofiler\DrupalDataCollectorInterface;
use Drupal\webprofiler\Entity\EntityManagerWrapper;
use Drupal\webprofiler\Views\TraceableViewExecutable;
use Drupal\webprofiler\Views\ViewExecutableFactoryWrapper;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;

/**
 * Collects data about rendered views.
 */
class ViewsDataCollector extends DataCollector implements DrupalDataCollectorInterface {

  use StringTranslationTrait, DrupalDataCollectorTrait;

  /**
   * @var \Drupal\webprofiler\Views\ViewExecutableFactoryWrapper
   */
  private $view_executable_factory;

  /**
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  private $entityManager;

  /**
   * @param ViewExecutableFactoryWrapper $view_executable_factory
   * @param \Drupal\Core\Entity\EntityManagerInterface $entityManager
   */
  public function __construct(ViewExecutableFactoryWrapper $view_executable_factory, EntityManagerInterface $entityManager) {
    $this->entityManager = $entityManager;
    $this->view_executable_factory = $view_executable_factory;

    $this->data['views'] = [];
  }

  /**
   * {@inheritdoc}
   */
  public function collect(Request $request, Response $response, \Exception $exception = NULL) {
    $views = $this->view_executable_factory->getViews();

    /** @var TraceableViewExecutable $view */
    foreach ($views as $view) {
      if ($view->executed) {
        $data = [
          'id' => $view->storage->id(),
          'current_display' => $view->current_display,
          'build_time' => $view->getBuildTime(),
          'execute_time' => $view->getExecuteTime(),
          'render_time' => $view->getRenderTime(),
        ];

        $this->data['views'][] = $data;
      }
    }

//    TODO: also use those data.
//    $loaded = $this->entityManager->getLoaded('view');
//
//    if ($loaded) {
//      /** @var \Drupal\webprofiler\Entity\EntityStorageDecorator $views */
//      foreach ($loaded->getEntities() as $views) {
//        $this->data['views'][] = array(
//          'id' => $views->get('id'),
//        );
//      }
//    }
  }

  /**
   * @return int
   */
  public function countViews() {
    return count($this->data['views']);
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return 'views';
  }

  /**
   * {@inheritdoc}
   */
  public function getTitle() {
    return $this->t('Views');
  }

  /**
   * {@inheritdoc}
   */
  public function getPanelSummary() {
    return $this->t('Total views: @count', ['@count' => $this->countViews()]);
  }

  /**
   * @return array
   */
  public function getData() {
    $data = $this->data;

    /** @var \Drupal\Core\Entity\EntityManager $entity_manager */
    $entity_manager = \Drupal::service('entity.manager');
    $storage = $entity_manager->getStorage('view');

    foreach ($data['views'] as &$view) {
      $entity = $storage->load($view['id']);
      if ($entity->access('update') && $entity->hasLinkTemplate('edit-display-form')) {
        $route = $entity->urlInfo('edit-display-form');
        $route->setRouteParameter('display_id', $view['current_display']);
        $view['route'] = $route->toString();
      }
    }

    return $data;
  }
}
