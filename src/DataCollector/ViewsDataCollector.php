<?php

/**
 * @file
 * Contains \Drupal\webprofiler\DataCollector\ViewsDataCollector.
 */

namespace Drupal\webprofiler\DataCollector;

use Drupal\Core\Entity\EntityManager;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\views\ViewExecutable;
use Drupal\webprofiler\DrupalDataCollectorInterface;
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

  /** @var ViewExecutableFactoryWrapper $view_executable_factory */
  private $view_executable_factory;

  /**
   * @param ViewExecutableFactoryWrapper $view_executable_factory
   */
  public function __construct(ViewExecutableFactoryWrapper $view_executable_factory) {
    $this->view_executable_factory = $view_executable_factory;

    $this->data['views'] = array();
  }

  /**
   * {@inheritdoc}
   */
  public function collect(Request $request, Response $response, \Exception $exception = NULL) {
    $views = $this->view_executable_factory->getViews();

    /** @var TraceableViewExecutable $view */
    foreach ($views as $view) {
      if ($view->executed) {
        $data = array(
          'id' => $view->storage->id(),
          'current_display' => $view->current_display,
          'build_time' => $view->getBuildTime(),
          'execute_time' => $view->getExecuteTime(),
          'render_time' => $view->getRenderTime(),
        );

        $this->data['views'][] = $data;
      }
    }
  }

  /**
   * @return array
   */
  public function getViews() {
    return $this->data['views'];
  }

  /**
   * @return int
   */
  public function getViewsCount() {
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
    return $this->t('Total views: @count', array('@count' => $this->getViewsCount()));
  }

  /**
   * {@inheritdoc}
   */
  public function getPanel() {
    $build = array();

    if ($this->getViewsCount()) {

      /** @var EntityManager $entity_manager */
      $entity_manager = \Drupal::service('entity.manager');
      $storage = $entity_manager->getStorage('view');

      $rows = array();
      foreach ($this->getViews() as $view) {
        $row = array();

        $entity = $storage->load($view['id']);

        $operations = array();
        if ($entity->access('update') && $entity->hasLinkTemplate('edit-display-form')) {
          $route = $entity->urlInfo('edit-display-form');
          $route->setRouteParameter('display_id', $view['current_display']);

          $operations['edit'] = array(
              'title' => $this->t('Edit'),
              'weight' => 10,
              'url' => $route
            );
        }

        $row[] = $view['id'];
        $row[] = $view['current_display'];
        $row[] = sprintf('%0.2f ms', ($view['build_time'] * 1000));
        $row[] = sprintf('%0.2f ms', ($view['execute_time'] * 1000));
        $row[] = sprintf('%0.2f ms', ($view['render_time'] * 1000));
        $row[] = array(
          'data' => array(
            '#type' => 'operations',
            '#links' => $operations,
          ),
        );

        $rows[] = $row;
      }

      $header = array(
        $this->t('Id'),
        $this->t('Display'),
        array(
          'data' => $this->t('Build time'),
          'class' => array(RESPONSIVE_PRIORITY_LOW),
        ),
        array(
          'data' => $this->t('Execute time'),
          'class' => array(RESPONSIVE_PRIORITY_LOW),
        ),
        array(
          'data' => $this->t('Render time'),
          'class' => array(RESPONSIVE_PRIORITY_LOW),
        ),
        $this->t('Operations'),
      );

      $build['title'] = array(
        '#type' => 'inline_template',
        '#template' => '<h3>{{ title }}</h3>',
        '#context' => array(
          'title' => $this->t('Rendered views'),
        ),
      );

      $build['table'] = array(
        '#type' => 'table',
        '#rows' => $rows,
        '#header' => $header,
        '#sticky' => TRUE,
      );
    }

    return $build;
  }
}
