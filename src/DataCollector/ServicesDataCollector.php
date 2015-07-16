<?php

/**
 * @file
 */

namespace Drupal\webprofiler\DataCollector;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\webprofiler\DependencyInjection\TraceableContainer;
use Drupal\webprofiler\DrupalDataCollectorInterface;
use Symfony\Component\DependencyInjection\IntrospectableContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;

/**
 * Class ServicesDataCollector
 */
class ServicesDataCollector extends DataCollector implements DrupalDataCollectorInterface {

  use StringTranslationTrait, DrupalDataCollectorTrait;

  /**
   * @var \Symfony\Component\DependencyInjection\IntrospectableContainerInterface
   *   $container
   */
  private $container;

  /**
   * @param IntrospectableContainerInterface $container
   */
  public function __construct(IntrospectableContainerInterface $container) {
    $this->container = $container;
  }

  /**
   * {@inheritdoc}
   */
  public function collect(Request $request, Response $response, \Exception $exception = NULL) {
    $this->data['initialized_services'] = [];
    if ($this->countServices()) {
      foreach (array_keys($this->getServices()) as $id) {
        if ($this->container->initialized($id)) {
          $this->data['initialized_services'][] = $id;
        }
      }
    }

    if ($this->container instanceof TraceableContainer) {
      $this->data['times'] = $this->container->getTracedData();
    }
  }

  /**
   * @param $graph
   */
  public function setServicesGraph($graph) {
    $this->data['graph'] = $graph;
  }

  /**
   * @return int
   */
  public function countServices() {
    return count($this->data['graph']);
  }

  /**
   * @return int
   */
  public function countInitializedServices() {
    return count($this->data['initialized_services']);
  }

  /**
   * @return int
   */
  public function countInitializedServicesWithoutWebprofiler() {
    $countWithoutWebprofiler = 0;
    foreach ($this->data['initialized_services'] as $service) {
      if (strpos($service, 'webprofiler') !== 0) {
        $countWithoutWebprofiler++;
      }
    }
    return $countWithoutWebprofiler;
  }

  /**
   * @return array
   */
  public function getServices() {
    return $this->data['graph'];
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return 'services';
  }

  /**
   * {@inheritdoc}
   */
  public function getTitle() {
    return $this->t('Services');
  }

  /**
   * {@inheritdoc}
   */
  public function getPanelSummary() {
    return $this->t('Initialized: @count, initialized without Webprofiler: @count_without_webprofiler, available: @available', [
      '@count' => $this->countInitializedServices(),
      '@count_without_webprofiler' => $this->countInitializedServicesWithoutWebprofiler(),
      '@available' => $this->countServices()
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function getLibraries() {
    return [
      'webprofiler/service',
    ];
  }

  /**
   * @return array
   */
  public function getData() {
    $data = $this->data;

    $http_middleware = [];
    foreach($data['graph'] as $key => $service) {
      if(isset($service['value']['tags']['http_middleware'])) {
        $http_middleware[$key] = $service;
      }
    }

    uasort($http_middleware, function($a, $b) {
      $va = $a['value']['tags']['http_middleware'][0]['priority'];
      $vb = $b['value']['tags']['http_middleware'][0]['priority'];

      if ($va == $vb) {
        return 0;
      }
      return ($va > $vb) ? -1 : 1;
    });

    $data['http_middleware'] = $http_middleware;

    return $data;
  }
}
