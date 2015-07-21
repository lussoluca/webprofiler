<?php

/**
 * @file
 * Contains \Drupal\webprofiler\DataCollector\RoutingDataCollector.
 */

namespace Drupal\webprofiler\DataCollector;

use Drupal\webprofiler\DrupalDataCollectorInterface;
use Drupal\Core\Routing\RouteProviderInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;

/**
 * Provides a data collector which shows all available routes.
 */
class RoutingDataCollector extends DataCollector implements DrupalDataCollectorInterface {

  use StringTranslationTrait, DrupalDataCollectorTrait;

  /**
   * The route profiler.
   *
   * @var \Drupal\Core\Routing\RouteProviderInterface
   */
  protected $routeProvider;

  /**
   * Constructs a new RoutingDataCollector.
   *
   * @param \Drupal\Core\Routing\RouteProviderInterface $routeProvider
   *   The route provider.
   */
  public function __construct(RouteProviderInterface $routeProvider) {
    $this->routeProvider = $routeProvider;
  }

  /**
   * {@inheritdoc}
   */
  public function collect(Request $request, Response $response, \Exception $exception = NULL) {
    $this->data['routing'] = array();
    foreach ($this->routeProvider->getAllRoutes() as $route_name => $route) {
      // @TODO Find a better visual representation.
      $this->data['routing'][] = array(
        'name' => $route_name,
        'path' => $route->getPath(),
      );
    }
  }

  /**
   * @return int
   */
  public function getRoutingCount() {
    return count($this->routing());
  }

  /**
   * Twig callback for displaying the routes.
   */
  public function routing() {
    return $this->data['routing'];
  }

  /**
   * {@inheritdoc}
   */
  public function getTitle() {
    return $this->t('Routing');
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return 'routing';
  }

  /**
   * {@inheritdoc}
   */
  public function getPanelSummary() {
    return $this->t('Defined routes: @route', array('@route' => count($this->routing())));
  }

  /**
   * {@inheritdoc}
   */
  public function getPanel() {
    $build = array();

    $rows = array();
    foreach ($this->routing() as $value) {
      $row = array();

      $row[] = $value['name'];
      $row[] = $value['path'];

      $rows[] = $row;
    }

    $build['title'] = array(
      '#type' => 'inline_template',
      '#template' => '<h3>{{ title }}</h3>',
      '#context' => array(
        'title' => $this->t('Available routes'),
      ),
    );

    $build['table'] = array(
      '#type' => 'table',
      '#rows' => $rows,
      '#header' => array($this->t('Route name'), 'URL'),
      '#sticky' => TRUE,
    );

    return $build;
  }

}
