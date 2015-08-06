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
    if ($this->countServices()) {

      $tracedData = [];
      if ($this->container instanceof TraceableContainer) {
        $tracedData = $this->container->getTracedData();
      }

      foreach (array_keys($this->getServices()) as $id) {
        $this->data['services'][$id]['initialized'] = ($this->container->initialized($id)) ? TRUE : FALSE;
        $this->data['services'][$id]['time'] = isset($tracedData[$id]) ? $tracedData[$id] : NULL;
      }
    }
  }

  /**
   * @param $services
   */
  public function setServices($services) {
    $this->data['services'] = $services;
  }

  /**
   * @return array
   */
  public function getServices() {
    return $this->data['services'];
  }

  /**
   * @return int
   */
  public function countServices() {
    return count($this->getServices());
  }

  /**
   * @return array
   */
  public function getInitializedServices() {
    return array_filter($this->getServices(), function($item) {
      return $item['initialized'];
    });
  }

  /**
   * @return int
   */
  public function countInitializedServices() {
    return count($this->getInitializedServices());
  }

  /**
   * @return array
   */
  public function getInitializedServicesWithoutWebprofiler() {
    return array_filter($this->getInitializedServices(), function($item) {
      return strpos($item['value']['id'], 'webprofiler') !== 0;
    });
  }

  /**
   * @return int
   */
  public function countInitializedServicesWithoutWebprofiler() {
    return count($this->getInitializedServicesWithoutWebprofiler());
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
    foreach ($data['services'] as $key => $service) {
      if (isset($service['value']['tags']['http_middleware'])) {
        $http_middleware[$key] = $service;
      }
    }

    uasort($http_middleware, function ($a, $b) {
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
